<?php
/**
 * Uninstall script for Simple Maintenance Mode plugin
 * 
 * This file is automatically called when the plugin is deleted via WordPress admin
 * It removes all plugin data, settings, cache, and files
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Load WordPress functions
global $wpdb;

/**
 * Remove all plugin options from database
 */
function wpmm_delete_plugin_options() {
    // Delete main plugin option
    delete_option('wp_maintenance_mode_status');
    
    // Delete any additional options that might be added in future versions
    $options_to_delete = array(
        'wp_maintenance_mode_custom_message',
        'wp_maintenance_mode_custom_css',
        'wp_maintenance_mode_background_color',
        'wp_maintenance_mode_text_color',
        'wp_maintenance_mode_logo_url',
        'wp_maintenance_mode_social_links',
        'wp_maintenance_mode_countdown',
        'wp_maintenance_mode_seo_title',
        'wp_maintenance_mode_seo_description',
        'wp_maintenance_mode_analytics_code',
        'wp_maintenance_mode_custom_html',
        'wp_maintenance_mode_email_collection',
        'wp_maintenance_mode_exclude_urls',
        'wp_maintenance_mode_user_roles',
        'wp_maintenance_mode_ip_whitelist',
        'wp_maintenance_mode_schedule',
        'wp_maintenance_mode_cache_version'
    );
    
    foreach ($options_to_delete as $option) {
        delete_option($option);
        delete_site_option($option); // For multisite
    }
}

/**
 * Remove plugin transients and cache
 */
function wpmm_clear_plugin_cache() {
    // Delete transients
    $transients = array(
        'wpmm_maintenance_page_cache',
        'wpmm_settings_cache',
        'wpmm_visitor_count',
        'wpmm_analytics_data'
    );
    
    foreach ($transients as $transient) {
        delete_transient($transient);
        delete_site_transient($transient); // For multisite
    }
    
    // Clear object cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
    }
    
    // Clear any plugin-specific cache files
    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/wp-maintenance-mode-cache';
    
    if (is_dir($cache_dir)) {
        wpmm_recursive_delete($cache_dir);
    }
}

/**
 * Remove plugin files and directories
 */
function wpmm_remove_plugin_files() {
    $plugin_dir = plugin_dir_path(__FILE__);
    
    // Files to remove
    $files_to_remove = array(
        'maintenance-page.php',
        'css/admin.css',
        'js/admin.js',
        'images/logo.png',
        'cache/.htaccess',
        'logs/maintenance.log'
    );
    
    foreach ($files_to_remove as $file) {
        $file_path = $plugin_dir . $file;
        if (file_exists($file_path)) {
            @unlink($file_path);
        }
    }
    
    // Remove directories
    $dirs_to_remove = array(
        'css',
        'js',
        'images',
        'cache',
        'logs',
        'templates',
        'includes'
    );
    
    foreach ($dirs_to_remove as $dir) {
        $dir_path = $plugin_dir . $dir;
        if (is_dir($dir_path)) {
            wpmm_recursive_delete($dir_path);
        }
    }
}

/**
 * Helper function to recursively delete directories
 */
function wpmm_recursive_delete($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), array('.', '..'));
    
    foreach ($files as $file) {
        $file_path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($file_path)) {
            wpmm_recursive_delete($file_path);
        } else {
            @unlink($file_path);
        }
    }
    
    return @rmdir($dir);
}

/**
 * Remove plugin user meta data
 */
function wpmm_delete_user_meta() {
    global $wpdb;
    
    // Delete user meta related to plugin
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            '%wp_maintenance_mode%'
        )
    );
}

/**
 * Remove plugin cron jobs
 */
function wpmm_clear_cron_jobs() {
    // Clear any scheduled cron events
    $cron_hooks = array(
        'wpmm_daily_cleanup',
        'wpmm_cache_cleanup',
        'wpmm_analytics_sync',
        'wpmm_email_digest'
    );
    
    foreach ($cron_hooks as $hook) {
        wp_clear_scheduled_hook($hook);
    }
}

/**
 * Main uninstall function
 */
function wpmm_uninstall_plugin() {
    // Check if user has permission to delete plugins
    if (!current_user_can('delete_plugins')) {
        return;
    }
    
    // Perform cleanup in order
    wpmm_delete_plugin_options();
    wpmm_clear_plugin_cache();
    wpmm_remove_plugin_files();
    wpmm_delete_user_meta();
    wpmm_clear_cron_jobs();
    
    // Log uninstall for debugging (optional)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Simple Maintenance Mode plugin uninstalled successfully');
    }
}

// Run uninstall process
wpmm_uninstall_plugin();
