/**
 * WordPress Media Category Admin JavaScript
 *
 * Adds media filtering capabilities and bulk actions for media categories
 */
(function($, wp) {
  'use strict';

  /**
   * Media Library Taxonomy Filter
   * Extends the WordPress media library filters to include our custom taxonomy
   */
  var MediaLibraryTaxonomyFilter = wp.media.view.AttachmentFilters.extend({
    id: 'media-attachment-taxonomy-filter',
    className: 'attachment-filters attachment-category-filter',

    createFilters: function() {
      var filters = {};
      
      // Add "All" option
      filters.all = {
        text: wp.i18n.__('All Media Categories', 'media-category'),
        props: {
          media_category: ''
        },
        priority: 10
      };

      // Add "Uncategorized" option
      filters.uncategorized = {
        text: wp.i18n.__('Uncategorized', 'media-category'),
        props: {
          media_category: '0'
        },
        priority: 15
      };
      
      // Add all taxonomy terms from localized script data
      if (wpmc_admin_js.terms && typeof wpmc_admin_js.terms === 'object') {
        _.each(wpmc_admin_js.terms, function(term) {
          // Skip if term is missing required properties
          if (!term || !term.slug || !term.name) {
            return;
          }

          filters[term.slug] = {
            text: term.name,
            props: {
              media_category: term.slug
            },
            priority: 20
          };
        });
      }
      
      this.filters = filters;
    }
  });

  /**
   * Extend and override wp.media.view.AttachmentsBrowser to include our filter
   */
  var AttachmentsBrowser = wp.media.view.AttachmentsBrowser;
  wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
    createToolbar: function() {
      // Call the original function to maintain core functionality
      AttachmentsBrowser.prototype.createToolbar.call(this);
      
      // Add our custom filter
      this.toolbar.set('MediaLibraryTaxonomyFilter', new MediaLibraryTaxonomyFilter({
        controller: this.controller,
        model: this.collection.props,
        priority: -75
      }).render());
    }
  });

  /**
   * Bulk Actions Handler
   * Manages the display of category dropdown for bulk actions
   */
  $(document).ready(function() {
    // Common function to handle both top and bottom bulk actions
    function handleBulkActionChange(selectElement) {
      var $select = $(selectElement);
      var selectVal = $select.val();
      
      // Remove any existing category dropdown
      $('#terms_cat').remove();
      
      // Remove any existing loader
      $('.loader-image-container').remove();
      
      if (selectVal === 'change_term') {
        // Add loader
        var $loaderContainer = $('<span/>', {
          'class': 'loader-image-container',
          'aria-hidden': 'true'
        }).insertAfter($select);
        
        var $loader = $('<img/>', {
          src: wpmc_admin_js.spinner_url,
          'class': 'loader-image',
          alt: wp.i18n.__('Loading...', 'media-category')
        }).appendTo($loaderContainer);
        
        // Fetch categories via AJAX
        $.ajax({
          type: 'POST',
          url: wpmc_admin_js.ajax_url,
          dataType: 'json',
          data: {
            action: 'list_terms',
            nonce: wpmc_admin_js.nonce
          },
          success: function(response) {
            $loaderContainer.remove();
            
            if (response.success && response.data) {
              $(response.data).insertAfter($select);
            } else {
              console.error('Error loading media categories');
            }
          },
          error: function(xhr, status, error) {
            $loaderContainer.remove();
            console.error('AJAX error:', error);
          }
        });
      }
    }
    
    // Bind events for top bulk action dropdown
    $('body').on('change', '#bulk-action-selector-top', function() {
      handleBulkActionChange(this);
    });
    
    // Bind events for bottom bulk action dropdown
    $('body').on('change', '#bulk-action-selector-bottom', function() {
      handleBulkActionChange(this);
    });
    
    // Handle clicking away from the dropdown
    $(document).on('click', function(event) {
      if (!$(event.target).closest('.bulkactions').length && 
          !$(event.target).is('#terms_cat')) {
        $('#terms_cat').hide();
      }
    });
  });

})(jQuery, wp);