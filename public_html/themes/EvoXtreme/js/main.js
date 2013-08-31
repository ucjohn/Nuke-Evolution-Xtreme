/**
 * JavaScript for the EvoXtreme theme
 *
 * @filename        main.js
 * @author          SgtLegend
 * @version         3.0
 * @date            06/08/2011 (DD/MM/YYY)
 * @license         Copyright (c) 2011 SgtLegend under the MIT license
 * @notes           n/a
 */

$jq(function($) {
	
	// Internet Explorer 7/8 animation effects
	if ($.browser.msie && $.browser.version < '9.0') {
		
		// Main header navigation fadeIn/Out effect to match the smooth
		// CSS3 tranisition
		$('a', 'header nav').hover(function() {
			$(this)
				.filter(':not(:animated)')
				.animate({
					backgroundColor : '#102B3A',
					color : '#81D3FF',
					height : '24px',
					lineHeight : '24px'
				}, 200);
		}, function() {
			$(this).animate({
				backgroundColor : '#396a83',
				color : '#fff'
			}, 200);
		});
		
	}
	
});