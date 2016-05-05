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
        add_action('wp_head', array(__CLASS__, 'cookie_author'));

    }

    public static function cookie_author() {

        $author_id = get_the_author_id();

        /* if author not set then bail */
        if (!$author_id) {
            return;
        }

        /* get affiliate id from author id */
        $affiliate_id = affwp_get_affiliate_id($author_id);

        /* do not cookie internal traffic */
        if (preg_match('/inboundnow.com/', $_SERVER['HTTP_REFERER'])) {
            return;
        }

        /* do not cookie rules */
        if (
            /* do not cookie for authors not registered as affiliates */
            !$affiliate_id
            ||
            /* do not cookie the cookied */
            isset($_COOKIE['ref_cookie'])
            ||
            /* do not cookie if we detect visitor is already a lead */
            isset($_COOKIE['wp_lead_id'])
        ) {
            return;
        }

        setcookie('ref_cookie', $affiliate_id, time() + (30 * 24 * 60 * 60), '/');
        setcookie('affwp_campaign', 'guest-publishing', time() + (30 * 24 * 60 * 60), '/');

    }
}

new AFFWP_Authors;