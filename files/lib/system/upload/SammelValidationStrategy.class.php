<?php
namespace wcf\system\upload;
use wcf\system\upload\DefaultUploadFileValidationStrategy;
use wcf\system\upload\UploadFile;

/**
 * Validates uploaded icons.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
class SammelValidationStrategy extends DefaultUploadFileValidationStrategy {
	/**
	 * Creates a new ValidationStrategy object.
	 */
	public function __construct() {
		parent::__construct(PHP_INT_MAX, ['jpg', 'jpeg', 'png']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate(UploadFile $uploadFile) {
		if (parent::validate($uploadFile)) {
			
			// check if entry is an image
			$imageData = $uploadFile->getImageData();
			if ($imageData === null) {
				$uploadFile->setValidationErrorType('noImage');
				return false;
			}
			
			// check if image is too small
			if ($imageData['height'] < 144 || $imageData['width'] < 144) {
				$uploadFile->setValidationErrorType('tooSmall');
				return false;
			}
			
			// check if image is too large
			if ($uploadFile->getFilesize() > SAMMEL_ICON_MAXSIZE) {
				$uploadFile->setValidationErrorType('tooLarge');
				return false;
			}
		}
		
		return true;
	}
}
