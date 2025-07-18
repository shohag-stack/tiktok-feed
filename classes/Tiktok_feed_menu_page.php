<?php
class Tiktok_feed_menu_page{
    public function add_tiktok_menu_page() {
        add_menu_page(
            __("TikTok Feed", "tiktok-feed"),
            __("TikTok Feed", "tiktok-feed"),
            'manage_options',
            "tiktok-feed",
            [ $this ,  "tiktok_admin_menu_ui" ],
            "dashicons-video-alt3",
            6
        );
    }
    public function tiktok_admin_menu_ui(){
    // This function will render the admin menu UI for TikTok Feed
        $admin_ui_template =  plugin_dir_path(__FILE__) . '../src/index.php';
        if (  file_exists($admin_ui_template)   && file_exists($admin_ui_template)) {
            require $admin_ui_template;
        }
        else {
            echo "<p> admin ui template has not found  </p>";
        }
}
};
?>