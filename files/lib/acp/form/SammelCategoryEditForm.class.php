<?php
namespace wcf\acp\form;

/**
 * Shows the SammelDB category edit form.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategoryEditForm extends AbstractCategoryEditForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.sammel.category.list';
	public $objectTypeName = 'ch.grischamedia.sammel.category';
}
