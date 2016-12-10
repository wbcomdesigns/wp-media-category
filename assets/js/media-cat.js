jQuery(document).ready(function() {
	jQuery("#bulk-action-selector-top").live('change',function(){
		var selectVal = jQuery('#bulk-action-selector-top :selected').val();
	    if(selectVal=='change_term'){
	    	var loaderContainer = jQuery( '<span/>', {
	        	'class': 'loader-image-container'
	        }).insertAfter( "#bulk-action-selector-top" );
	    	var loader = jQuery( '<img/>', {
	            src: url.spinner_url,
	            'class': 'loader-image'
	        }).appendTo( loaderContainer );

	    	jQuery.ajax({
	        	type: "post",
	        	url: ajax_object.ajax_url,
	        	dataType:'text',
	        	data:{action:'list_terms'},
	            success: function(result) {
	            	jQuery(loaderContainer).hide();
	        	    jQuery(result).insertAfter("#bulk-action-selector-top");
	      		}
    		});
	    }
	    else{
	    	jQuery('#terms_cat').hide();
	    }
	});
});
