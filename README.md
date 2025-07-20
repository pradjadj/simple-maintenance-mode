# WP Maintenance Mode Plugin

A simple WordPress plugin that enables maintenance mode on your website using a custom maintenance page.

## Features

- Easy toggle on/off maintenance mode from WordPress admin
- Uses custom maintenance.php file for the maintenance page
- Bypasses maintenance mode for logged-in administrators
- Returns proper 503 HTTP status code
- Clean and responsive maintenance page design

## Installation

1. Upload the `simple-maintenance-mode` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Maintenance** menu in WordPress admin to enable/disable maintenance mode

## Usage

1. Navigate to **Maintenance** in your WordPress admin sidebar
2. Check the "Enable maintenance mode" checkbox
3. Click "Save Changes"
4. Your website will now show the maintenance page to visitors

## Customization

To customize the maintenance page, edit the `maintenance-page.php` file in the plugin directory.

## Technical Details

- Returns HTTP 503 status code for SEO purposes
- Excludes admin area and login pages from maintenance mode
- Bypasses maintenance mode for users with `manage_options` capability
- Sets proper retry-after headers