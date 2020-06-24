<?php
namespace wcf\system\cache\builder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\category\CategoryHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\WCF;

/**
 * Caches the available label group ids for sammel categories.
 *
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategoryLabelCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$conditionBuilder = new PreparedStatementConditionBuilder();
		$conditionBuilder->add('objectTypeID = ?', [ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.label.objectType', 'ch.grischamedia.sammel.category')->objectTypeID]);
		$conditionBuilder->add('objectID IN (SELECT categoryID FROM wcf'.WCF_N.'_category WHERE objectTypeID = ?)', [CategoryHandler::getInstance()->getObjectTypeByName('ch.grischamedia.sammel.category')->objectTypeID]);
		
		$sql = "SELECT	groupID, objectID
				FROM	wcf".WCF_N."_label_group_to_object
				".$conditionBuilder;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditionBuilder->getParameters());
		
		return $statement->fetchMap('objectID', 'groupID', false);
	}
}
