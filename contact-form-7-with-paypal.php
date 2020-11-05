<?php
/**
 * Plugin Name: Addon Paypal With Contact Form 7
 * Description: This plugin enables payment with paypal and stripe on contact form 7.
 * Version: 1.0
 */

if (!defined('ABSPATH')) {
    die('-1');
}
if (!defined('CF7WPAY_PLUGIN_NAME')) {
    define('CF7WPAY_PLUGIN_NAME', 'Addon Paypal With Contact Form 7');
}
if (!defined('CF7WPAY_VERSION')) {
    define('CF7WPAY_VERSION', '1.0.0');
}
if (!defined('CF7WPAY_PATH')) {
    define('CF7WPAY_PATH', __FILE__);
}
if (!defined('CF7WPAY_PLUGIN_DIR')) {
    define('CF7WPAY_PLUGIN_DIR',plugins_url('', __FILE__));
}
if (!defined('CF7WPAY_DOMAIN')) {
    define('CF7WPAY_DOMAIN', 'CF7WPAY');
}
if (!defined('CF7WPAYPREFIX')) {
    define('CF7WPAYPREFIX', "cf7wpay_");
}
if (!defined('CF7WPAY_PAGE_SLUG')) {
    define('CF7WPAY_PAGE_SLUG', "cfywpay_paypal_entries");
}
if (!defined('CF7WPAY_PAYPAL_VERSION')) {
    define('CF7WPAY_PAYPAL_VERSION', '1.1.8');
}


if (!class_exists('CF7WPAY')) {

    class CF7WPAY {

        protected static $instance;
        function includes() {
            include_once('admin/cfywpay-backend.php');
            include_once('admin/cfywpay-export-csv.php');
            include_once('admin/cfywpay-cf7-panel.php');
            include_once('admin/cfywpay-payment.php');

            if (!class_exists('Stripe\Stripe')) {
                include_once('includes/stripe_library/init.php');
            }
        }


        function init() {
            add_action( 'admin_enqueue_scripts', array($this, 'CF7WPAY_load_admin_script_style'));
            add_action('wp_enqueue_scripts',  array($this, 'CF7WPAY_load_front_script_style'));
            add_action( 'admin_init', array($this, 'CF7WPAY_load_plugin'), 11 );

            session_start();
            global $wpdb;
            $table_name = $wpdb->prefix.'cf7wpay_forms';
            if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {

                $charset_collate = $wpdb->get_charset_collate();

                $sql = "CREATE TABLE $table_name (
                    form_id bigint(20) NOT NULL AUTO_INCREMENT,
                    form_post_id bigint(20) NOT NULL,
                    form_value longtext NOT NULL,
                    form_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    PRIMARY KEY  (form_id)
                ) $charset_collate;";

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $sql );
            }

            $upload_dir      = wp_upload_dir();
            $cf7wpay_dirname = $upload_dir['basedir'].'/cf7wpay_uploads';
            if ( ! file_exists( $cf7wpay_dirname ) ) {
                wp_mkdir_p( $cf7wpay_dirname );
            }
        }

        function CF7WPAY_load_front_script_style() {
            wp_enqueue_style( 'CF7WPAY-front-style', CF7WPAY_PLUGIN_DIR . '/includes/css/front_style.css', false, '1.0.0' );
            wp_enqueue_script('cf7pp-stripe-checkout','https://js.stripe.com/v3/');
        }

        function CF7WPAY_load_admin_script_style() {
            wp_enqueue_style( 'CF7WPAY-back-style', CF7WPAY_PLUGIN_DIR . '/includes/css/back_style.css', false, '1.0.0' );
            wp_enqueue_script( 'CF7WPAY-back-script', CF7WPAY_PLUGIN_DIR . '/includes/js/back_script.js', false, '1.0.0' );
        }

        function CF7WPAY_load_plugin() {
          if ( ! ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) ) {
            add_action( 'admin_notices', array($this,'OCCF7CAL_install_error') );
          }
        }

      
        public static function instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
                self::$instance->includes();
            }
            return self::$instance;
        }
    }
    add_action('plugins_loaded', array('CF7WPAY', 'instance'));
}