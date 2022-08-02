<?php
namespace wcf\form;
use wcf\data\category\Category;
use wcf\data\sammel\SammelAction;
use wcf\data\sammel\category\SammelCategory;
use wcf\data\sammel\category\SammelCategoryNodeTree;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\SammelCategoryLabelCacheBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\label\object\SammelLabelObjectHandler;
use wcf\system\message\censorship\Censorship;
use wcf\system\page\PageLocationManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Shows the SammelDB add form.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelAddForm extends AbstractForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'ch.grischamedia.sammel.SammelPage';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['user.sammel.canEdit'];
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $messageObjectType = 'ch.grischamedia.sammel';
	
	/**
	 * @var HtmlInputProcessor
	 */
	public $htmlInputProcessor;
	
	/**
	 * sammel data
	 */
	public $categoryID = 0;
	public $title = '';
	public $details = '';
	public $number = '';
	public $online = 0;
	public $url = '';
	
	/**
	 * others
	 */
	public $categoryNodeTree = null;
	public $categoryWarning = 0;
	
	public $action = 'add';
	
	/**
	 * Labels
	 */
	public $availableLabels = [];
	public $labelGroups;
	public $labelIDs = [];
	public $labelGroupsToCategories = [];
	
	/**
	 * icon data
	 */
	public $tmpHash = '';
	public $iconLocation = '';
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// categories
		$this->categoryNodeTree = new SammelCategoryNodeTree('ch.grischamedia.sammel.category');
		$accessIDs = SammelCategory::getAccessibleCategoryIDs(['canUseCategory']);
		$this->categoryWarning = count($accessIDs) ? 0 : 1;
		
		// labels
		$this->labelGroupsToCategories = SammelCategoryLabelCacheBuilder::getInstance()->getData();
		$this->labelGroups = SammelCategory::getAccessibleLabelGroups();
		
		// add breadcrumbs
		PageLocationManager::getInstance()->addParentLocation('ch.grischamedia.sammel.SammelPage');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// tmphash for icon
		if (isset($_REQUEST['tmpHash'])) {
			$this->tmpHash = StringUtil::trim($_REQUEST['tmpHash']);
		}
		if (empty($this->tmpHash)) {
			$this->tmpHash = StringUtil::getRandomID();
		}
		
		// labels
		SammelLabelObjectHandler::getInstance()->setCategoryIDs(SammelCategory::getAccessibleCategoryIDs());
	}
	
	/**
	 * @inheritDoc
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		$this->online = 0;
		if (isset($_POST['categoryID'])) $this->categoryID = intval($_POST['categoryID']);
		if (isset($_POST['title'])) $this->title = StringUtil::trim($_POST['title']);
		if (isset($_POST['details'])) $this->details = StringUtil::trim($_POST['details']);
		if (isset($_POST['number'])) $this->number = StringUtil::trim($_POST['number']);
		if (isset($_POST['online'])) $this->online = intval($_POST['online']);
		if (isset($_POST['url'])) $this->url = StringUtil::trim($_POST['url']);
		
		if (isset($_POST['labelIDs']) && is_array($_POST['labelIDs'])) $this->labelIDs = $_POST['labelIDs'];
		else $this->labelIDs = [];
		
		$iconExtension = WCF::getSession()->getVar('SammelIcon-'.$this->tmpHash);
		if ($iconExtension && file_exists(WCF_DIR.'sammelImages/'.$this->tmpHash.'.'.$iconExtension)) {
			$this->iconLocation = WCF::getPath('wcf').'sammelImages/'.$this->tmpHash.'.'.$iconExtension;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		parent::validate();
		
		// title
		if (empty($this->title)) {
			throw new UserInputException('title', 'empty');
		}
		if (mb_strlen($this->title) > 80) {
			throw new UserInputException('title', 'tooLong');
		}
		
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($this->title);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('title', 'censoredWordsFound');
			}
		}
		
		// details
		if (empty($this->details)) {
			throw new UserInputException('details', 'empty');
		}
		
		$this->htmlInputProcessor = new HtmlInputProcessor();
		$this->htmlInputProcessor->process($this->details, $this->messageObjectType, 0);
		
		if ($this->htmlInputProcessor->appearsToBeEmpty()) {
			throw new UserInputException('details', 'empty');
		}
		$details = $this->htmlInputProcessor->getTextContent();
		if (mb_strlen($details) > 60000) {
			throw new UserInputException('details', 'tooLong');
		}
		
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($details);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('details', 'censoredWordsFound');
			}
		}
		
		// category
		$category = new Category($this->categoryID);
		if (!$category->categoryID) {
			throw new UserInputException('categoryID', 'invalid');
		}
		
		$accessIDs = SammelCategory::getAccessibleCategoryIDs(['canUseCategory']);
		if (!in_array($this->categoryID, $accessIDs)) {
			throw new UserInputException('categoryID', 'invalid');
		}
		
		// number, except empty ufn
		//if (empty($this->number)) {
		//	throw new UserInputException('number', 'empty');
		//}
		
		if (mb_strlen($this->title) > 192) {
			throw new UserInputException('number', 'tooLong');
		}
		
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($this->number);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('number', 'censoredWordsFound');
			}
		}
		
		// url
		if (mb_strlen($this->url) > 60000) {
			throw new UserInputException('url', 'tooLong');
		}
		
		if (ENABLE_CENSORSHIP) {
			$result = Censorship::getInstance()->test($this->url);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
				throw new UserInputException('url', 'censoredWordsFound');
			}
		}
		
		$this->validateLabelIDs();
	}
	
	/**
	 * Validates the selected labels.
	 */
	protected function validateLabelIDs() {
		SammelLabelObjectHandler::getInstance()->setCategoryIDs([$this->categoryID]);
		
		$validationResult = SammelLabelObjectHandler::getInstance()->validateLabelIDs($this->labelIDs, 'canSetLabel', false);
		
		// reset category ids to accessible category ids
		SammelLabelObjectHandler::getInstance()->setCategoryIDs(SammelCategory::getAccessibleCategoryIDs());
		
		if (!empty($validationResult[0])) {
			throw new UserInputException('labelIDs');
		}
		
		if (!empty($validationResult)) {
			throw new UserInputException('label', $validationResult);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		parent::save();
		
		// save item
		$this->objectAction = new SammelAction([], 'create', [
				'data' => array_merge($this->additionalFields, [
						'categoryID' => $this->categoryID ? $this->categoryID : null,
						'title' => $this->title,
						'details' => $this->details,
						'number' => $this->number,
						'online' => $this->online,
						'url' => $this->url,
						
						'time' => TIME_NOW,
						'userID' => WCF::getUser()->userID,
						
						'hasLabels' => count($this->labelIDs) ? 1 : 0
				]),
				'labelIDs' => $this->labelIDs,
				'htmlInputProcessor' => $this->htmlInputProcessor,
				'tmpHash' => $this->tmpHash
		]);
		$sammel = $this->objectAction->executeAction()['returnValues'];
		
		// labels
		if (!empty($this->labelIDs)) {
			SammelLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $sammel->sammelID);
		}
		
		$this->saved();
		
		HeaderUtil::redirect(LinkHandler::getInstance()->getLink('Sammel', []));
		exit;
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
				'action' => 'add',
				'categoryNodeList' => $this->categoryNodeTree->getIterator(),
				
				'categoryID' => $this->categoryID,
				'categoryWarning' => $this->categoryWarning,
				'title' => $this->title,
				'details' => $this->details,
				'number' => $this->number,
				'online' => $this->online,
				'url' => $this->url,
				
				'userID' => WCF::getUser()->userID,
				'time' => TIME_NOW,
				'availableLabels' => $this->availableLabels,
				'labelGroups' => $this->labelGroups,
				'labelIDs' => $this->labelIDs,
				'labelGroupsToCategories' => $this->labelGroupsToCategories,
				
				'iconLocation' => $this->iconLocation,
				'tmpHash' => $this->tmpHash
		]);
	}
}
