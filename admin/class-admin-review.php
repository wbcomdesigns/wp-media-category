<?php
/**
 * Plugin review class.
 * Prompts users to give a review of the plugin on WordPress.org after a period of usage.
 *
 * @package Wp_Media_Category
 * @since 1.2.0
 */

if ( ! class_exists( 'WP_Media_Category_Feedback' ) ) :

    /**
     * The feedback notification class.
     */
    class WP_Media_Category_Feedback {

        /**
         * Slug for identifying plugin options.
         *
         * @var string $slug
         */
        private $slug;

        /**
         * Plugin name for display.
         *
         * @var string $name
         */
        private $name;

        /**
         * Time before showing the notice.
         *
         * @var int $time_limit
         */
        private $time_limit;

        /**
         * Option name for "don't show again".
         *
         * @var string $nobug_option
         */
        public $nobug_option;

        /**
         * Option name for install date.
         *
         * @var string $date_option
         */
        public $date_option;

        /**
         * Class constructor.
         *
         * @param array $args Arguments to configure the notice.
         */
        public function __construct( $args ) {
            $this->slug = isset( $args['slug'] ) ? sanitize_key( $args['slug'] ) : 'wp_media_category_feedback';
            $this->name = isset( $args['name'] ) ? sanitize_text_field( $args['name'] ) : __( 'WordPress Media Category', 'media-category' );

            $this->date_option  = $this->slug . '_activation_date';
            $this->nobug_option = $this->slug . '_no_bug';

            $this->time_limit = isset( $args['time_limit'] ) ? absint( $args['time_limit'] ) : WEEK_IN_SECONDS * 2;

            // Add actions.
            add_action( 'admin_init', array( $this, 'check_installation_date' ) );
            add_action( 'admin_init', array( $this, 'set_no_bug' ), 5 );
        }

        /**
         * Convert seconds to human-readable time.
         *
         * @param int $seconds Seconds to convert.
         * @return string Human-readable time difference.
         */
        public function seconds_to_words( $seconds ) {
            $seconds = absint( $seconds );

            // Get the years.
            $years = ( $seconds / YEAR_IN_SECONDS ) % 100;
            if ( $years > 1 ) {
                /* translators: %s: Number of years */
                return sprintf( __( '%s years', 'media-category' ), floor( $years ) );
            } elseif ( $years > 0 ) {
                return __( 'a year', 'media-category' );
            }

            // Get the weeks.
            $weeks = ( $seconds / WEEK_IN_SECONDS ) % 52;
            if ( $weeks > 1 ) {
                /* translators: %s: Number of weeks */
                return sprintf( __( '%s weeks', 'media-category' ), floor( $weeks ) );
            } elseif ( $weeks > 0 ) {
                return __( 'a week', 'media-category' );
            }

            // Get the days.
            $days = ( $seconds / DAY_IN_SECONDS ) % 7;
            if ( $days > 1 ) {
                /* translators: %s: Number of days */
                return sprintf( __( '%s days', 'media-category' ), floor( $days ) );
            } elseif ( $days > 0 ) {
                return __( 'a day', 'media-category' );
            }

            // Get the hours.
            $hours = ( $seconds / HOUR_IN_SECONDS ) % 24;
            if ( $hours > 1 ) {
                /* translators: %s: Number of hours */
                return sprintf( __( '%s hours', 'media-category' ), floor( $hours ) );
            } elseif ( $hours > 0 ) {
                return __( 'an hour', 'media-category' );
            }

            // Get the minutes.
            $minutes = ( $seconds / MINUTE_IN_SECONDS ) % 60;
            if ( $minutes > 1 ) {
                /* translators: %s: Number of minutes */
                return sprintf( __( '%s minutes', 'media-category' ), floor( $minutes ) );
            } elseif ( $minutes > 0 ) {
                return __( 'a minute', 'media-category' );
            }

            // Get the seconds.
            $seconds = $seconds % 60;
            if ( $seconds > 1 ) {
                /* translators: %s: Number of seconds */
                return sprintf( __( '%s seconds', 'media-category' ), $seconds );
            } elseif ( $seconds > 0 ) {
                return __( 'a second', 'media-category' );
            }
            
            return '';
        }

        /**
         * Check installation date and add notice if it exceeds the time limit.
         */
        public function check_installation_date() {
            // No need to check if user has already dismissed or we're not in admin
            if ( ! is_admin() || get_site_option( $this->nobug_option ) ) {
                return;
            }

            // If activation date doesn't exist, create it
            $install_date = get_site_option( $this->date_option );
            if ( ! $install_date ) {
                add_site_option( $this->date_option, time() );
                return;
            }

            // If time since installation is greater than the time limit, display notice
            if ( ( time() - $install_date ) > $this->time_limit ) {
                add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
            }
        }

        /**
         * Display the admin notice.
         */
        public function display_admin_notice() {
            $screen = get_current_screen();

            // Only show on the plugins page
            if ( ! isset( $screen->base ) || 'plugins' !== $screen->base ) {
                return;
            }

            $no_bug_url = wp_nonce_url( admin_url( '?' . $this->nobug_option . '=true' ), 'media-category-feedback-nonce' );
            $time       = $this->seconds_to_words( time() - get_site_option( $this->date_option ) );
            $review_url = 'https://wordpress.org/support/plugin/media-category/reviews/';
            ?>
            <div class="notice updated media-category-notice">
                <div class="media-category-notice-inner">
                    <div class="media-category-notice-icon">
                        <img src="<?php echo esc_url( WPMC_PLUGIN_URL . 'admin/images/wbcom.png' ); ?>" alt="<?php echo esc_attr__( 'WordPress Media Category', 'media-category' ); ?>" />
                    </div>
                    <div class="media-category-notice-content">
                        <h3><?php echo esc_html__( 'Are you enjoying WordPress Media Category?', 'media-category' ); ?></h3>
                        <p>
                            <?php
                            /* translators: 1. Plugin name, 2. Time period */
                            printf(
                                esc_html__( 'You have been using %1$s for %2$s now! If you find it helpful, please consider leaving a review. Your feedback helps improve the plugin and reach more users!', 'media-category' ),
                                '<strong>' . esc_html( $this->name ) . '</strong>',
                                esc_html( $time )
                            );
                            ?>
                        </p>
                    </div>
                    <div class="media-category-install-now">
                        <a href="<?php echo esc_url( $review_url ); ?>" class="button button-primary media-category-install-button" target="_blank">
                            <?php echo esc_html__( 'Leave a Review', 'media-category' ); ?>
                        </a>
                        <a href="<?php echo esc_url( $no_bug_url ); ?>" class="no-thanks">
                            <?php echo esc_html__( 'No thanks / I already did', 'media-category' ); ?>
                        </a>
                    </div>
                </div>
            </div>
            <style>
            .notice.media-category-notice {
                border-left-color: #0073aa !important;
                padding: 20px;
                background-color: #f8f8f8;
            }
            .rtl .notice.media-category-notice {
                border-right-color: #0073aa !important;
                border-left-color: #e5e5e5 !important;
            }
            .media-category-notice-inner {
                display: flex;
                align-items: center;
            }
            .media-category-notice-icon {
                flex: 0 0 64px;
                margin-right: 20px;
            }
            .media-category-notice-icon img {
                width: 64px;
                height: auto;
                border-radius: 4px;
            }
            .media-category-notice-content {
                flex: 1;
                padding-right: 20px;
            }
            .media-category-notice-content h3 {
                margin: 0 0 8px;
                font-size: 16px;
            }
            .media-category-notice-content p {
                margin: 0;
                padding: 0;
                color: #555;
            }
            .media-category-install-now {
                flex: 0 0 180px;
                text-align: center;
            }
            .media-category-install-now .media-category-install-button {
                display: block;
                padding: 8px 15px;
                height: auto;
                line-height: 20px;
                width: 100%;
                text-align: center;
            }
            .media-category-install-now .no-thanks {
                display: block;
                margin-top: 8px;
                color: #72777c;
                text-decoration: none;
                font-size: 12px;
            }
            .media-category-install-now .no-thanks:hover {
                color: #444;
            }
            @media (max-width: 767px) {
                .media-category-notice-inner {
                    display: block;
                }
                .media-category-notice-icon {
                    margin-bottom: 15px;
                    margin-right: 0;
                }
                .media-category-notice-content {
                    padding-right: 0;
                    margin-bottom: 15px;
                }
                .media-category-install-now {
                    width: 100%;
                    text-align: left;
                }
            }
            </style>
            <?php
        }

        /**
         * Set the plugin to no longer display the notice.
         */
        public function set_no_bug() {
            // Verify the nonce and check if user has required capability
            if ( ! isset( $_GET['_wpnonce'] ) ) {
                return;
            }

            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'media-category-feedback-nonce' ) ) {
                return;
            }
            
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
            
            if ( isset( $_GET[ $this->nobug_option ] ) && 'true' === $_GET[ $this->nobug_option ] ) {
                add_site_option( $this->nobug_option, true );
            }
        }
    }
endif;

/**
 * Initialize the feedback notice.
 */
new WP_Media_Category_Feedback(
    array(
        'slug'       => 'wp_media_category_feedback',
        'name'       => __( 'WordPress Media Category', 'media-category' ),
        'time_limit' => WEEK_IN_SECONDS * 2, // Show after two weeks
    )
);