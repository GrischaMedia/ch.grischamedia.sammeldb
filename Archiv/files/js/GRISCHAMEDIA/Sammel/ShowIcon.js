/**
 * Dialog to show larger version of icon
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2020 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
define(['Ajax', 'Language', 'Ui/Dialog'], function(Ajax, Language, UiDialog) {
	"use strict";
	
	function SammelShowIcon() { this.init(); }
	
	SammelShowIcon.prototype = {
		init: function() {
			var buttons = elBySelAll('.jsSammelIcon');
			for (var i = 0, length = buttons.length; i < length; i++) {
				buttons[i].addEventListener(WCF_CLICK_EVENT, this._showDialog.bind(this));
			}
		},
		
		_showDialog: function(event) {
			event.preventDefault();
			
			Ajax.api(this, {
				actionName:	'getIconDialog',
				parameters:	{
					objectID:	~~elData(event.currentTarget, 'object-id')
				}
			});
		},
		
		_ajaxSuccess: function(data) {
			this._render(data);
		},
		
		_render: function(data) {
			UiDialog.open(this, data.returnValues.template);
		},
		
		_ajaxSetup: function() {
			return {
				data: {
					className: 'wcf\\data\\sammel\\SammelAction'
				}
			};
		},
		
		_dialogSetup: function() {
			return {
				id: 		'SammelIcon',
				options: 	{ title: Language.get('wcf.sammel.preview') },
				source: 	null
			};
		}
	};
	
	return SammelShowIcon;
});
