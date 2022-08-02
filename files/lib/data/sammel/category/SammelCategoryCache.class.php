<?php
namespace wcf\data\sammel\category;
use wcf\data\sammel\Sammel;
use wcf\data\category\Category;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages the sammel category cache.
 *
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategoryCache extends SingletonFactory {
	/**
	 * number of total sammels
	 */
	protected $sammels;
	
	/**
	 * Calculates the number of sammels.
	 */
	protected function initSammels() {
		$this->sammels = [];
		
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('isDisabled = ?', [0]);
		
		$sql = "SELECT		COUNT(*) AS count, categoryID
				FROM		wcf" . WCF_N . "_sammel
				".$conditionBuilder."
				GROUP BY	categoryID";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		$sammels = $statement->fetchMap('categoryID', 'count');
		
		$categoryToParent = [];
		
		foreach (CategoryHandler::getInstance()->getCategories(SammelCategory::OBJECT_TYPE_NAME) as $category) {
			if (!isset($categoryToParent[$category->parentCategoryID])) $categoryToParent[$category->parentCategoryID] = [];
			$categoryToParent[$category->parentCategoryID][] = $category->categoryID;
		}
		
		$this->countSammels($categoryToParent, $sammels, 0);
	}
	
	/**
	 * Counts the sammels contained in this category and its children.
	 */
	protected function countSammels(array &$categoryToParent, array &$sammels, $categoryID) {
		$count = (isset($sammels[$categoryID])) ? $sammels[$categoryID] : 0;
		if (isset($categoryToParent[$categoryID])) {
			foreach ($categoryToParent[$categoryID] as $childCategoryID) {
				$count += $this->countSammels($categoryToParent, $sammels, $childCategoryID);
			}
		}
		
		if ($categoryID) $this->sammels[$categoryID] = $count;
		
		return $count;
	}
	
	/**
	 * Returns the number of sammels in the category with the given id.
	 */
	public function getSammels($categoryID) {
		if ($this->sammels === null) {
			$this->initSammels();
		}
		
		if (isset($this->sammels[$categoryID])) return $this->sammels[$categoryID];
		return 0;
	}
}
