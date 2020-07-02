<?php
namespace wcf\data\sammel;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\data\sammel\Sammel;
use wcf\data\sammel\SammelEditor;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\image\ImageHandler;
use wcf\system\label\object\SammelLabelObjectHandler;
use wcf\system\upload\SammelValidationStrategy;
use wcf\system\upload\UploadHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Executes SammelDB-related actions.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelAction extends AbstractDatabaseObjectAction implements IToggleAction {
	/**
	 * @inheritDoc
	 */
	protected $className = SammelEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['user.sammel.canEdit'];
	protected $permissionsUpdate = ['user.sammel.canEdit'];
	
	protected $allowGuestAccess = ['open', 'getIconDialog'];
	
	// item
	public $item;
	public $upload;
	
	/**
	 * @inheritDoc
	 */
	public function create() {
		// create entry
		$data = $this->parameters['data'];
		
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$data['details'] = $this->parameters['htmlInputProcessor']->getHtml();
		}
		
		$item = call_user_func([$this->className, 'create'], $data);
		$sammelEditor = new SammelEditor($item);
		
		// labels
		if (!empty($this->parameters['labelIDs'])) SammelLabelObjectHandler::getInstance()->setLabels($this->parameters['labelIDs'], $item->sammelID);
		
		// save sammel icon
		$this->updateSammelIcon($item);
		
		return $item;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		if (!empty($this->parameters['htmlInputProcessor'])) {
			$this->parameters['data']['details'] = $this->parameters['htmlInputProcessor']->getHtml();
		}
		
		parent::update();
		
		// update labels
		foreach ($this->getObjects() as $item) {
			SammelLabelObjectHandler::getInstance()->setLabels($this->parameters['labelIDs'], $item->sammelID);
		}
		
		foreach ($this->getObjects() as $item) {
			// save icon
			$sammel = new Sammel($item->sammelID);
			$this->updateSammelIcon($sammel);
		}
	}
	
	/**
	 * Delete given items.
	 */
	public function validateDelete() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new IllegalLinkException();
			}
		}
		
		foreach ($this->getObjects() as $sammel) {
			if (!$sammel->canEdit()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	public function delete() {
		foreach ($this->getObjects() as $item) {
			$item->delete();
			
			// delete icon
			if ($item->getIconLocation()) {
				@unlink($item->getIconLocation());
				@unlink($item->getIconOrigLocation());
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateToggle() {
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new IllegalLinkException();
			}
		}
		
		foreach ($this->getObjects() as $sammel) {
			if (!$sammel->canEdit()) {
				throw new PermissionDeniedException();
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function toggle() {
		foreach ($this->getObjects() as $item) {
			$item->update([
					'isDisabled' => $item->isDisabled ? 0 : 1
			]);
		}
	}
	
	/**
	 * Open details text.
	 */
	public function validateOpen() {
		$this->item = new Sammel($this->parameters['objectID']);
		if (!$this->item->sammelID) {
			throw new IllegalLinkException();
		}
		
		if (!$this->item->canSee()) {
			throw new PermissionDeniedException();
		}
	}
	
	public function open() {
		return [
				'id' => $this->item->sammelID,
				'text' => $this->item->getFormattedDetails()
		];
	}
	
	/**
	 * Open dialog with full-sized icon.
	 */
	public function validateGetIconDialog() {
		$this->item = new Sammel($this->parameters['objectID']);
		if (!$this->item->sammelID) {
			throw new IllegalLinkException();
		}
		
		if (!$this->item->canSee()) {
			throw new PermissionDeniedException();
		}
	}
	
	public function getIconDialog() {
		$icon = '';
		if ($this->item->iconPath) {
			if (@file_exists($this->item->getIconOrigLocation())) {
				$icon = $this->item->getIconOrigURL();
			}
			else {
				$icon = $this->item->getIconURL();
			}
		}
		
		WCF::getTPL()->assign([
				'data' => $icon
		]);
		
		return [
				'template' => WCF::getTPL()->fetch('sammelIconDialog')
		];
	}
	
	/**
	 * Upload an icon.
	 */
	public function validateUploadIcon() {
		$this->readString('tmpHash');
		$this->readInteger('sammelID', true);
		
		// check permissions
		if (!$this->parameters['sammelID'] && !WCF::getSession()->getPermission('user.sammel.canEdit')) {
			throw new PermissionDeniedException();
		}
		
		if ($this->parameters['sammelID']) {
			$this->sammel = new Sammel($this->parameters['sammelID']);
			if (!$this->sammel->sammelID) {
				throw new UserInputException('sammelID');
			}
			
			if (!$this->sammel->canEdit()) {
				throw new PermissionDeniedException();
			}
		}
		
		$uploadHandler = $this->parameters['__files'];
		
		if (count($uploadHandler->getFiles()) != 1) {
			throw new IllegalLinkException();
		}
		
		// check uploaded sammel icon
		$uploadHandler->validateFiles(new SammelValidationStrategy());
	}
	
	public function uploadIcon() {
		$files = $this->parameters['__files']->getFiles();
		$sammel = reset($files);
		
		try {
			if (!$sammel->getValidationErrorType()) {
				$imageData = $sammel->getImageData();
				$neededMemory = $imageData['width'] * $imageData['height'] * ($sammel->getFileExtension() == 'png' ? 4 : 3) * 2.1;
				if (FileUtil::checkMemoryLimit($neededMemory)) {
					$adapter = ImageHandler::getInstance()->getAdapter();
					$adapter->loadFile($sammel->getLocation());
					
					$sammelOrigLocation = $sammel->getLocation();
					
					$sammelLocation = FileUtil::getTemporaryFilename();
					$thumbnail = $adapter->createThumbnail(144, 144, false);
					$adapter->writeImage($thumbnail, $sammelLocation);
					
					$iconLocation = WCF_DIR.'sammelImages/' . $this->parameters['tmpHash'] . '.' . $sammel->getFileExtension();
					$iconOrigLocation = WCF_DIR.'sammelImages/' . $this->parameters['tmpHash'] . '_orig.' . $sammel->getFileExtension();
					
					if (@copy($sammelLocation, $iconLocation)) {
						@copy($sammelOrigLocation, $iconOrigLocation);
						@unlink($sammelLocation);
						
						// store extension within session variables
						WCF::getSession()->register('SammelIcon-'.$this->parameters['tmpHash'], $sammel->getFileExtension());
						
						return [
								'url' => WCF::getPath() . 'sammelImages/' . $this->parameters['tmpHash'] . '.' . $sammel->getFileExtension()
						];
					}
					else {
						throw new UserInputException('image', 'uploadFailed');
					}
				}
				else {
					throw new UserInputException('image', 'tooLarge');
				}
			}
		}
		catch (UserInputException $e) {
			$sammel->setValidationErrorType($e->getType());
		}
		
		return ['errorType' => $sammel->getValidationErrorType()];
	}
	
	/**
	 * Delete an icon.
	 */
	public function validateDeleteIcon() {
		$this->readString('tmpHash');
		$this->readInteger('sammelID', true);
		
		if (!$this->parameters['sammelID']) {
			if (!WCF::getSession()->getPermission('user.sammel.canEdit')) {
				throw new PermissionDeniedException();
			}
			
			// check if user has uploaded any sammel icon
			$iconExtension = WCF::getSession()->getVar('SammelIcon-' . $this->parameters['tmpHash']);
			if (!$iconExtension || !file_exists(WCF_DIR.'sammelImages/' . $this->parameters['tmpHash'] . '.' . $iconExtension)) {
				throw new IllegalLinkException();
			}
		}
		else {
			$this->sammel = new Sammel($this->parameters['sammelID']);
			if (!$this->sammel->sammelID) {
				throw new UserInputException('sammelID');
			}
			
			if (!$this->sammel->canEdit()) {
				throw new PermissionDeniedException();
			}
			
			if (!$this->sammel->getIconLocation()) {
				// check if user has uploaded any sammel icon
				$iconExtension = WCF::getSession()->getVar('SammelIcon-' . $this->parameters['tmpHash']);
				if (!$iconExtension || !file_exists(WCF_DIR.'sammelImages/' . $this->parameters['tmpHash'] . '.' . $iconExtension)) {
					throw new IllegalLinkException();
				}
			}
		}
	}
	
	public function deleteIcon() {
		if ($this->sammel) {
			@unlink($this->sammel->getIconLocation());
			@unlink($this->sammel->getIconOrigLocation());
			
			// back to item
			$item = new Sammel($this->sammel->sammelID);
			$itemEditor = new SammelEditor($item);
			
			$itemEditor->update([
					'iconHash' => '',
					'iconExtension' => '',
					'iconPath' => ''
			]);
		}
		
		$iconExtension = WCF::getSession()->getVar('SammelIcon-' . $this->parameters['tmpHash']);
		if ($iconExtension) {
			@unlink(WCF_DIR . 'sammelImages/' . $this->parameters['tmpHash'] . '.' . $iconExtension);
			WCF::getSession()->unregister('SammelIcon-' . $this->parameters['tmpHash']);
		}
	}
	
	/**
	 * Updates the icon of the given item.
	 */
	public function updateSammelIcon(Sammel $sammel) {
		if (!isset($this->parameters['tmpHash'])) {
			return;
		}
		
		// back to item
		$item = new Sammel($sammel->sammelID);
		$itemEditor = new SammelEditor($item);
		
		$fileExtension = WCF::getSession()->getVar('SammelIcon-' . $this->parameters['tmpHash']);
		if ($fileExtension !== null) {
			$oldFilename = WCF_DIR . 'sammelImages/' . $this->parameters['tmpHash'] . '.' . $fileExtension;
			$oldOrigFilename = WCF_DIR . 'sammelImages/' . $this->parameters['tmpHash'] . '_orig.' . $fileExtension;
			if (file_exists($oldFilename)) {
				// delete old sammel icon
				if ($sammel->getIconLocation()) {
					@unlink($sammel->getIconLocation());
					@unlink($sammel->getIconOrigLocation());
				}
				
				$iconHash = sha1_file($oldFilename);
				$newFilename = WCF_DIR . 'sammelImages/' . substr($iconHash, 0, 2) . '/' . $sammel->sammelID . '.' . $fileExtension;
				$newOrigFilename = WCF_DIR . 'sammelImages/' . substr($iconHash, 0, 2) . '/' . $sammel->sammelID . '_orig.' . $fileExtension;
				$newFileUrl = WCF::getPath() . 'sammelImages/' . substr($iconHash, 0, 2) . '/' . $sammel->sammelID . '.' . $fileExtension;
				$directory = dirname($newFilename);
				
				// check if directory exists
				if (!@file_exists($directory)) {
					FileUtil::makePath($directory);
				}
				
				if (@rename($oldFilename, $newFilename)) {
					@rename($oldOrigFilename, $newOrigFilename);
					
					$itemEditor->update([
							'iconHash' => $iconHash,
							'iconExtension' => $fileExtension,
							'iconPath' => $newFileUrl
					]);
				}
				else {
					@unlink($oldFilename);
					@unlink($oldOrigFilename);
				}
			}
		}
	}
}
