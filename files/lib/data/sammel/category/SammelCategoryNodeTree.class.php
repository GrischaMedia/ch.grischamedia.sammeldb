<?php
namespace wcf\data\sammel\category;
use wcf\data\category\CategoryNode;
use wcf\data\category\CategoryNodeTree;

/**
 * Represents a list of sammel category nodes.
 *
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategoryNodeTree extends CategoryNodeTree {
	/**
	 * @inheritDoc
	 */
	protected $nodeClassName = SammelCategoryNode::class;
	
	/**
	 * @inheritDoc
	 */
	public function isIncluded(CategoryNode $categoryNode) {
		return parent::isIncluded($categoryNode) && $categoryNode->isAccessible();
	}
}
