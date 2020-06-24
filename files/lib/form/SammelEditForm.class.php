<?php
namespace wcf\form;
use wcf\data\sammel\Sammel;
use wcf\data\sammel\SammelAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\label\object\SammelLabelObjectHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Shows the SammelDB edit form.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelEditForm extends SammelAddForm {
	/**
	 * sammel data
	 */
	public $sammelID = 0;
	public $sammel = null;
	
	public $action = 'edit';
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->sammelID = intval($_REQUEST['id']);
		$this->sammel = new Sammel($this->sammelID);
		if (!$this->sammel->sammelID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		// save labels
		SammelLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $this->sammel->sammelID);
		$labelIDs = SammelLabelObjectHandler::getInstance()->getAssignedLabels([$this->sammel->sammelID], false);
		
		// update sammel
		$this->objectAction = new SammelAction([$this->sammelID], 'update', [
				'data' => array_merge($this->additionalFields, [
						'categoryID' => $this->categoryID ? $this->categoryID : null,
						'title' => $this->title,
						'details' => $this->details,
						'number' => $this->number,
						'online' => $this->online,
						'url' => $this->url,
						
						'time' => TIME_NOW,
						'userID' => WCF::getUser()->userID,
						
						'hasLabels' => (isset($labelIDs[$this->sammel->sammelID]) && !empty($labelIDs[$this->sammel->sammelID])) ? 1 : 0,
				]),
				'labelIDs' => $this->labelIDs,
				'htmlInputProcessor' => $this->htmlInputProcessor,
				'tmpHash' => $this->tmpHash
		]);
		$this->objectAction->executeAction();
		
		$this->saved();
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Sammel', []));
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->categoryID = $this->sammel->categoryID;
			$this->title = $this->sammel->title;
			$this->details = $this->sammel->details;
			$this->number = $this->sammel->number;
			$this->online = $this->sammel->online;
			$this->url = $this->sammel->url;
			
			// labels 
			$assignedLabels = SammelLabelObjectHandler::getInstance()->getAssignedLabels([$this->sammel->sammelID], true);
			if (isset($assignedLabels[$this->sammel->sammelID])) {
				foreach ($assignedLabels[$this->sammel->sammelID] as $label) {
					$this->labelIDs[$label->groupID] = $label->labelID;
				}
			}
			
			// icon
			$sammel = new Sammel($this->sammel->sammelID);
			$this->iconLocation = $sammel->getIconURL();
			
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'sammel' => $this->sammel,
				'action' => 'edit'
		]);
	}
}
