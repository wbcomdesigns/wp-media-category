<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Wp_Media_Category
 * @subpackage Wp_Media_Category/admin
 */

if ( ! class_exists( 'Wp_Media_Category_Admin' ) ) :

    /**
     * The admin-specific functionality of the plugin.
     *
     * Defines the plugin name, version, and hooks for admin functionality.
     *
     * @package    Wp_Media_Category
     * @subpackage Wp_Media_Category/admin
     * @author     Wbcom Designs <admin@wbcomdesigns.com>
     */
    class Wp_Media_Category_Admin {

        /**
         * The ID of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $plugin_name    The ID of this plugin.
         */
        private $plugin_name;

        /**
         * The version of this plugin.
         *
         * @since    1.0.0
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
        private $version;

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.0.0
         * @param    string $plugin_name       The name of this plugin.
         * @param    string $version           The version of this plugin.
         */
        public function __construct( $plugin_name, $version ) {
            $this->plugin_name = $plugin_name;
            $this->version     = $version;
            
            // Add filters for body classes
            add_filter( 'admin_body_class', array( $this, 'wpmc_add_body_class' ) );
            add_filter( 'body_class', array( $this, 'wpmc_add_body_class_for_video' ) );
        }

        /**
         * Register the stylesheets for the admin area.
         *
         * Only loads on relevant admin pages.
         *
         * @since    1.0.0
         */
        public function wpmc_enqueue_styles() {
            global $pagenow;
            
            // Only load on relevant admin pages
            if ( in_array( $pagenow, array( 'upload.php', 'post.php', 'post-new.php' ), true ) ) {
                wp_enqueue_media();
                wp_enqueue_style(
                    $this->plugin_name,
                    plugin_dir_url( __FILE__ ) . 'css/wp-media-category-admin.css',
                    array(),
                    $this->version,
                    'all'
                );
            }
        }

        /**
         * Register the JavaScript for the admin area.
         *
         * Only loads on relevant admin pages.
         *
         * @since    1.0.0
         */
        public function wpmc_enqueue_scripts() {
            global $pagenow;

            // Only load on relevant admin pages
            if ( in_array( $pagenow, array( 'upload.php', 'post.php', 'post-new.php' ), true ) ) {
                wp_enqueue_script(
                    $this->plugin_name,
                    plugin_dir_url( __FILE__ ) . 'js/wp-media-category-admin.js',
                    array( 'jquery' ),
                    $this->version,
                    true // Load in footer for better performance
                );
                
                // Get terms for the media categories
                $terms = get_terms( array(
                    'taxonomy'   => 'media_category',
                    'hide_empty' => false,
                ) );
                
                wp_localize_script(
                    $this->plugin_name,
                    'wpmc_admin_js',
                    array(
                        'ajax_url'    => admin_url( 'admin-ajax.php' ),
                        'nonce'       => wp_create_nonce( 'wpmc_nonce' ),
                        'spinner_url' => admin_url( 'images/spinner.gif' ),
                        'terms'       => is_wp_error( $terms ) ? array() : $terms,
                    )
                );
            }
        }

        /**
         * Add body class for media category pages.
         *
         * @param string $classes Current body classes.
         * @return string Modified body classes.
         */
        public function wpmc_add_body_class( $classes ) {
            global $pagenow;
            
            if ( in_array( $pagenow, array( 'post.php', 'post-new.php' ), true ) ) {
                $classes .= ' wp-media-category';
            }
            
            return $classes;
        }

        /**
         * Register the media category taxonomy.
         *
         * @since 1.0.0
         */
        public function wpmc_create_media_taxonomy() {
            $labels = array(
                'name'              => esc_html__( 'Media Categories', 'media-category' ),
                'singular_name'     => esc_html__( 'Media Category', 'media-category' ),
                'search_items'      => esc_html__( 'Search Media Categories', 'media-category' ),
                'all_items'         => esc_html__( 'All Media Categories', 'media-category' ),
                'parent_item'       => esc_html__( 'Parent Media Category', 'media-category' ),
                'parent_item_colon' => esc_html__( 'Parent Media Category:', 'media-category' ),
                'edit_item'         => esc_html__( 'Edit Media Category', 'media-category' ),
                'update_item'       => esc_html__( 'Update Media Category', 'media-category' ),
                'add_new_item'      => esc_html__( 'Add New Media Category', 'media-category' ),
                'new_item_name'     => esc_html__( 'New Media Category Name', 'media-category' ),
                'menu_name'         => esc_html__( 'Media Category', 'media-category' ),
            );

            $args = array(
                'hierarchical'          => true,
                'show_ui'               => true,
                'show_admin_column'     => true,
                'public'                => false,
                'show_in_nav_menus'     => true,
                'query_var'             => true,
                'rewrite'               => array( 'slug' => 'media_category' ),
                'update_count_callback' => '_update_generic_term_count',
                'labels'                => $labels,
                'show_in_rest'          => true, // Support for block editor
            );
            
            register_taxonomy( 'media_category', array( 'attachment' ), $args );
            
            // Register default 'Uncategorized' term if it doesn't exist
            if ( ! term_exists( 'Uncategorized', 'media_category' ) ) {
                wp_insert_term(
                    esc_html__( 'Uncategorized', 'media-category' ),
                    'media_category',
                    array(
                        'slug' => 'uncategorized',
                    )
                );
            }
        }

        /**
         * Display notices after bulk term changes.
         *
         * @since 1.0.0
         */
        public function wpmc_bulk_change_term_media_notices() {
            global $pagenow;
            
            if ( 'upload.php' === $pagenow && isset( $_REQUEST['change_term'] ) && absint( $_REQUEST['change_term'] ) > 0 ) {
                $change_term = absint( $_REQUEST['change_term'] );
                
                $message = sprintf(
                    /* translators: %s: number of attachments */
                    _n(
                        '%s attachment category changed.',
                        '%s attachments category changed.',
                        $change_term,
                        'media-category'
                    ),
                    number_format_i18n( $change_term )
                );
                
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            }
        }

        /**
         * AJAX handler for listing media categories.
         *
         * @since 1.0.0
         */
        public function wpmc_list_terms() {
            // Verify nonce
            check_ajax_referer( 'wpmc_nonce', 'nonce' );
            
            // Check permissions
            if ( ! current_user_can( 'upload_files' ) ) {
                wp_send_json_error( 'Permission denied' );
            }
            
            $terms = get_terms(
                array(
                    'taxonomy'   => 'media_category',
                    'hide_empty' => false,
                )
            );
            
            ob_start();
            
            echo '<select class="terms_form" name="terms" id="terms_cat">';
            
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                foreach ( $terms as $term_obj ) {
                    echo '<option value="' . esc_attr( $term_obj->name ) . '">' . esc_html( $term_obj->name ) . '</option>';
                }
            }
            
            echo '</select>';
            
            $select_html = ob_get_clean();
            wp_send_json_success( $select_html );
        }

        /**
         * Add bulk action for media category changes.
         *
         * @param array $bulk_actions Current bulk actions.
         * @return array Modified bulk actions.
         */
        public function wpmc_add_media_category_bulk_action( $bulk_actions ) {
            $bulk_actions['change_term'] = esc_html__( 'Change Media Category', 'media-category' );
            return $bulk_actions;
        }

        /**
         * Handle bulk action for media category changes.
         *
         * @param string $redirect_to Redirect URL.
         * @param string $action_name Action name.
         * @param array  $post_ids Selected post IDs.
         * @return string Modified redirect URL.
         */
        public function wpmc_media_category_bulk_action_handler( $redirect_to, $action_name, $post_ids ) {
            if ( 'change_term' === $action_name && ! empty( $post_ids ) ) {
                // Verify term is set
                $terms = isset( $_GET['terms'] ) ? sanitize_text_field( wp_unslash( $_GET['terms'] ) ) : '';
                
                if ( ! empty( $terms ) ) {
                    $taxonomy = 'media_category';
                    $updated_count = 0;
                    
                    foreach ( $post_ids as $post_id ) {
                        $post_id = absint( $post_id );
                        
                        if ( wp_set_object_terms( $post_id, $terms, $taxonomy ) ) {
                            $updated_count++;
                        }
                    }
                    
                    // Add count to redirect URL
                    $redirect_to = add_query_arg( 'bulk_media_category_processed', $updated_count, $redirect_to );
                }
            }
            
            return $redirect_to;
        }

        /**
         * Display notice after bulk update.
         *
         * @since 1.0.0
         */
        public function wpmc_updated_media_category() {
            if ( ! empty( $_REQUEST['bulk_media_category_processed'] ) ) {
                $posts_count = absint( $_REQUEST['bulk_media_category_processed'] );
                
                $message = sprintf(
                    /* translators: %d: Number of media items */
                    _n(
                        'Updated media category for %d item.',
                        'Updated media category for %d items.',
                        $posts_count,
                        'media-category'
                    ),
                    $posts_count
                );
                
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
            }
        }

        /**
         * Add category filter to media list screen.
         *
         * @since 1.0.0
         */
        public function wpmc_add_media_category_filter() {
            $screen = get_current_screen();
            
            if ( ! $screen || 'upload' !== $screen->base ) {
                return;
            }

            $taxonomy = 'media_category';
            $tax_obj = get_taxonomy( $taxonomy );
            
            if ( ! $tax_obj ) {
                return;
            }
            
            $tax_name = $tax_obj->labels->name;
            $terms = get_terms( array( 
                'taxonomy' => $taxonomy, 
                'hide_empty' => false 
            ) );

            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                return;
            }
            
            // Get current selected term
            $current_term = isset( $_GET[$taxonomy] ) ? sanitize_text_field( wp_unslash( $_GET[$taxonomy] ) ) : '';
            
            // Build dropdown
            ?>
            <select name="<?php echo esc_attr( $taxonomy ); ?>" id="<?php echo esc_attr( $taxonomy ); ?>" class="postform">
                <option value=""><?php echo esc_html( sprintf( __( 'All %s', 'media-category' ), $tax_name ) ); ?></option>
                <option value="0" <?php selected( '0', $current_term ); ?>><?php esc_html_e( 'Uncategorized', 'media-category' ); ?></option>
                
                <?php foreach ( $terms as $term ) : ?>
                    <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $term->slug, $current_term ); ?>>
                        <?php echo esc_html( $term->name . ' (' . $term->count . ')' ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        }

        /**
         * Add body class for pages with media shortcode.
         *
         * @param array $classes Current body classes.
         * @return array Modified body classes.
         */
        public function wpmc_add_body_class_for_video( $classes ) {
            global $post;
            
            if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'wbmedia' ) ) {
                $classes[] = 'wbmedia-shortcode';
            }
            
            return $classes;
        }

        /**
         * Filter to show only uncategorized media.
         *
         * @param WP_Query $query The WordPress query object.
         */
        public function filter_media_without_taxonomy( $query ) {
            // Only run in admin and on main query
            if ( ! is_admin() || ! $query->is_main_query() ) {
                return;
            }
            
            // Check if we're filtering for uncategorized media
            if ( isset( $_GET['media_category'] ) && '0' === $_GET['media_category'] ) {
                if ( 'attachment' === $query->get( 'post_type' ) ) {
                    $taxonomy = 'media_category';
                    
                    // Set tax query to find items without the taxonomy
                    $query->set( 'tax_query', array(
                        array(
                            'taxonomy' => $taxonomy,
                            'operator' => 'NOT EXISTS',
                        ),
                    ) );
                }
            }
        }
    }
endif;