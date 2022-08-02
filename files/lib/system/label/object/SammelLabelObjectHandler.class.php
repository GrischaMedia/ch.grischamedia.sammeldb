<?php
namespace wcf\system\label\object;
use wcf\system\cache\builder\SammelCategoryLabelCacheBuilder;
use wcf\system\label\LabelHandler;

/**
 * Label handler for SammelDB
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelLabelObjectHandler extends AbstractLabelObjectHandler {
	/**
	 * @inheritDoc
	 */
	protected $objectType = 'ch.grischamedia.sammel.label';
	
	/**
	 * Sets the label groups available for the categories with the given ids.
	 */
	public function setCategoryIDs($categoryIDs) {
		$labelGroupsToCategories = SammelCategoryLabelCacheBuilder::getInstance()->getData();
		
		$groupIDs = [];
		foreach ($labelGroupsToCategories as $categoryID => $__groupIDs) {
			if (in_array($categoryID, $categoryIDs)) {
				$groupIDs = array_merge($groupIDs, $__groupIDs);
			}
		}
		
		$this->labelGroups = [];
		if (!empty($groupIDs)) {
			$this->labelGroups = LabelHandler::getInstance()->getLabelGroups(array_unique($groupIDs));
		}
	}
}
