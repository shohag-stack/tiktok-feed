<?php
/*
    * Plugin Name: TikTok Feed
    * Plugin URI: https://example.com/tiktok-feed
    * Description: A plugin to display TikTok feeds on your WordPress site.
    * Version: 1.0.0
    * author: Shohag
    * Author URI: https://example.com
    * License: GPL2
    * License URI: https://www.gnu.org/licenses/gpl-2.0.html
    * Text Domain: tiktok-feed
    * Domain Path: /languages
    * Requires at least: 5.0
    * Requires PHP: 7.0
    * Tested up to: 6.0
 */

require_once plugin_dir_path(__FILE__) . 'classes/Tiktok_feed_menu_page.php';
require_once plugin_dir_path(__FILE__) . 'classes/Tiktok_feed_api.php';


if (!defined("ABSPATH")) {
    exit; // Exit if accessed directly
}

function tiktok_feed_menu_page() {
    $tiktok_menu_page = new Tiktok_feed_menu_page();
    $tiktok_menu_page->add_tiktok_menu_page();
}


add_action('init', function() {
    add_rewrite_rule('^tiktok-callback/?$', 'index.php?tiktok_callback=1', 'top');
});

add_filter('query_vars', function($vars) {
    $vars[] = 'tiktok_callback';
    return $vars;
});

add_action('template_redirect', function() {
    if (get_query_var('tiktok_callback')) {
        // Handle TikTok OAuth response
        if (isset($_GET['code'])) {
            Tiktok_feed_api::get_access_token(sanitize_text_field($_GET['code']));
        }
        wp_redirect(admin_url('admin.php?page=tiktok-feed'));
        exit;
    }
});

function tiktok_feed_shortcode() {
    if(!class_exists("Tiktok_feed_api")) {
        require_once plugin_dir_path(__FILE__) . 'classes/Tiktok_feed_api.php';
    }
    return Tiktok_feed_api::render_video();
}


add_action('admin_init', function() {
    if (isset($_POST['disconnect']) && check_admin_referer('tiktok_disconnect', 'tiktok_disconnect_nonce')) {
        delete_option('tiktok_access_token');
        wp_redirect(admin_url('admin.php?page=tiktok-feed&disconnected=1'));
        exit;
    }
});

add_shortcode("tiktok_feed", "tiktok_feed_shortcode");

add_action("admin_menu", "tiktok_feed_menu_page");

?>