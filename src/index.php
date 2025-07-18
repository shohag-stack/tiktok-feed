<?php 
require_once plugin_dir_path(__FILE__) . '/../classes/Tiktok_feed_api.php';

$token = get_option('tiktok_access_token');
$username = get_option('tiktok_user_name');
$avatar = get_option('tiktok_user_avatar');

var_dump($username, $avatar, $token);


if (isset($_GET['disconnected'])) {
    echo '<div class="notice notice-success"><p>✅ TikTok account disconnected successfully.</p></div>';
}
?>

<div class="wrap">
    <h1><?php esc_html_e("TikTok Feed Settings", "tiktok-feed"); ?></h1>
    <p><?php esc_html_e("Configure your TikTok feed settings here.", "tiktok-feed"); ?></p>

    <?php if (!empty($token)): ?>
        <p style="color: green;">✅ Connected to TikTok</p>
        <?php if (!empty($username)): ?>
            <div style="display:flex;align-items:center;gap:10px;">
                <img src="<?php echo esc_url($avatar); ?>" width="50" height="50" style="border-radius:50%;" />
                <strong><?php echo esc_html($username); ?></strong>
            </div>
        <?php endif; ?>
        <form method="post">
            <?php wp_nonce_field('tiktok_disconnect', 'tiktok_disconnect_nonce'); ?>
            <input type="submit" name="disconnect" class="button button-secondary" value="Disconnect TikTok">
        </form>
    <?php else: ?>
        <a href="<?php echo esc_url(Tiktok_feed_api::get_tiktok_athorization_code()) ?>" class="button button-primary">
            <?php esc_html_e("Connect TikTok", "tiktok-feed"); ?>
            
        </a>
    <?php endif; ?>
</div>