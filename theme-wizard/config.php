<?php
/**
 * Settings for theme wizard
 *
 * @package Whizzie
 * @author Catapult Themes
 * @since 1.0.0
 */

/**
 * Define constants
 **/
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! defined( 'st_demo_importer_WHIZZIE_DIR' ) ) {
	define( 'st_demo_importer_WHIZZIE_DIR', dirname( __FILE__ ) );
}

// Classes for separate codes
require_once STDI_DIR . 'classes/welcome.php';
require_once STDI_DIR . 'classes/script_enqueuer.php';
require_once STDI_DIR . 'classes/setup_plugins.php';
require_once STDI_DIR . 'classes/elementor_import.php';
require_once STDI_DIR . 'classes/activation.php';
require_once STDI_DIR . 'classes/steps.php';
require_once STDI_DIR . 'classes/premium_templates.php';
require_once STDI_DIR . 'classes/free_templates.php';

// Load the Whizzie class and other dependencies
require trailingslashit( st_demo_importer_WHIZZIE_DIR ) . 'st_demo_importer_whizzie.php';

/**
 * This kicks off the wizard
 **/
if( class_exists( 'st_demo_importer_ThemeWhizzie' ) ) {
	$st_demo_importer_ThemeWhizzie = new st_demo_importer_ThemeWhizzie();
}
