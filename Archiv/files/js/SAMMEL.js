/**
 * JS functions for SammelDB
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
if (!SAMMEL) var SAMMEL = {};

/**
 * Copy of WCF.Label.Chooser by WoltLab with minor changes
 * 
 */
/**
 * Provides a flexible label chooser.
 */
SAMMEL.SammelLabelChooser = WCF.Label.Chooser.extend({
	_labelGroupsToCategories: null,
	
	/**
	 * Initializes a new WCF.Label.ArticleLabelChooser object.
	 */
	init: function (labelGroupsToCategories, selectedLabelIDs, containerSelector, submitButtonSelector, showWithoutSelection) {
		this._super(selectedLabelIDs, containerSelector, submitButtonSelector, showWithoutSelection);
		this._labelGroupsToCategories = labelGroupsToCategories;
		
		this._updateLabelGroups();
		
		$('#categoryID').change($.proxy(this._updateLabelGroups, this));
	},
	
	/**
	 * Updates the visible label groups based on the selected categories.
	 */
	_updateLabelGroups: function () {
		// hide all label choosers first
		$('.labelChooser').each(function (index, element) {
			$(element).parents('dl:eq(0)').hide();
		});
		
		var visibleGroupIDs = [];
		var categoryID = parseInt($('#categoryID').val());
		
		if (this._labelGroupsToCategories[categoryID]) {
			for (var i = 0, length = this._labelGroupsToCategories[categoryID].length; i < length; i++) {
				$('#labelGroup' + this._labelGroupsToCategories[categoryID][i]).parents('dl:eq(0)').show();
			}
		}
	},
	
	/**
	 * @see        WCF.Label.Chooser._submit()
	 */
	_submit: function () {
		// delete non-selected groups to avoid submitting these labels
		for (var groupID in this._groups) {
			if (!this._groups[groupID].is(':visible')) {
				delete this._groups[groupID];
			}
		}
		
		this._super();
	}
});

/**
 * Handles uploading icons.
 */
SAMMEL.IconUpload = WCF.Upload.extend({
	/**
	 * button to delete the current icon
	 */
	_deleteSammelIconButton: null,
	
	/**
	 * id of the item the uploaded icon belongs to
	 */
	_sammelID: 0,
	
	/**
	 * icon element
	 */
	_icon: null,
	
	/**
	 * temporary hash
	 */
	_tmpHash: '',
	
	/**
	 * Initializes a new object.
	 */
	init: function (sammelID, tmpHash) {
		this._sammelID = sammelID;
		this._tmpHash = tmpHash;
		this._icon = $('#sammelIcon');
		this._deleteSammelIconButton = $('#deleteSammelIcon').click($.proxy(this._confirmDeleteIcon, this));
		
		this._super($('#sammelIconUploadButton'), $('<ul />'), 'wcf\\data\\sammel\\SammelAction', {action: 'uploadIcon'});
	},
	
	/**
	 * @see WCF.Upload. _getParameters()
	 */
	_getParameters: function () {
		return {
			sammelID: this._sammelID,
			tmpHash: this._tmpHash
		};
	},
	
	/**
	 * @see WCF.Upload._success()
	 */
	_success: function (uploadID, data) {
		if (data.returnValues.url) {
			// show image
			this._getIcon().show().attr('src', data.returnValues.url + '?timestamp=' + Date.now());
			
			// hide error
			this._buttonSelector.next('.innerError').remove();
			
			// show success message
			var $notification = new WCF.System.Notification(WCF.Language.get('wcf.global.success'));
			$notification.show();
			
			this._deleteSammelIconButton.show();
		}
		else if (data.returnValues.errorType) {
			this._getInnerErrorElement().text(WCF.Language.get('wcf.sammel.icon.error.' + data.returnValues.errorType));
		}
	},
	
	/**
	 * @see WCF.Upload._upload()
	 */
	_upload: function () {
		this._super();
		
		if (this._fileUpload) {
			this._removeButton();
			this._createButton();
		}
	},
	
	/**
	 * Returns the icon element.
	 */
	_getIcon: function () {
		if (!this._icon.length) {
			this._icon = $('<img src="" alt="" id="sammelIcon" />').prependTo($('#sammelIconUpload > dd'));
		}
		
		return this._icon;
	},
	
	/**
	 * Returns the inner error element for the icon.
	 */
	_getInnerErrorElement: function () {
		var $span = $('#sammelIconUploadButton').next('.innerError');
		if (!$span.length) {
			$span = $('<small class="innerError" />').insertAfter($('#sammelIconUploadButton'));
		}
		
		return $span;
	},
	
	/**
	 * Confirms deleting the current icon.
	 */
	_confirmDeleteIcon: function (event) {
		event.preventDefault();
		
		WCF.System.Confirmation.show(WCF.Language.get('wcf.sammel.icon.delete.confirmMessage'), $.proxy(function (action) {
			if (action === 'confirm') {
				this._deleteIcon();
			}
		}, this));
	},
	
	/**
	 * Deletes the current icon.
	 */
	_deleteIcon: function () {
		new WCF.Action.Proxy({
			autoSend: true,
			data: {
				actionName: 'deleteIcon',
				className: 'wcf\\data\\sammel\\SammelAction',
				parameters: this._getParameters()
			}
		});
		
		this._deleteSammelIconButton.hide();
		this._icon.hide();
	},
});
