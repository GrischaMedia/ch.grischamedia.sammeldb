<?php
namespace wcf\data\sammel;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of SammelDBs.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Sammel::class;
}
