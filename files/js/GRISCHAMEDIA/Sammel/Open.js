/**
 * Opens a details in table.
 * 
 * @author		GrischaMedia.ch
 * @copyright	2019-2021 GrischaMedia.ch
 * @license		GrischaMedia.ch Commercial License <https://GrischaMedia.ch.de>
 * @package		ch.grischamedia.sammeldb
 */
define(['Ajax'], function(Ajax) {
	"use strict";
	
	function SammelOpen() { this.init(); }
	
	SammelOpen.prototype = {
			init: function() {
				var buttons = elBySelAll('.jsOpenButton');
				for (var i = 0, length = buttons.length; i < length; i++) {
					buttons[i].addEventListener(WCF_CLICK_EVENT, this._click.bind(this));
				}
			},
			
			_ajaxSetup: function() {
				return {
					data: {
						actionName:	'open',
						className:	'wcf\\data\\sammel\\SammelAction'
					}
				};
			},
			
			_ajaxSuccess: function(data) {
				// set full details
				var row = document.getElementById(data.returnValues.id);
				row.innerHTML = data.returnValues.text;
			},
			
			_click: function(event) {
				var objectID = elData(event.currentTarget, 'object-id');
				
				Ajax.api(this, {
					parameters: {
						objectID: objectID
					}
				});
			}
		};
	return SammelOpen;
});
