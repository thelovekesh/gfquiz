<?php
/**
 * GF Quiz Add-On.
 *
 * @since 1.0.0
 * @package GFQuiz
 * @author rtCamp, thelovekesh
 * @copyright Copyright (c) 2021, rtCamp
 */

// Do not access plugin file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GF Notion Feed Add-on class.
 *
 * @see https://docs.gravityforms.com/gffeedaddon/
 *
 * @since 1.0.0
 */
class Gravityforms_Quiz_Enhanced extends GFFeedAddOn {
	/**
	 * The current version of the Add-On.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $_version The current version of the Add-On.
	 */
	protected $_version = GF_RT_QUIZ_VERSION;

	/**
	 * The minimum version of Gravity Forms required.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $_min_gravityforms_version The minimum version of Gravity Forms required.
	 */
	protected $_min_gravityforms_version = '1.9.10';

	/**
	 * The plugin slug.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $_slug The plugin slug.
	 */
	protected $_slug = 'gravityforms-quiz-enhanced';

	/**
	 * The full path to the plugin's main file.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $_path The full path to the plugin's main file.
	 */
	protected $_path = 'gravityforms-quiz-enhanced/gravityforms-quiz-enhanced.php';

	/**
	 * Full path to this class file.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $_full_path The full path to this class file.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Title of the Add-On.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Quiz Add-On';

	/**
	 * Short title of the Add-On.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string $_short_title The short title of the Add-On.
	 */
	protected $_short_title = 'GF Quiz';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array $_instance Contains an instance of this class, if available.
	 */
	private static $_instance = null;

	private $question_bank_form = 4; // replace it with your question bank form id for now.

	/**
	 * Get an instance of this class.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return GF_RT_Quiz
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Registers needed plugin hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'halve_form_fields' ) );
		add_action( 'gform_pre_validation', array( $this, 'halve_form_fields' ) );
		add_action( 'gform_after_submission', array( $this, 'reset_option' ), 10, 2 );

		parent::init();
	}

	/**
	 * Initialize the admin specific hooks.
	 */
	public function init_admin() {

		// form editor
		add_action( 'gform_field_standard_settings', array( $this, 'quiz_category_field_settings' ), 10, 2 );
		add_action( 'gform_editor_js', array($this, 'quiz_category_editor_script' ) );
		add_filter( 'gform_tooltips', array( $this, 'quiz_category_field_tooltips' ) );


		parent::init_admin();
	}

	/**
	 * Add questions category dropdown to the form editor.
	 */
	public function quiz_category_field_settings( $position, $form_id ) {
		if ( $position == 25 ) {
			?>
			<li class="gquiz-setting-field-type field_setting">
				<label for="field_quiz_category">
					<?php esc_html_e( 'Question Category', 'gravityformsquiz' ); ?>
					<?php gform_tooltip( 'form_field_quiz_category' ) ?>
				</label>
				<select id="field_quiz_category" onchange="SetFieldProperty('questionCategory', jQuery(this).val());">
					<option value=""><?php esc_html_e( 'Select a category', 'gravityformsquiz' ); ?></option>
					<?php
					$categories = $this->get_quiz_questions_categories();
					foreach ( $categories as $category ) {
						?>
						<option value="<?php echo esc_attr( $category['id'] ); ?>"><?php echo esc_html( $category['name'] ); ?></option>
						<?php
					}
					?>
				</select>
			</li>
			<?php
		}
	}

	/**
	 * Question categories.
	 */
	public function get_quiz_questions_categories() {
		return array(
			array(
				'id' => 'php',
				'name' => 'PHP',
			),
			array(
				'id' => 'js',
				'name' => 'JavaScript',
			),
			array(
				'id' => 'css',
				'name' => 'CSS',
			),
			array(
				'id' => 'html',
				'name' => 'HTML',
			)
		);
	}

	/**
	 * Add script to the form editor.
	 */
	public function quiz_category_editor_script() {
		?>
		<script type='text/javascript'>
			jQuery(document).bind('gform_load_field_settings', function (event, field, form) {
				jQuery('#field_quiz_category').val(field.questionCategory);
			});
		</script>
		<?php
	}

	/**
	 * Add tooltips to the form editor.
	 */
	public function quiz_category_field_tooltips( $tooltips ) {
		$tooltips['form_field_quiz_category'] = '<h6>' . esc_html__( 'Quiz Category', 'gravityformsquiz' ) . '</h6>' . esc_html__( 'Select the category of the questions you want to display.', 'gravityformsquiz' );
		return $tooltips;
	}

	/**
	 * Get the question form question-bank form and save it to the quiz form
	 * 
	 * @param object $form The quiz form
	 * 
	 * @return $form The quiz form
	 */
	public function halve_form_fields( $form ) {

		// if form id is equals to question_bank_form then return form
		if ( $form['id'] == $this->question_bank_form ) {
			return $form;
		}

		if ( get_option( 'rt_qb_form_update', false ) ) {
			return $form;
		}

		// we have to create a single quiz field in order to get the quiz form
		$quiz_fields = GFAPI::get_fields_by_type( $form, array( 'quiz' ) );

		if ( empty( $quiz_fields ) ) {
			return $form;
		}

		$question_bank = GFAPI::get_form( $this->question_bank_form ); // get question bank form

		$question_bank_fields = GFAPI::get_fields_by_type( $question_bank, array( 'quiz' ) );

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
				'text' => esc_html__( 'Next', 'gravityformsquiz' ),
			),
			'previousButton' => array(
				'type' => 'text',
				'text' => esc_html__( 'Previous', 'gravityformsquiz' ),
			)
		);

		foreach ( $form_fields as $field ) {
			$field['pageNumber'] = $page_number;
			$form['fields'][] = $field;
			
			if ( $page_number > 1 ) {
				$page_break['id'] = $page_id + 1;
				$page_break['pageNumber'] = $page_number + 1;
			}

			$form['fields'][] = GF_Fields::create( $page_break );

			$page_id++;
			$page_number++;
		}

		// remove last page break
		array_pop( $form['fields'] );

		$this->log_debug( __METHOD__ . '(): Form fields: ' . print_r( 'updating', true ) );

		GFAPI::update_form( $form );
		update_option( 'rt_qb_form_update', true ); // update the option if quiz form is updated once

		return $form;

	}

	/**
	 * Reset the `rt_qb_form_update` option when form is submitted.
	 *
	 * @param array $entry The entry object.
	 * @param object $form The form object.
	 * @return void
	 */
	public function reset_option( $entry, $form ) {
		update_option( 'rt_qb_form_update', false );
		$this->log_debug( __METHOD__ . '(): Reset form creation ' . print_r( get_option( 'rt_qb_form_update' ), true ) );
	}

}
