jQuery(document).ready(function() {
	'use strict';
	jQuery('<option>').val('change_term').text('Change Media Category').appendTo("select[name='action']");
	jQuery('<option>').val('change_term').text('Change Media Category').appendTo("select[name='action2']");

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
});
