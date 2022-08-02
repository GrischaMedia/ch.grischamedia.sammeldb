<?php
namespace wcf\data\sammel;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit SammelDBs.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	public static $baseClass = Sammel::class;
}
