<?php
namespace wcf\data\sammel;
use wcf\data\DatabaseObject;
use wcf\data\sammel\category\SammelCategory;
use wcf\system\label\object\SammelLabelObjectHandler;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\request\IRouteController;
use wcf\system\WCF;
use wcf\util\MessageUtil;

/**
 * Represents a SammelDB
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class Sammel extends DatabaseObject implements IRouteController {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'sammel';
	protected static $databaseTableIndexName = 'sammelID';
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * get assigned labels
	 */
	public function getLabels() {
		$labels = SammelLabelObjectHandler::getInstance()->getAssignedLabels([$this->sammelID]);
		
		$data = [];
		foreach ($labels as $labelObjects) {
			foreach ($labelObjects as $label) {
				$data[] = $label;
			}
		}
		return $data;
	}
	
	/**
	 * Returns the details.
	 */
	public function getFormattedDetails() {
		$processor = new HtmlOutputProcessor();
		$processor->process($this->details, 'ch.grischamedia.sammel', $this->sammelID);
		
		return $processor->getHtml();
	}
	
	/**
	 * Returns the truncated details.
	 */
	public function getFormattedExcerpt($maxLength = 250) {
		$processor = new HtmlOutputProcessor();
		$processor->process($this->details, 'ch.grischamedia.sammel', $this->sammelID);
		
		return MessageUtil::truncateFormattedMessage($processor->getHtml(), $maxLength);
	}
	
	/**
	 * Returns the location of the icon.
	 */
	public function getIconLocation() {
		if ($this->iconHash) {
			return WCF_DIR . 'sammelImages/' . substr($this->iconHash, 0, 2) . '/' . $this->sammelID . '.' . $this->iconExtension;
		}
		
		return '';
	}
	
	/**
	 * Returns the location of the original icon imgage.
	 */
	public function getIconOrigLocation() {
		if ($this->iconHash) {
			return WCF_DIR . 'sammelImages/' . substr($this->iconHash, 0, 2) . '/' . $this->sammelID . '_orig.' . $this->iconExtension;
		}
		
		return '';
	}
	
	/**
	 * Returns the url of the icon.
	 */
	public function getIconURL() {
		if ($this->iconHash) {
			return WCF::getPath() . 'sammelImages/' . substr($this->iconHash, 0, 2) . '/' . $this->sammelID . '.' . $this->iconExtension;
		}
		
		return '';
	}
	
	/**
	 * Returns the url of the original icon image.
	 */
	public function getIconOrigURL() {
		if ($this->iconHash) {
			return WCF::getPath() . 'sammelImages/' . substr($this->iconHash, 0, 2) . '/' . $this->sammelID . '_orig.' . $this->iconExtension;
		}
		
		return '';
	}
	
	/**
	 * Returns true if current user can edit this sammel.
	 */
	public function canEdit() {
		// check category
		$accessIDs = SammelCategory::getAccessibleCategoryIDs(['canUseCategory']);
		if ($this->categoryID && !in_array($this->categoryID, $accessIDs)) {
			return false;
		}
		// check permission
		if (!WCF::getSession()->getPermission('user.sammel.canEdit')) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns true if current user can see this sammel.
	 */
	public function canSee() {
		// check category
		$accessIDs = SammelCategory::getAccessibleCategoryIDs(['canViewCategory']);
		if ($this->categoryID && !in_array($this->categoryID, $accessIDs)) {
			return false;
		}
		// check permission
		if (!WCF::getSession()->getPermission('user.sammel.canSee')) {
			return false;
		}
		
		return true;
	}
}
