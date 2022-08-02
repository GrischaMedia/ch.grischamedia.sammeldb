<?php
namespace wcf\page;
use wcf\data\category\CategoryList;
use wcf\data\sammel\Sammel;
use wcf\data\sammel\SammelList;
use wcf\data\sammel\category\SammelCategory;
use wcf\system\category\CategoryHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows the SammelDB
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelPage extends SortablePage{
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'ch.grischamedia.sammel.SammelPage';
	public $neededPermissions = ['user.sammel.canEdit', 'user.sammel.canSee'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = SAMMEL_ITEMS_PER_PAGE;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = SammelList::class;
	
	
	/**
	 * categories and items
	 */
	public $categories = null;
	public $items = null;
	public $itemsToCategory = [];
	
	/**
	 * sorting
	 */
	public $defaultSortField = 'categoryID';
	public $defaultSortOrder = 'ASC';
	public $validSortFields = ['categoryID', 'sammelID', 'iconPath', 'title', 'details', 'number', 'online', 'url'];
	
	/**
	 * search
	 */
	public $search = '';
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		// categories for template
		$objectType = CategoryHandler::getInstance()->getObjectTypeByName('ch.grischamedia.sammel.category');
		if ($objectType) {
			$categoryList = new CategoryList();
			$categoryList->getConditionBuilder()->add('category.objectTypeID = ?', [$objectType->objectTypeID]);
			$categoryList->getConditionBuilder()->add('category.isDisabled = ?', [0]);
			$categoryList->readObjects();
			$this->categories = $categoryList->getObjects();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		// read search
		if (!empty($_REQUEST['search'])) $this->search = StringUtil::trim($_REQUEST['search']);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// search
		if ($this->search) {
			$search = '%'.$this->search.'%';
			$this->objectList->getConditionBuilder()->add('(sammel.title LIKE ? OR sammel.details LIKE ?)', [$search, $search]);
		}
		
		// categories, show items without category
		$accessibleCategoryIDs = SammelCategory::getAccessibleCategoryIDs();
		
		if (empty($accessibleCategoryIDs)) {
			$this->objectList->getConditionBuilder()->add('sammel.categoryID IS NULL');
		}
		else {
			$this->objectList->getConditionBuilder()->add('(sammel.categoryID IN (?) OR sammel.categoryID IS NULL)', [$accessibleCategoryIDs]);
		}
		
		// disabled
		if (!WCF::getSession()->getPermission('user.sammel.canEdit')) {
			$this->objectList->getConditionBuilder()->add('sammel.isDisabled = ?', [0]);
		}
		
		// show order
		$this->objectList->sqlSelects = 'sammel.*, category.showOrder';
		$this->objectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_category category ON (category.categoryID = sammel.categoryID)";
}
	
	/**
	 * @inheritDoc
	 */
	protected function readObjects() {
		if ($this->sortField == 'categoryID') {
			$this->sqlOrderBy = 'category.showOrder ASC';
		}
		
		parent::readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables () {
		parent::assignVariables();
		
		// category sorting
		$this->itemsToCategory = $this->getItemsToCategory();
		
		WCF::getTPL()->assign([
				'categories' => $this->categories,
				'itemsToCategory' => $this->itemsToCategory,
				'search' => $this->search
		]);
	}
	
	/**
	 * Item to category sorting
	 */
	public function getItemsToCategory() {
		$itemsToCategory = [];
		foreach ($this->objectList as $sammel) {
			if ($sammel->categoryID) {
				$itemsToCategory[$sammel->categoryID][] = $sammel;
			}
			else {
				$itemsToCategory[0][] = $sammel;
			}
		}
		
		// excerpt added
		if (count($itemsToCategory)) {
			foreach ($itemsToCategory as $items) {
				foreach ($items as $item) {
					$item->truncated = '';
					$item->details = $item->getFormattedDetails();
					if (mb_strlen($item->details) > SAMMEL_EXCERPT_LENGTH) {
						$item->truncated = $item->getFormattedExcerpt(SAMMEL_EXCERPT_LENGTH);
					}
				}
			}
		}
		
		return $itemsToCategory;
	}
}
