jQuery(document).ready(function() {
	'use strict';
	
	jQuery("#bulk-action-selector-top").live('change',function(){
		var selectVal = jQuery('#bulk-action-selector-top :selected').val();
		if(selectVal=='change_term'){
			var loaderContainer = jQuery( '<span/>', {
			'class': 'loader-image-container'
			}).insertAfter( "#bulk-action-selector-top" );
			var loader = jQuery( '<img/>', {
			src: wpmc_admin_js.spinner_url,
			'class': 'loader-image'
			}).appendTo( loaderContainer );

			jQuery.ajax({
				type: "post",
				url: wpmc_admin_js.ajax_url,
				dataType:'text',
				data:{ action:'list_terms' },
				success: function(result) {
					jQuery(loaderContainer).hide();
					jQuery(result).insertAfter("#bulk-action-selector-top");
				}
			});
		} else {
			jQuery('#terms_cat').hide();
		}
	});

	jQuery("#bulk-action-selector-bottom").live('change',function(){
		var selectVal = jQuery('#bulk-action-selector-bottom :selected').val();
		if(selectVal=='change_term'){
			var loaderContainer = jQuery( '<span/>', {
			'class': 'loader-image-container'
			}).insertAfter( "#bulk-action-selector-bottom" );
			var loader = jQuery( '<img/>', {
			src: wpmc_admin_js.spinner_url,
			'class': 'loader-image'
			}).appendTo( loaderContainer );

			jQuery.ajax({
				type: "post",
				url: wpmc_admin_js.ajax_url,
				dataType:'text',
				data:{ action:'list_terms' },
				success: function(result) {
					jQuery(loaderContainer).hide();
					jQuery(result).insertAfter("#bulk-action-selector-bottom");
				}
			});
		} else {
			jQuery('#terms_cat').hide();
		}
	});
});
