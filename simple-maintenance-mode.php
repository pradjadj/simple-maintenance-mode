<?php
/**
 * Plugin Name: Simple Maintenance Mode
 * Plugin URI: https://sgnet.co.id
 * Description: Simple maintenance mode plugin that uses custom maintenance-page.php file
 * Version: 1.0
 * Author: Pradja DJ
 * Author URI: https://sgnet.co.id
 * License: GPL2
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Maintenance_Mode {
    
    private $option_name = 'wp_maintenance_mode_status';
    
    public function __construct() {
        // Add hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_loaded', array($this, 'maintenance_mode'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Add plugin action links
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Maintenance Mode',
            'Maintenance',
            'manage_options',
            'wp-maintenance-mode',
            array($this, 'admin_page'),
            'dashicons-hammer',
            80
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wp_maintenance_mode_options', $this->option_name);
        register_setting('wp_maintenance_mode_options', 'wp_maintenance_mode_page_content');
        
        // Handle file save
        if (isset($_POST['save_maintenance_page'])) {
            $this->save_maintenance_page();
        }
    }
    
    /**
     * Maintenance mode - force show maintenance page on all front-end pages
     */
    public function maintenance_mode() {
        // Don't apply to admin area or login page
        if (is_admin() || strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false || strpos($_SERVER['REQUEST_URI'], '/wp-login.php') !== false) {
            return;
        }
        
        // Check if maintenance mode is enabled
        if (get_option($this->option_name, 'off') !== 'on') {
            return;
        }
        
        // Don't apply to logged in users with capability to manage options
        if (is_user_logged_in() && current_user_can('manage_options')) {
            return;
        }
        
        // Don't apply to AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
        
        // Don't apply to REST API requests
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return;
        }
        
        // Serve maintenance page
        $maintenance_file = plugin_dir_path(__FILE__) . 'maintenance-page.php';
        
        if (file_exists($maintenance_file)) {
            status_header(503);
            header('Retry-After: 3600');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            
            // Include the maintenance page
            include($maintenance_file);
            exit();
        }
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_wp-maintenance-mode') {
            return;
        }
        
        wp_enqueue_style('wp-maintenance-mode-admin', plugins_url('css/admin.css', __FILE__));
    }
    
    /**
     * Add plugin action links
     */
    public function plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wp-maintenance-mode') . '">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Get maintenance page content
     */
    private function get_maintenance_page_content() {
        $file_path = plugin_dir_path(__FILE__) . 'maintenance-page.php';
        if (file_exists($file_path)) {
            return file_get_contents($file_path);
        }
        return $this->get_default_maintenance_content();
    }
    
    /**
     * Get default maintenance page content
     */
    private function get_default_maintenance_content() {
        return '<?php
	header(\'HTTP/1.1 503 Service Temporarily Unavailable\');
	header(\'Status: 503 Service Temporarily Unavailable\');
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title>MAINTENANCE</title>
<link href="/favicon.ico" rel="shortcut icon" />
<style>
  body {text-align: center; padding: 150px; background: #F7F7F7;
		 font: 20px Helvetica, sans-serif; color: #333;}
  h1 {font-size: 50px;}
  article {display: block; text-align: left; width: 650px; margin: 0 auto;}
  a {color: #dc8100; text-decoration: none;}
  a:hover {color: #333; text-decoration: none;}
</style>
</head>
<body>
<article>
    <div style="text-align: center;">
    <img src="https://www.svgrepo.com/show/426192/cogs-settings.svg" width="150px"/>
    <h1>We\'ll be back soon!</h1>
    </div>
    <div>
        <p>Hi, <a href="/" style="font-weight:bold;">My Blog</a> is going to maintenance. Sorry for the inconvenience. Please come back later \'kay.</p>
        <p>Regards,<br />
		&mdash; Administrator</p>
    </div>
</article>
</body>
</html>';
    }
    
    /**
     * Save maintenance page content
     */
    public function save_maintenance_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }
        
        check_admin_referer('save_maintenance_page_nonce');
        
        $content = stripslashes($_POST['maintenance_page_content']);
        $file_path = plugin_dir_path(__FILE__) . 'maintenance-page.php';
        
        if (file_put_contents($file_path, $content) !== false) {
            echo '<div class="updated notice"><p>Maintenance page updated successfully!</p></div>';
        } else {
            echo '<div class="error notice"><p>Failed to update maintenance page. Please check file permissions.</p></div>';
        }
    }
    
    /**
     * Admin page
     */
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $status = get_option($this->option_name, 'off');
        $maintenance_content = $this->get_maintenance_page_content();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields('wp_maintenance_mode_options'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Maintenance Mode</th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo $this->option_name; ?>" value="on" <?php checked($status, 'on'); ?> />
                                Enable maintenance mode
                            </label>
                            <p class="description">When enabled, all visitors will see the maintenance page instead of your website.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2>Edit Maintenance Page</h2>
            <p>You can edit the maintenance page content directly below. This will update the <code>maintenance-page.php</code> file.</p>
            
            <form method="post" action="">
                <?php wp_nonce_field('save_maintenance_page_nonce'); ?>
                <input type="hidden" name="save_maintenance_page" value="1">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Page Content</th>
                        <td>
                            <textarea name="maintenance_page_content" rows="20" cols="100" style="font-family: monospace; width: 100%; max-width: 800px;"><?php echo esc_textarea($maintenance_content); ?></textarea>
                            <p class="description">Edit the HTML/PHP content of your maintenance page. This will be saved to maintenance-page.php</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Save Maintenance Page'); ?>
            </form>
            
            <div class="notice notice-info">
                <p><strong>Current Status:</strong> <?php echo $status === 'on' ? 'Maintenance mode is ACTIVE' : 'Maintenance mode is OFF'; ?></p>
                <p><strong>Maintenance Page:</strong> Uses <code>maintenance-page.php</code> file from plugin directory</p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Activation hook
     */
    public static function activate() {
        add_option('wp_maintenance_mode_status', 'off');
    }
    
    /**
     * Deactivation hook
     */
    public static function deactivate() {
        // Clean up plugin options
        delete_option('wp_maintenance_mode_status');
        
        // Clear any transients
        delete_transient('wpmm_maintenance_page_cache');
        delete_transient('wpmm_settings_cache');
        
        // Clear cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Clear cron jobs
        wp_clear_scheduled_hook('wpmm_daily_cleanup');
        wp_clear_scheduled_hook('wpmm_cache_cleanup');
    }
}

// Initialize plugin
$wp_maintenance_mode = new WP_Maintenance_Mode();

// Register activation/deactivation hooks
register_activation_hook(__FILE__, array('WP_Maintenance_Mode', 'activate'));
register_deactivation_hook(__FILE__, array('WP_Maintenance_Mode', 'deactivate'));
