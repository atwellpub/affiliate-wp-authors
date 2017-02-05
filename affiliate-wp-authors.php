<?php
/**
 * Plugin Name: AffiliateWP - Authors as Affiliates
 * Plugin URI: http://www.inboundnow.com
 * Description: Automatically sets content author's affiliate cookie for brand new traffic arriving on author articles.
 * Author: Hudson Atwell
 * Author URI: http://www.twitter.com/atwellpub
 * Version: 1.0.1
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AFFWP_Authors {

    /**
     *  Initialize class
     */
    public function __construct() {
        self::define_constants();
        self::load_hooks();
    }

    /**
     *  Define constants
     */
    public static function define_constants() {
        define('AFFWP_AUTHORS_CURRENT_VERSION', '1.0.1');
        define('AFFWP_AUTHORS_FILE', __FILE__);
        define('AFFWP_AUTHORS_PATH', realpath(dirname(__FILE__)) . '/');
        define('AFFWP_AUTHORS_URLPATH', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/');
    }

    /**
     *  Load hooks and filters
     */
    public static function load_hooks() {

        /* Cookie Author */
        add_action('wp_footer', array(__CLASS__, 'cookie_author'));

    }

    public static function cookie_author() {

        $author_id = get_the_author_meta('ID');

        /* if author not set then bail */
        if (!$author_id) {
            return;
        }

        /* get affiliate id from author id */
        $affiliate_id = affwp_get_affiliate_id($author_id);

        /* do not cookie internal traffic */
        if (preg_match('/inboundnow.com/', wp_get_referer())) {
            return;
        }

        /* do not cookie rules */
        if (
            /* do not cookie for authors not registered as affiliates */
            !$affiliate_id
            ||
            /* do not cookie the cookied */
            isset($_COOKIE['affwp_ref'])
            ||
            /* do not cookie if we detect visitor is already a lead */
            isset($_COOKIE['wp_lead_id'])
            ||
            /* do not cookie if homepage */
            is_front_page()
        ) {
            return;
        }


        /**/
        $result = wp_remote_post(admin_url('admin-ajax.php'), array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(),
            'body' => array(
                'action' => 'affwp_track_visit',
                'affiliate' => $affiliate_id,
                'campaign' => 'guest-publishing',
                'url' => "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]",
                'referrer' => wp_get_referer()
            )
        ));

        /* set cookies */
        setcookie('affwp_ref', $affiliate_id , time() + (60*60*24*30 ), '/');
        setcookie('affwp_ref_visit_id', $result['body'] , time() + (60*60*24*30 ), '/');


    }
}

new AFFWP_Authors;