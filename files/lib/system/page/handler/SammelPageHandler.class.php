<?php
namespace wcf\system\page\handler;
use wcf\system\WCF;

/**
 * Page handler for SammelDB.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelPageHandler extends AbstractMenuPageHandler {
	/**
	 * @inheritDoc
	 */
	public function isVisible($objectID = null) {
		if (WCF::getSession()->getPermission('user.sammel.canEdit')) return true;
		if (WCF::getSession()->getPermission('user.sammel.canSee')) return true;
		
		return false;
	}
}
