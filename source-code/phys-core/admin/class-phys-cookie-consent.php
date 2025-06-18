<?php

/**
 * Class Phys_Cookie_Consent.
 *
 * @since 2.0.6
 */
class Phys_Cookie_Consent extends Phys_Admin_Sub_Page {
	/**
	 * @var string
	 *
	 * @since 2.0.6
	 */
	public $key_page = 'cookie-consent';

	/**
	 * Phys_Cookie_Consent constructor.
	 *
	 * @since 2.0.6
	 */
	protected function __construct() {
		parent::__construct();

		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since 2.0.6
	 */
	private function hooks() {
		add_filter( 'phys_dashboard_sub_pages', array( $this, 'add_sub_page' ) );
 		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'fe_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'render_banner' ) );
		
		// Register AJAX hooks
		add_action( 'wp_ajax_update_cookie_manager_form', array( $this, 'update_cookie_manager_form' ) );
		add_action( 'wp_ajax_nopriv_update_cookie_manager_form', array( $this, 'update_cookie_manager_form' ) );

		add_action( 'wp_ajax_cookie_consent_settings', array( $this, 'phys_save_cookie_consent_settings' ) );
		add_action( 'wp_ajax_nopriv_cookie_consent_settings', array( $this, 'phys_save_cookie_consent_settings' ) );

		add_action( 'wp_ajax_phys_edit_cookie_list', array( $this, 'phys_edit_cookie_list_handler' ) );
	}

	/**
	 * Add sub page.
	 *
	 * @since 2.0.6
	 *
	 *
	 * @param $sub_pages
	 *
	 * @return mixed
	 */
	public function add_sub_page( $sub_pages ) {

		if ( ! current_user_can( 'administrator' ) ) {
			return $sub_pages;
		}

		$sub_pages['cookie-consent'] = array(
			'title' => __( 'Cookie Consent', 'phys-core' ),
			'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="26" fill="none" stroke="#50575E" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M12 2a10 10 0 0 0-1 19.95 10 10 0 0 0 9-14.82A2.5 2.5 0 0 1 15.5 5a2.5 2.5 0 0 1-2.45-3A10 10 0 0 0 12 2Z"/>
                        <circle cx="10.5" cy="9" r="1.5"/>
                        <circle cx="15.5" cy="13" r="1.5"/>
                        <circle cx="7.5" cy="16" r="1.5"/>
                    </svg>',
		);

		return $sub_pages;
	}

	/**
	 * Get default cookie categories.
	 *
	 * @since 2.0.6
	 *
	 * @return array
	 */
	public static function get_default_categories() {
		return array(
			'necessary' => array(
				'title' => 'Necessary',
				'desc'  => 'Necessary cookies are required to enable the basic features of this site, such as providing secure log-in or adjusting your consent preferences. These cookies do not store any personally identifiable data.',
			),
			'analytics' => array(
				'title' => 'Analytics',
				'desc'  => 'Analytical cookies are used to understand how visitors interact with the website. These cookies help provide information on metrics such as the number of visitors, bounce rate, traffic source, etc.',
			),
			'ads' => array(
				'title' => 'Advertising',
				'desc'  => 'Advertisement cookies are used to provide visitors with customised advertisements based on the pages you visited previously and to analyse the effectiveness of the ad campaigns.',
			),
			'functional' => array(
				'title' => 'Functional',
				'desc'  => 'Functional cookies help perform certain functionalities like sharing the content of the website on social media platforms, collecting feedback, and other third-party features.',
			),
		);
	}

	/**
	 * Get cookie details for scanner
	 *
	 * @since 2.0.6
	 *
	 * @return array
	 */
	public static function cookie_details() {
		$cookies = $_COOKIE;

		$cookie_details = [];
		foreach ($cookies as $name => $value) {
			$cookie_details[] = array(
				'name'        => htmlspecialchars($name),
				'value'       => htmlspecialchars($value),
				'domain'      => $_SERVER['HTTP_HOST'], 
				'description' => self::get_cookie_description($name),
				'duration'    => self::get_cookie_duration($name),
				'type'        => self::get_cookie_type($name)
			);
		}

		return $cookie_details;
	}

	/**
	 * Get cookie description.
	 *
	 * @since 2.0.6
	 *
	 * @param string $cookie_name
	 *
	 * @return string
	 */
	private static function get_cookie_description($cookie_name) {
		// Map cookie names to descriptions
		$descriptions = array(
			'physcookie-consent' => 'Stores user consent preferences for cookies.',
			'PHPSESSID'          => 'Session identifier for PHP.',
		);

		return isset($descriptions[$cookie_name]) ? $descriptions[$cookie_name] : esc_html('No description.','phys-core');
	}

	/**
	 * Get cookie duration.
	 *
	 * @since 2.0.6
	 *
	 * @param string $cookie_name
	 *
	 * @return string
	 */
	private static function get_cookie_duration($cookie_name) {
		// Map cookie names to durations
		$durations = array(
			'physcookie-consent' => '365 days',
			'PHPSESSID'          => 'Session',
		);

		// Check if the cookie is explicitly mapped
		if (isset($durations[$cookie_name])) {
			return $durations[$cookie_name];
		}

		// Check if the cookie name has the prefix 'sbjs_'
		if (strpos($cookie_name, 'sbjs_') === 0) {
			return 'Session';
		}

		// Default to 'Session' if no expiration date is set
		if (!isset($_COOKIE[$cookie_name])) {
			return esc_html__('Unknown duration', 'phys-core');
		}

		// Check if the cookie is a session cookie (no expiration date)
		$cookie_data = $_COOKIE[$cookie_name];
		if (empty($cookie_data)) {
			return esc_html__('Session', 'phys-core');
		}

		return esc_html__('Unknown duration', 'phys-core');
	}

	/**
	 * Get cookie type based on domain.
	 *
	 * @since 2.0.6
	 *
	 * @param string $cookie_name
	 * @param string $cookie_domain
	 *
	 * @return string
	 */
	private static function get_cookie_type($cookie_name, $cookie_domain = null) {
		$current_domain = $_SERVER['HTTP_HOST'];

		// Check if the cookie is third-party or site cookies
		if ($cookie_domain && $cookie_domain !== $current_domain) {
			return esc_html__('Third-party','phys-core');
		}

		return esc_html__('Site cookies','phys-core');
	}

	/**
	 * Handle AJAX request to update cookie manager form.
	 *
	 * @since 2.0.6
	 */
	public function update_cookie_manager_form() {
		// Verify the AJAX request
		if ( ! isset( $_POST['cookie_category'] ) ) {
			wp_send_json_error( array( 'message' => 'Invalid request.' ) );
			return;
		}

		// Sanitize the input
		$cookie_category = sanitize_text_field( $_POST['cookie_category'] );

		// Retrieve the category data
		$categories = self::get_data_settings()['cookie_categories'];

		// Check if the category exists
		if ( isset( $categories[ $cookie_category ] ) ) {
			$category_data = $categories[ $cookie_category ];

			ob_start();

			$data = array(
				'cookie_category' => $cookie_category, 
				'categories'      => $categories,
				'cookie_list'     => self::get_data_settings()['cookie_list'],
			);
			Phys_Template_Helper::template( 'cookie-category-fields.php', $data, true );
                   
			$form_html = ob_get_clean();

			wp_send_json_success( array( 'form_html' => $form_html ) );
		} else {
			wp_send_json_error( array( 'message' => 'Category not found.' ) );
		}
	}

	/**
	 * Save cookie_consent_settings
	 *
	 * @since 2.0.6
	 *
	 */
	public function phys_save_cookie_consent_settings() {
		// Verify nonce for security
		if ( ! isset( $_POST['cookie_consent_nonce'] ) || ! wp_verify_nonce( $_POST['cookie_consent_nonce'], 'cookie_consent_settings_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce.' ) );
			exit;
		}

		// Hander cookie banner settings data
		$enable_popup 		 	= isset( $_POST['enable_popup'] ) ? sanitize_text_field( $_POST['enable_popup'] ) : '';
		$popup_position 	 	= isset( $_POST['popup_position'] ) ? sanitize_text_field( $_POST['popup_position'] ) : 'bottom-left';
		$enable_mobile_popup 	= isset( $_POST['enable_mobile_popup'] ) ? sanitize_text_field( $_POST['enable_mobile_popup'] ) : '';
		$consent_message 	 	= isset( $_POST['consent_message'] ) ? wp_kses_post( $_POST['consent_message'] ) : '';
		$customise_consent_mess = isset( $_POST['customise_consent_mess'] ) ? wp_kses_post( $_POST['customise_consent_mess'] ) : '';

		// Handle cookie categories data
		$cookie_category = isset( $_POST['cookie_category'] ) ? sanitize_text_field( $_POST['cookie_category'] ) : 'necessary';
		$cookie_title    = isset( $_POST['cat_cookie_title'] ) ? sanitize_text_field( $_POST['cat_cookie_title'] ) : '';
		$cookie_desc     = isset( $_POST['cat_cookie_desc'] ) ? sanitize_text_field( $_POST['cat_cookie_desc'] ) : '';

		// Initialize default cookie categories with default values
		$categories_data = get_option( 'phys_cookie_categories', self::get_default_categories() );
	
		// Update the specific category with submitted data
		if ( $cookie_category ) {
			$categories_data[ $cookie_category ] = array(
				'title' => $cookie_title ?: $categories_data[ $cookie_category ]['title'],
				'desc'  => $cookie_desc ?: $categories_data[ $cookie_category ]['desc'],
			);
		}

		// Handle cookie list data grouped by category
		$cookie_list_data = get_option( 'phys_cookie_list', array() );

		// Ensure the category exists in the cookie list data
		if ( ! isset( $cookie_list_data[ $cookie_category ] ) ) {
			$cookie_list_data[ $cookie_category ] = array();
		}
	
		// Append new cookie list data to the existing category
		if ( isset( $_POST['ck_list_id'] ) && is_array( $_POST['ck_list_id'] ) ) {
			foreach ( $_POST['ck_list_id'] as $index => $id ) {
				$cookie_list_data[ $cookie_category ][] = array(
					'id'       => sanitize_text_field( $id ),
					'domain'   => sanitize_text_field( $_POST['ck_list_domain'][ $index ] ?? '' ),
					'duration' => sanitize_text_field( $_POST['ck_list_duration'][ $index ] ?? '' ),
					'desc'     => sanitize_text_field( $_POST['ck_list_desc'][ $index ] ?? '' ),
					'src'      => sanitize_text_field( $_POST['ck_list_src'][$index] ?? '' ),
				);
			}
		}

		// Save data to wp_options table
		if ( isset( $_POST['cookie_category'] ) ) {
			update_option( 'phys_cookie_categories', $categories_data );
			update_option( 'phys_cookie_list', $cookie_list_data );
		} else {
			update_option( 'phys_cookie_enable_popup', $enable_popup );
			update_option( 'phys_cookie_popup_position', $popup_position );
			update_option( 'phys_cookie_enable_mobile_popup', $enable_mobile_popup );
			update_option( 'phys_cookie_consent_message', $consent_message );
			update_option( 'phys_cookie_customise_consent_mess', $customise_consent_mess );
		}

		exit;
	}


	/**
	 * Edit cookie_list in phys_cookie_list option
	 *
	 * @since 2.0.6
	 *
	 */
	public function phys_edit_cookie_list_handler() {
		$cookie_id 		 = sanitize_text_field($_POST['cookie_id']);
		$cookie_category = sanitize_text_field($_POST['cookie_category']);
	
		// Retrieve the existing cookie list
		$cookie_list = get_option('phys_cookie_list', array());
	
		if (isset($cookie_list[$cookie_category])) {
			// Filter out the cookie with the specified ID
			$cookie_list[$cookie_category] = array_filter($cookie_list[$cookie_category], function ($cookie) use ($cookie_id) {
				return $cookie['id'] !== $cookie_id;
			});
	
			// Update the option
			update_option('phys_cookie_list', $cookie_list);
	
			wp_send_json_success(['message' => 'Cookie deleted successfully.']);
		} else {
			wp_send_json_error(['message' => 'Cookie not found.']);
		}
	
		exit;
	}

	/**
	 * Get arguments for template.
	 *
	 * @since 2.0.6
	 *
	 * @return null
	 */
	protected function get_template_args() {
		$args = self::get_data_settings();

		return $args;
	}

	/**
	 * Get data cookie consent settings
	 *
	 * @since 2.0.6
	 *
	 * @return string
	 */
	public static function get_data_settings() {
		// Retrieve all cookie consent settings from wp_options
		$options = array(
			'enable_popup'        		=> get_option( 'phys_cookie_enable_popup', '' ),
			'popup_position'      		=> get_option( 'phys_cookie_popup_position', 'bottom-left' ),
			'enable_mobile_popup' 		=> get_option( 'phys_cookie_enable_mobile_popup', '' ),
			'consent_message'     		=> get_option( 'phys_cookie_consent_message', 'We use cookies to improve your experience, including essential cookies required for the website to function. By continuing, you agree to our use of cookies. <a href="#">Learn more</a>.' ),
			'customise_consent_mess'  	=> get_option( 'phys_cookie_customise_consent_mess', '<h4 class="heading-top">Customise Consent Preferences</h4>We use cookies to help you navigate efficiently and perform certain functions. You will find detailed information about all cookies under each consent category below. {{necessary}}{{analytics}}{{ads}}{{functional}}' ),
			'cookie_categories'  		=> get_option( 'phys_cookie_categories', self::get_default_categories() ),
			'cookie_list'          		=> get_option( 'phys_cookie_list', array() ),
		);
	
		return $options;
	}

	/**
	 * Render banner.
	 *
	 * @since 2.0.6
	 */
	public function render_banner() {
		$options = self::get_data_settings();

		$args = array(
			'template' => 'cookie-banner.php', 
			'id'       => 'cookie-banner',
			'options'  => $options
		);

		if( $options['enable_popup'] == 'on' ) {
			return Phys_Template_Helper::template( 'modals/cookie-consent.php', $args, true );
		}
	}

	/**
	 * Enqueue script.
	 *
	 * @since 2.0.6
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_myself() ) {
			return;
		}
		
		wp_enqueue_script( 'phys-core-cookie-consent', PHYS_CORE_ADMIN_URI . '/assets/js/cookie-consent.js', array(
			'jquery'
		), PHYS_CORE_VERSION );

		// Pass ajaxurl and nonce to JavaScript
		wp_localize_script( 'phys-core-cookie-consent', 'physCookieConsent', array(
			'ajaxurl' 		=> admin_url( 'admin-ajax.php' ),
			'nonce'   		=> wp_create_nonce( 'cookie_consent_settings_nonce' ),
			'cookieDetails' => self::cookie_details(),
		) );
	}

	/**
	 * Frontend Enqueue script.
	 *
	 * @since 2.0.6
	 */
	public function fe_enqueue_scripts() {
		$options = self::get_data_settings();

		if( $options['enable_popup'] == 'on' ) {
			wp_enqueue_style( 'phys-core-cookie-consent', PHYS_CORE_URI . '/assets/css/modal.css' );
			wp_enqueue_script( 'phys-core-cookie-consent', PHYS_CORE_URI . '/assets/js/cookie-consent.js', array(
				'jquery'
			), PHYS_CORE_VERSION );
			wp_localize_script('phys-core-cookie-consent', 'physCookieList', get_option( 'phys_cookie_list', [] ));
		}
	}
	
}