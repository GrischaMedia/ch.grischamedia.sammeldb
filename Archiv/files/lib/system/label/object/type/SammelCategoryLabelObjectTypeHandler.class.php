<?php
namespace wcf\system\label\object\type;
use wcf\data\sammel\category\SammelCategoryNodeTree;
use wcf\system\cache\builder\SammelCategoryLabelCacheBuilder;

/**
 * Object type handler for sammel categories.
 *
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategoryLabelObjectTypeHandler extends AbstractLabelObjectTypeHandler {
	/**
	 * category list
	 */
	public $categoryList;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$categoryTree = new SammelCategoryNodeTree('ch.grischamedia.sammel.category');
		
		$this->categoryList = $categoryTree->getIterator();
		$this->categoryList->setMaxDepth(0);
	}
	
	/**
	 * @inheritDoc
	 */
	public function setObjectTypeID($objectTypeID) {
		parent::setObjectTypeID($objectTypeID);
		
		$this->container = new LabelObjectTypeContainer($this->objectTypeID);
		foreach ($this->categoryList as $category) {
			$this->container->add(new LabelObjectType($category->getTitle(), $category->categoryID, 0));
			foreach ($category as $subCategory) {
				$this->container->add(new LabelObjectType($subCategory->getTitle(), $subCategory->categoryID, 1));
				foreach ($subCategory as $subSubCategory) {
					$this->container->add(new LabelObjectType($subSubCategory->getTitle(), $subSubCategory->categoryID, 2));
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		SammelCategoryLabelCacheBuilder::getInstance()->reset();
	}
}
