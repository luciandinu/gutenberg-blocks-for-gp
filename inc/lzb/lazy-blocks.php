<?php
/**
 * Plugin Name:  Lazy Blocks
 * Description:  Gutenberg blocks visual constructor. Custom meta fields or blocks with output without hard coding.
 * Version:      2.0.1
 * Author:       nK
 * Author URI:   https://nkdev.info/
 * License:      GPLv2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  lazy-blocks
 *
 * @package lazyblocks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'LazyBlocks' ) ) :

    /**
     * LazyBlocks Class
     */
    class LazyBlocks {
        /**
         * The single class instance.
         *
         * @var null
         */
        private static $instance = null;

        /**
         * Main Instance
         * Ensures only one instance of this class exists in memory at any one time.
         */
        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }

        /**
         * The base path to the plugin in the file system.
         *
         * @var string
         */
        public $plugin_path;

        /**
         * URL Link to plugin
         *
         * @var string
         */
        public $plugin_url;

        /**
         * Blocks class object.
         *
         * @var object
         */
        private $blocks;

        /**
         * Templates class object.
         *
         * @var object
         */
        private $templates;

        /**
         * LazyBlocks constructor.
         */
        public function __construct() {
            /* We do nothing here! */
        }

        /**
         * Activation Hook
         */
        public function activation_hook() {
            LazyBlocks_Dummy::add();
        }

        /**
         * Deactivation Hook
         */
        public function deactivation_hook() {}

        /**
         * Init.
         */
        public function init() {
            $this->plugin_path = plugin_dir_path( __FILE__ );
            $this->plugin_url  = plugin_dir_url( __FILE__ );

            $this->load_text_domain();
            $this->add_actions();
            $this->include_dependencies();

            $this->icons     = new LazyBlocks_Icons();
            $this->controls  = new LazyBlocks_Controls();
            $this->blocks    = new LazyBlocks_Blocks();
            $this->templates = new LazyBlocks_Templates();
            $this->tools     = new LazyBlocks_Tools();

            add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        }

        /**
         * Get plugin_path.
         */
        public function plugin_path() {
            return apply_filters( 'lzb/plugin_path', $this->plugin_path );
        }

        /**
         * Get plugin_url.
         */
        public function plugin_url() {
            return apply_filters( 'lzb/plugin_url', $this->plugin_url );
        }

        /**
         * Sets the text domain with the plugin translated into other languages.
         */
        public function load_text_domain() {
            load_plugin_textdomain( 'lazy-blocks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        }

        /**
         * Actions.
         */
        public function add_actions() {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        }

        /**
         * Set plugin Dependencies.
         */
        private function include_dependencies() {
            require_once $this->plugin_path() . '/classes/class-icons.php';
            require_once $this->plugin_path() . '/classes/class-controls.php';
            require_once $this->plugin_path() . '/classes/class-blocks.php';
            require_once $this->plugin_path() . '/classes/class-templates.php';
            require_once $this->plugin_path() . '/classes/class-tools.php';
            require_once $this->plugin_path() . '/classes/class-rest.php';
            require_once $this->plugin_path() . '/classes/class-dummy.php';
        }

        /**
         * Get lazyblocks icons object.
         */
        public function icons() {
            return $this->icons;
        }

        /**
         * Get lazyblocks controls object.
         */
        public function controls() {
            return $this->controls;
        }

        /**
         * Get lazyblocks blocks object.
         */
        public function blocks() {
            return $this->blocks;
        }

        /**
         * Get lazyblocks templates object.
         */
        public function templates() {
            return $this->templates;
        }

        /**
         * Get lazyblocks tools object.
         */
        public function tools() {
            return $this->tools;
        }

        /**
         * Add lazyblocks block.
         *
         * @param array $data - block data.
         */
        public function add_block( $data ) {
            return $this->blocks()->add_block( $data );
        }

        /**
         * Add lazyblocks template.
         *
         * @param array $data - template data.
         */
        public function add_template( $data ) {
            return $this->templates()->add_template( $data );
        }

        /**
         * Admin menu.
         */
        public function admin_menu() {
            // Documentation menu link.
            add_submenu_page(
                'edit.php?post_type=lazyblocks',
                esc_html__( 'Documentation', 'lazy-blocks' ),
                esc_html__( 'Documentation', 'lazy-blocks' ),
                'manage_options',
                'https://lazyblocks.com/documentation/getting-started/'
            );
        }

        /**
         * Enqueue admin styles and scripts.
         */
        public function admin_enqueue_scripts() {
            global $post;
            global $post_type;
            global $wp_locale;

            if ( 'lazyblocks' === $post_type ) {
                wp_enqueue_script(
                    'lazyblocks-constructor',
                    $this->plugin_url() . 'assets/admin/constructor/index.min.js',
                    array( 'wp-blocks', 'wp-editor', 'wp-block-editor', 'wp-i18n', 'wp-element', 'wp-components', 'lodash', 'jquery' ),
                    '2.0.1',
                    true
                );
                wp_localize_script(
                    'lazyblocks-constructor',
                    'lazyblocksConstructorData',
                    array(
                        'post_id'             => isset( $post->ID ) ? $post->ID : 0,
                        'allowed_mime_types'  => get_allowed_mime_types(),
                        'controls'            => $this->controls()->get_controls(),
                        'controls_categories' => $this->controls()->get_controls_categories(),
                        'icons'               => $this->icons()->get_all(),
                    )
                );

                wp_enqueue_style( 'lazyblocks-constructor', $this->plugin_url() . 'assets/admin/constructor/style.min.css', array(), '2.0.1' );
            }

            wp_enqueue_script( 'date_i18n', $this->plugin_url() . 'vendor/date_i18n/date_i18n.js', array(), '1.0.0', true );

            $month_names       = array_map( array( &$wp_locale, 'get_month' ), range( 1, 12 ) );
            $month_names_short = array_map( array( &$wp_locale, 'get_month_abbrev' ), $month_names );
            $day_names         = array_map( array( &$wp_locale, 'get_weekday' ), range( 0, 6 ) );
            $day_names_short   = array_map( array( &$wp_locale, 'get_weekday_abbrev' ), $day_names );

            wp_localize_script(
                'date_i18n',
                'DATE_I18N',
                array(
                    'month_names'       => $month_names,
                    'month_names_short' => $month_names_short,
                    'day_names'         => $day_names,
                    'day_names_short'   => $day_names_short,
                )
            );

            wp_enqueue_style( 'lazyblocks-admin', $this->plugin_url() . 'assets/admin/css/style.min.css', '', '2.0.1' );
        }
    }

    /**
     * The main cycle of the plugin.
     *
     * @return null|LazyBlocks
     */
    function lazyblocks() {
        return LazyBlocks::instance();
    }

    // Initialize.
    lazyblocks();

    // Activation / Deactivation hooks.
    register_deactivation_hook( __FILE__, array( lazyblocks(), 'activation_hook' ) );
    register_activation_hook( __FILE__, array( lazyblocks(), 'deactivation_hook' ) );

    /**
     * Function to get meta value with some improvements for Lazyblocks metas.
     *
     * @param string   $name - metabox name.
     * @param int|null $id - post id.
     *
     * @return array|mixed|object
     */
    // phpcs:ignore
    function get_lzb_meta( $name, $id = null ) {
        $control_data = null;

        if ( null === $id ) {
            global $post;
            $id = $post->ID;
        }

        // Find control data by meta name.
        $blocks = lazyblocks()->blocks()->get_blocks();
        foreach ( $blocks as $block ) {
            if ( isset( $block['controls'] ) && is_array( $block['controls'] ) ) {
                foreach ( $block['controls'] as $control ) {
                    if ( $control_data || 'true' !== $control['save_in_meta'] ) {
                        continue;
                    }

                    $meta_name = false;

                    if ( isset( $control['save_in_meta_name'] ) && $control['save_in_meta_name'] ) {
                        $meta_name = $control['save_in_meta_name'];
                    } elseif ( $control['name'] ) {
                        $meta_name = $control['name'];
                    }

                    if ( $meta_name && $meta_name === $name ) {
                        $control_data = $control;
                    }
                }
            }
        }

        $result = get_post_meta( $id, $name, true );

        // set default.
        if ( ! $result && isset( $control_data['default'] ) && $control_data['default'] ) {
            $result = $control_data['default'];
        }

        return apply_filters( 'lzb/get_meta', $result, $name, $id, $control_data );
    }

endif;
