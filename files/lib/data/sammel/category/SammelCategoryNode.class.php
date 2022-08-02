<?php
namespace wcf\data\sammel\category;
use wcf\data\category\CategoryNode;

/**
 * Represents a list of sammel category nodes.
 *
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategoryNode extends CategoryNode {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = SammelCategory::class;
}
