<?php
namespace wcf\system\category;
use wcf\data\category\CategoryEditor;
use wcf\system\category\AbstractCategoryType;
use wcf\system\WCF;

/**
 * Category type for SammelDB.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategoryType extends AbstractCategoryType {
	/**
	 * @inheritDoc
	 */
	protected $forceDescription = false;
	
	/**
	 * @inheritDoc
	 */
	protected $langVarPrefix = 'wcf.sammel.category';
	
	/**
	 * @inheritDoc
	 */
	protected $objectTypes = ['com.woltlab.wcf.acl' => 'ch.grischamedia.sammel.category'];
	
	/**
	 * @inheritDoc
	 */
	protected $maximumNestingLevel = 3;
	
	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return $this->canEditCategory();
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission('admin.sammel.canManage');
	}
	
	/**
	 * @inheritDoc
	 */
	public function beforeDeletion(CategoryEditor $categoryEditor) {
		// move to parent if exist
		$category = $categoryEditor->getDecoratedObject();
		
		$sql = "UPDATE	wcf".WCF_N."_sammel
				SET		categoryID = ?
				WHERE	categoryID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		if ($category->parentCategoryID) {
			$statement->execute([$category->parentCategoryID, $category->categoryID]);
		}
		else {
			$statement->execute([null, $category->categoryID]);
		}
	}
}
