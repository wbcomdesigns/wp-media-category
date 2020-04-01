(function() {
  var MediaLibraryTaxonomyFilter = wp.media.view.AttachmentFilters.extend({
    id: 'media-attachment-taxonomy-filter',

    createFilters: function() {
      var filters = {};
      // Formats the 'terms' we've included via wp_localize_script()
      _.each(wpmc_admin_js.terms || {}, function(value, index) {
        filters[index] = {
          text: value.name,
          props: {
            // Change this: key needs to be the WP_Query var for the taxonomy
            media_category: value.slug,
          }
        };
      });
      filters.all = {
        text: 'Show all Media Categories',
        props: {
          // Change this: key needs to be the WP_Query var for the taxonomy
          media_category: ''
        },
        priority: 10
      };
      this.filters = filters;
    }
  });
  /**
   * Extend and override wp.media.view.AttachmentsBrowser to include our new filter
   */
  var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
  wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
    createToolbar: function() {
      // Make sure to load the original toolbar
      AttachmentsBrowser.prototype.createToolbar.call(this);
      this.toolbar.set('MediaLibraryTaxonomyFilter', new MediaLibraryTaxonomyFilter({
        controller: this.controller,
        model: this.collection.props,
        priority: -75
      }).render());
    }
  });
})()

jQuery(document).ready(function() {
  'use strict';
  jQuery("#bulk-action-selector-top").live('change', function() {
    var selectVal = jQuery('#bulk-action-selector-top :selected').val();
    if (selectVal == 'change_term') {
      var loaderContainer = jQuery('<span/>', {
        'class': 'loader-image-container'
      }).insertAfter("#bulk-action-selector-top");
      var loader = jQuery('<img/>', {
        src: wpmc_admin_js.spinner_url,
        'class': 'loader-image'
      }).appendTo(loaderContainer);

      jQuery.ajax({
        type: "post",
        url: wpmc_admin_js.ajax_url,
        dataType: 'text',
        data: {
          action: 'list_terms'
        },
        success: function(result) {
          jQuery(loaderContainer).hide();
          jQuery(result).insertAfter("#bulk-action-selector-top");
        }
      });
    } else {
      jQuery('#terms_cat').hide();
    }
  });

  jQuery("#bulk-action-selector-bottom").live('change', function() {
    var selectVal = jQuery('#bulk-action-selector-bottom :selected').val();
    if (selectVal == 'change_term') {
      var loaderContainer = jQuery('<span/>', {
        'class': 'loader-image-container'
      }).insertAfter("#bulk-action-selector-bottom");
      var loader = jQuery('<img/>', {
        src: wpmc_admin_js.spinner_url,
        'class': 'loader-image'
      }).appendTo(loaderContainer);

      jQuery.ajax({
        type: "post",
        url: wpmc_admin_js.ajax_url,
        dataType: 'text',
        data: {
          action: 'list_terms'
        },
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