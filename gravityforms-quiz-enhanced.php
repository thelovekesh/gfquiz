<?php
/**
 * GF Quiz Add-On
 *
 * @package     GFQuiz
 *
 * @wordpress-plugin
 * Plugin Name: GF Quiz Add-On
 * Plugin URI: http://www.gravityforms.com
 * Description: Integrates Gravity Forms with Quiz
 * Version: 1.0.0
 * Requires at least: 4.0
 * Requires PHP: 5.6
 * Author: rtCamp, thelovekesh
 * Author URI: http://www.rtcamp.com
 * Text Domain: gf-quiz
 * Domain Path: /languages
 * Update URI: http://www.rtcamp.com
 *
 * Copyright 2009-2021 rtCamp Inc.
 */

// Do not access plugin file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'GF_RT_QUIZ_VERSION', '1.0.0' );
define( 'GF_RT_QUIZ_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'GF_RT_QUIZ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Define all actions and filters needed in main plugin file.
add_action( 'gform_loaded', [ 'GF_RT_Quiz_Bootstrap', 'load' ], 5 );

/**
 * Class GF_RT_Quiz_Bootstrap
 * Handles the loading of the GF Quiz Add-On
 * Registers GF Quiz Add-On with the GF Add-On framework.
 *
 * @since 1.0.0
 * @package GF_Quiz
 */
class GF_RT_Quiz_Bootstrap {

	/**
	 * If the GF Feed Add-On is installed, load load GF Quiz Add-On.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {
		// Check and include GF Feed Add-On Framework.
		if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
			return;
		}
		GFForms::include_feed_addon_framework();

		// Load GF Quiz Add-On.
		require_once GF_RT_QUIZ_PLUGIN_PATH . '/class-gravityforms-quiz-enhanced.php';

		// Register GF Quiz Add-On.
		GFAddOn::register( 'Gravityforms_Quiz_Enhanced' );
	}
}
