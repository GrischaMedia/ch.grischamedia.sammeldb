<?php
namespace wcf\data\sammel\category;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\IAccessibleObject;
use wcf\data\ITitledLinkObject;
use wcf\system\cache\builder\SammelCategoryLabelCacheBuilder;
use wcf\system\category\CategoryHandler;
use wcf\system\category\CategoryPermissionHandler;
use wcf\system\label\LabelHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Represents an sammel category.
 *
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelCategory extends AbstractDecoratedCategory implements IAccessibleObject, ITitledLinkObject {
	/**
	 * object type name of the sammel categories
	 */
	const OBJECT_TYPE_NAME = 'ch.grischamedia.sammel.category';
	
	/**
	 * acl permissions of this category grouped by the id of the user
	 */
	protected $userPermissions = [];
	
	/**
	 * @inheritDoc
	 */
	public function isAccessible(User $user = null) {
		if ($this->getObjectType()->objectType != self::OBJECT_TYPE_NAME) return false;
		
		// check permissions
		return $this->getPermission('canViewCategory', $user);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPermission($permission, User $user = null) {
		if ($user === null) {
			$user = WCF::getUser();
		}
		
		if (!isset($this->userPermissions[$user->userID])) {
			$this->userPermissions[$user->userID] = CategoryPermissionHandler::getInstance()->getPermissions($this->getDecoratedObject(), $user);
		}
		
		if (isset($this->userPermissions[$user->userID][$permission])) {
			return $this->userPermissions[$user->userID][$permission];
		}
		
		if ($this->getParentCategory()) {
			return $this->getParentCategory()->getPermission($permission, $user);
		}
		
		// map category permission to user group permission
		switch ($permission) {
			case 'canViewCategory':
				$permission = 'canSee';
				break;
				
			case 'canUseCategory':
				$permission = 'canEdit';
				break;
		}
		
		if ($user->userID === WCF::getSession()->getUser()->userID) {
			return WCF::getSession()->getPermission('user.sammel.'.$permission);
		}
		else {
			$userProfile = new UserProfile($user);
			return $userProfile->getPermission('user.sammel.'.$permission);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return LinkHandler::getInstance()->getLink('Sammel', [
			'forceFrontend' => true,
			'object' => $this->getDecoratedObject()
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return WCF::getLanguage()->get($this->title);
	}
	
	/**
	 * Returns a list with ids of accessible categories.
	 */
	public static function getAccessibleCategoryIDs(array $permissions = ['canViewCategory']) {
		$categoryIDs = [];
		
		foreach (CategoryHandler::getInstance()->getCategories(self::OBJECT_TYPE_NAME) as $category) {
			$result = true;
			$category = new SammelCategory($category);
			foreach ($permissions as $permission) {
				$result = $result && $category->getPermission($permission) && !$category->isDisabled;
			}
			
			if ($result) {
				$categoryIDs[] = $category->categoryID;
			}
		}
		
		return $categoryIDs;
	}
	
	/**
	 * Returns the label groups available for sammels in the category.
	 */
	public function getLabelGroups($permission = 'canSetLabel') {
		$labelGroups = [];
		
		$labelGroupsToCategories = SammelCategoryLabelCacheBuilder::getInstance()->getData();
		if (isset($labelGroupsToCategories[$this->categoryID])) {
			$labelGroups = LabelHandler::getInstance()->getLabelGroups($labelGroupsToCategories[$this->categoryID], true, $permission);
		}
		
		return $labelGroups;
	}
	
	/**
	 * Returns the label groups for all accessible categories.
	 */
	public static function getAccessibleLabelGroups($permission = 'canSetLabel') {
		$labelGroupsToCategories = SammelCategoryLabelCacheBuilder::getInstance()->getData();
		$accessibleCategoryIDs = self::getAccessibleCategoryIDs();
		
		$groupIDs = [];
		foreach ($labelGroupsToCategories as $categoryID => $__groupIDs) {
			if (in_array($categoryID, $accessibleCategoryIDs)) {
				$groupIDs = array_merge($groupIDs, $__groupIDs);
			}
		}
		if (empty($groupIDs)) return [];
		
		return LabelHandler::getInstance()->getLabelGroups(array_unique($groupIDs), true, $permission);
	}
}
