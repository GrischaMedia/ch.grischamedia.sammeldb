<?php
namespace wcf\acp\page;

/**
 * Shows the SammelDB category list page.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategoryListPage extends AbstractCategoryListPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.sammel.category.list';
	public $objectTypeName = 'ch.grischamedia.sammel.category';
}
