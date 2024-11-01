<?php
/**
*  Plugin Name:       ST Demo Importer
*  Plugin URI:        https://spectrathemes.com/plugins/
*  Description:       ST Demo Importer is a WordPress plugin for Elementor, enabling fast import of pre-designed themes and templates, saving time in website creation.
*  Version:           0.1.1
*  Requires at least: 5.2
*  Requires PHP:      7.4
*  Author:            spectrathemes
*  Author URI:        https://www.spectrathemes.com/
*  License:           GPL v2 or later
*  License URI:       https://www.gnu.org/licenses/gpl-2.0.html
*  Text Domain:       st-demo-importer 
**/

if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly
}

add_action('init', 'st_demo_importer_check_activation_redirect');

function st_demo_importer_check_activation_redirect() {
  if (is_admin() && get_option('st_demo_importer_plugin_activated', false)) {
    delete_option('st_demo_importer_plugin_activated');
    wp_safe_redirect(admin_url('admin.php?page=stdemoimporter-wizard'));
    exit;
  }
}

register_activation_hook(__FILE__, 'st_demo_importer_activate');

function st_demo_importer_activate() {
    add_option('st_demo_importer_plugin_activated', true);
}

// License verification constant
define( 'STDI_SECRET_KEY', '65e43d97f3eca3.41814020' );
define( 'STDI_FILE', __FILE__ );
define( 'STDI_BASE', plugin_basename( STDI_FILE ) );
define( 'STDI_DIR', plugin_dir_path( STDI_FILE ) );
define( 'STDI_URL', plugins_url( '/', STDI_FILE ) );
define( 'STDI_ADMIN_CUSTOM_ENDPOINT', 'https://spectrathemes.com/wp-json/spectra-license-admin/v2/' );
define( 'STDI_THEMES_HOME_URL', "https://spectrathemes.com" );

if( ! function_exists('get_plugin_data') ) {
  require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

$plugin_data = get_plugin_data( __FILE__ );
define( 'STDI_VER', $plugin_data['Version'] );

require STDI_DIR .'theme-wizard/config.php';
require STDI_DIR .'widgets/dashboard-widgets.php';