<?php
/**
 * GF Quiz Add-On
 *
 * @package     GFQuiz
 *
 * @wordpress-plugin
 * Plugin Name: GF Quiz Shuffler
 * Plugin URI: http://www.gravityforms.com
 * Description: Generate new randomized forms from Question Bank form with command <code>wp quiz generate</code>
 * Version: 1.0.1
 * Requires at least: 4.0
 * Requires PHP: 5.6
 * Author: rtCamp, thelovekesh
 * Author URI: http://www.rtcamp.com
 * Text Domain: gfquiz
 * Domain Path: /languages
 * Update URI: http://www.rtcamp.com
 *
 * Copyright 2009-2021 rtCamp Inc.
 */

namespace GF_Quiz_Shuffler;

// Do not access plugin file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
define( 'GF_RT_QUIZ_PLUGIN_VERSION', get_plugin_data( __FILE__ )['Version'] );
define( 'GF_RT_QUIZ_PLUGIN_PATH', __DIR__ );
define( 'GF_RT_QUIZ_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

add_action(
	'cli_init',
	function(){
		\WP_CLI::add_command( 'quiz generate', function( $args, $assoc_args ){
			$forms = \GFAPI::get_forms();

			foreach( $forms as $form ) {
				if ( 'Question Bank' === $form['title'] ) {
					$qb_form = $form;
				}
			}

			$qb_form['title'] = 'Quiz Variation ' . rand();
			$qb_form['description'] = 'Quiz Variation';

			$new_form = generate_form_from_question_bank_form( $qb_form );

			$new_form_id = \GFAPI::add_form( $new_form );

			\WP_CLI::log(
				sprintf(
					__( 'New form created: %s', 'gfquiz' ),
					admin_url( 'admin.php?page=gf_edit_forms&id=' . (int) $new_form_id )
				)
			);
		} );
	}
);

function generate_form_from_question_bank_form( $qb_form ) {
	$form = $qb_form;

	$question_bank_fields = \GFAPI::get_fields_by_type( $qb_form, array( 'quiz' ) );

	if ( empty( $question_bank_fields ) ) {
		return $form;
	}
	
	// store forms fields based on questionCategory
	$css_question_category = array();
	$html_question_category = array();
	$js_question_category = array();
	$php_question_category = array();

	// loop through all quiz fields and store them in the correct array
	foreach ( $question_bank_fields as $field ) {
		$question_category = $field['questionCategory'];
		switch ( $question_category ) {
			case 'css':
				$css_question_category[] = $field;
				break;
			case 'html':
				$html_question_category[] = $field;
				break;
			case 'js':
				$js_question_category[] = $field;
				break;
			case 'php':
				$php_question_category[] = $field;
				break;
		}
	}

	// shuffle the arrays
	shuffle( $css_question_category );
	shuffle( $html_question_category );
	shuffle( $js_question_category );
	shuffle( $php_question_category );

	// keep only first 10 fields
	$css_question_category = array_slice( $css_question_category, 0, 1 );
	$html_question_category = array_slice( $html_question_category, 0, 1 );
	$js_question_category = array_slice( $js_question_category, 0, 1 );
	$php_question_category = array_slice( $php_question_category, 0, 1 );

	// replace form fields with the new ones
	$form_fields = array_merge( $css_question_category, $html_question_category, $js_question_category, $php_question_category );

	$form['fields'] = array();
	$page_number = 1;
	$page_id = 1111; // take a large number for unique id

	$page_break = array( // create a custom page break field
		'type' => 'page',
		'id' => $page_id,
		'displayOnly' => true,
		'formId' => $form['id'],
		'pageNumber' => 2,
		'nextButton' => array(
			'type' => 'text',
			'text' => esc_html__( 'Next', 'gfquiz' ),
		),
		'previousButton' => array(
			'type' => 'text',
			'text' => esc_html__( 'Previous', 'gfquiz' ),
		)
	);

	foreach ( $form_fields as $field ) {
		$field['pageNumber'] = $page_number;
		$form['fields'][] = $field;
		
		if ( $page_number > 1 ) {
			$page_break['id'] = $page_id + 1;
			$page_break['pageNumber'] = $page_number + 1;
		}

		$form['fields'][] = \GF_Fields::create( $page_break );

		$page_id++;
		$page_number++;
	}

	// remove last page break
	array_pop( $form['fields'] );

	unset( $form['id'] );

	return $form;
}
