<?php

/**
 * Class Phys_Product_Registration.
 *
 * @package   Phys_Core
 * @since     0.2.1
 */
class Phys_Product_Registration extends Phys_Singleton {
	/**
	 * @since 0.2.1
	 *
	 * @var string
	 */
	public static $key_callback_request = 'tc_callback_registration';

	/**
	 * Premium themes.
	 *
	 * @since 0.9.0
	 *
	 * @var null
	 */
	private static $themes = null;

	/**
	 * Deregister product registration.
	 *
	 * @return true|WP_Error
	 * @since 1.5.0
	 *
	 */
	public static function deregister() {
		if ( ! self::is_active() ) {
			return true;
		}

		$allow_deregister = apply_filters( 'phys_core_allow_deregister_activation', true );
		if ( ! $allow_deregister ) {
			return new WP_Error( 'not_allowed', __( 'Can not deregister activation.', 'phys-core' ) );
		}

		$_personal_token    = self::get_data_theme_register('personal_token');


		if ( $_personal_token ) {
			$request = Phys_Remote_Helper::post(
				Phys_Admin_Config::get( 'host_downloads_api' ) . '/wp-json/phys-market/v1/deactivate/',
				array(
					'body' => array(
						'site_code' =>self::get_data_theme_register('purchase_token'),
					),
				),
				true
			);

			if ( is_wp_error( $request ) ) {
				return $request;
			}

			if ( $request->status !== 'success' ) {
				return new WP_Error( 'something_went_wrong', ! empty( $request->message ) ? $request->message : __( 'Something went wrong!', 'phys-core' ) );
			}

		}

		self::destroy_active();

		return true;
	}

	/**
	 * Double check theme update before inject update theme.
	 *
	 * @since 1.1.1
	 */
	public static function double_check_theme_update() {
		$instance = self::instance();

		$instance->check_theme_update( true );
	}

	/**
	 * Get product registration data.
	 *
	 * @return array();
	 * @since 0.9.0
	 *
	 */
	public static function get_themes() {
		if ( self::$themes === null ) {
			self::$themes = get_site_option( 'phys_core_product_registration_themes' );
 		}

		self::$themes = (array) self::$themes;

		foreach ( self::$themes as $key => $theme ) {
			if ( is_numeric( $key ) ) {
				unset( self::$themes[$key] );
			}
		}

		return self::$themes;
	}

	/**
	 * Set product registration data.
	 *
	 * @param array $data
	 *
	 * @since 0.9.0
	 *
	 */
	private static function _set_themes( $data = array() ) {
		self::$themes = $data;

		update_site_option( 'phys_core_product_registration_themes', $data );
	}

	/**
	 * Get registration data by theme.
	 *
	 * @param       $field
	 * @param null  $theme
	 * @param mixed $default
	 *
	 * @return mixed
	 * @since 0.9.0
	 *
	 */
	public static function get_data_by_theme( $field, $default = false, $theme = null ) {

		if ( ! $theme ) {
			$theme = Phys_Theme_Manager::get_current_theme();
		}

		$registration_data = self::get_themes();

		if ( ! $registration_data ) {
			return $default;
		}

		$theme_data = isset( $registration_data[$theme] ) ? $registration_data[$theme] : false;
 		if ( ! $theme_data ) {
			return $default;
		}

		return isset( $theme_data[$field] ) ? $theme_data[$field] : $default;
	}

	/**
	 * Get filed data by theme.
	 *
	 * @param $theme
	 * @param $field
	 * @param $value
	 *
	 * @since 0.9.0
	 *
	 */
	public static function set_data_by_theme( $field, $value, $theme = null ) {
		if ( ! $theme ) {
			$theme = Phys_Theme_Manager::get_current_theme();
		}

		$registration_data = self::get_themes();

		$theme_data         = isset( $registration_data[$theme] ) ? $registration_data[$theme] : array();
		$theme_data         = (array) $theme_data;
		$theme_data[$field] = $value;

		$registration_data[$theme] = $theme_data;

		self::_set_themes( $registration_data );
	}

	/**
	 * Save item id.
	 *
	 * @param $item_id
	 *
	 * @since 0.7.0
	 *
	 */
	private static function save_item_id( $item_id ) {
		self::set_data_by_theme( 'envato_item_id', $item_id );
		self::set_time_activation_successful();
	}

	/**
	 * Set time activation successful.
	 *
	 * @param $time
	 *
	 * @since 0.8.0
	 *
	 */
	private static function set_time_activation_successful( $time = null ) {
		if ( ! $time ) {
			$time = time();
		}

		self::set_data_by_theme( 'time_activate_successful', $time );
	}



	/**
	 * Get personal token.
	 *
	 * @param $stylesheet
	 *
	 * @return bool|string
	 * @since 0.7.0
	 *
	 */
	public static function get_data_theme_register($key, $stylesheet = null ) {
		$option = self::get_data_by_theme( $key, false, $stylesheet );

		return $option;
	}

	public static function save_data_theme_register_key( $key, $value ) {
		self::set_data_by_theme(  $key, $value );
	}


	/**
	 * Get active theme from envato.
	 *
	 * @return bool
	 * @since 0.2.1
	 *
	 */
	public static function is_active() {
 		$purchase_code = self::get_data_theme_register('purchase_code');
		$is_active      = '';

	 	if ( $purchase_code ) {
			$is_active = ! empty( $purchase_code );
		}

		return $is_active;
	}

	/**
	 * Destroy active theme from envato.
	 *
	 * @since 0.8.0
	 */
	public static function destroy_active() {
		// self::save_site_key( false );
		if(self::get_data_theme_register('purchase_code')){
			self::save_data_theme_register_key('purchase_code', false);
			self::save_data_theme_register_key('purchase_token', false);
		}else{
			delete_option( 'phys_core_product_registration_themes' );
			// self::save_data_theme_register_key('site_key', false);
		}
	}

	/**
	 * Get url auth.
	 *
	 * @return string
	 * @since 0.2.1
	 *
	 */
	public static function get_url_auth() {
		$base_url = Phys_Admin_Config::get( 'host_envato_app' ) . '/register';

		return $base_url;
	}
	// Use in plugin thim-elementor-kit, get url of library template elemntor when import
	public static function get_url_fetch() {
		$base_url = Phys_Admin_Config::get( 'host_downloads_api' ) . '/wp-json/thim_em/v1/thim-kit/import-library';

		return $base_url;
	}

	// Use in plugin thim-elementor-kit, link return active license when license error
	public static function menu_admin_active_license() {
 		return 'phys-license';
	}

	/**
	 * Get verify callback url.
	 *
	 * @param $return
	 *
	 * @return string
	 * @since 0.2.1
	 *
	 */
	public static function get_url_verify_callback( $return = false ) {
		$url = Phys_Dashboard::get_link_main_dashboard(
			array(
				self::$key_callback_request => 1,
			)
		);

		if ( $return ) {
			$url = add_query_arg(
				array(
					'return' => urlencode( $return ),
				),
				$url
			);
		}

		return $url;
	}

	/**
	 * Get url link download theme from envato.
	 *
	 * @param $stylesheet
	 *
	 * @return WP_Error|string
	 * @since 0.7.0
	 *
	 */
	public static function get_url_download_theme( $stylesheet = null ) {

		$theme_data = Phys_Theme_Manager::get_metadata();
		$item_id    = $theme_data['envato_item_id'] ?? 0;


		return Phys_Envato_API::get_url_download_item( $item_id );
	}

	/**
	 * Get link review of theme on themeforest.
	 *
	 * @sicne
	 *
	 * @return string
	 */
	public static function get_link_reviews() {
		$link       = 'https://themeforest.net/downloads';
		$theme_data = Phys_Theme_Manager::get_metadata();
		$item_id    = $theme_data['envato_item_id'];

		if ( ! empty( $item_id ) ) {
			$link .= sprintf( '#item-%s', $item_id );
		}

		return $link;
	}

	/**
	 * Phys_Product_Registration constructor.
	 *
	 * @since 0.2.1
	 */
	protected function __construct() {
		$this->init_hooks();
		$this->upgrader();
	}

	/**
	 * Upgrader.
	 *
	 * @since 0.9.0
	 */
	private function upgrader() {
		Phys_Auto_Upgrader::instance();
	}

	/**
	 * Init hooks.
	 *
	 * @since 0.2.1
	 */
	private function init_hooks() {
 		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'wp_ajax_phys_core_update_theme', array( $this, 'ajax_update_theme' ) );
		add_action( 'phys_core_background_check_update_theme', array( $this, 'background_check_update_theme' ), 1 );
		add_action( 'phys_core_list_modals', array( $this, 'add_modal_activate_theme' ) );
		add_action( 'phys_core_dashboard_init', array( $this, 'handle_deregister' ) );
		add_action( 'template_redirect', array( $this, 'handle_connect_check_activation' ) );
  	}

	/**
	 * Handle request check activation.
	 *
	 * @since 1.4.10
	 */
	public function handle_connect_check_activation() {
		$check = isset( $_REQUEST['phys-core-check-activation'] );

		if ( ! $check ) {
			return;
		}

		if ( ! self::is_active() ) {
			wp_send_json_error(
				__( 'Site has not been activate theme.', 'phys-core' )
			);
		}

		wp_send_json_success( __( 'Ok!', 'phys-core' ) );
	}

	/**
	 * Handle deregister.
	 *
	 * @since 1.4.2
	 */
	public function handle_deregister() {
		if ( ! isset( $_REQUEST['phys-core-deregister'] ) ) {
			return;
		}

		$result = self::deregister();

		if ( is_wp_error( $result ) ) {
			$link = Phys_Dashboard::get_link_main_dashboard();
			$link = add_query_arg(
				array(
					'phys-core-error' => $result->get_error_code(),
				),
				$link
			);
			phys_core_redirect( $link );

			return;
		}

		$link = Phys_Dashboard::get_link_main_dashboard();
		phys_core_redirect( $link );
	}

	/**
	 * Add modal activate theme.
	 *
	 * @since 1.3.4
	 */
	public function add_modal_activate_theme() {
		if ( self::is_active() ) {
			return;
		}

		Phys_Modal::render_modal( array(
			'id'       => 'tc-modal-activate-theme',
			'template' => 'registration/activate-modal.php',
		) );
	}

	/**
	 * Handle ajax update theme.
	 *
	 * @since 1.1.0
	 */
	public function ajax_update_theme() {
		check_ajax_referer( 'phys_core_update_theme', 'nonce' );

		$theme_data = Phys_Theme_Manager::get_metadata();
		$theme      = $theme_data['template'];

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Theme_Upgrader( $skin );
		$results  = $upgrader->bulk_upgrade( array( $theme ) );
		$messages = $skin->get_upgrade_messages();

		if ( ! $results || ! isset( $results[$theme] ) ) {
			wp_send_json_error( $messages );
		}

		$result = $results[$theme];
		if ( ! $result ) {
			wp_send_json_error( array( __( 'Something went wrong! Please try again later.', 'phys-core' ) ) );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_messages() );
		}

		$theme_data = Phys_Theme_Manager::get_metadata( true );
		$theme      = $theme_data['version'];

		wp_send_json_success( $theme );
	}

	/**
	 * Check update theme in background.
	 *
	 * @since 1.1.0
	 */
	public function background_check_update_theme() {
		$force = isset( $_GET['force-check'] );

		$this->check_theme_update( $force );
	}

	/**
	 * Get check update themes.
	 *
	 * @return array
	 * @since 1.1.0
	 *
	 */
	public static function get_update_themes() {
		$update = get_option( 'phys_core_check_update_themes', array() );

		return wp_parse_args( $update, array(
			'last_checked' => false,
			'themes'       => array(),
		) );
	}

	/**
	 * Check update theme from envato.
	 *
	 * @param $force bool
	 *
	 * @since 1.1.0
	 *
	 */
	private function check_theme_update( $force = false ) {
		$update_themes = self::get_update_themes();
		$last_checked  = $update_themes['last_checked'];
		$now           = time();
		$timeout       = 12 * 3600;

		if ( ! $force && $last_checked && $now - $last_checked < $timeout ) {
			return;
		}

		$theme_data      = Phys_Theme_Manager::get_metadata();
		$item_id         = $theme_data['envato_item_id'];
		$current_version = $theme_data['version'];

		$checker                       = new Phys_Theme_Envato_Check_Update( $item_id, $current_version );
		$update_themes['last_checked'] = $now;
		$data                          = $checker->get_theme_data();

		$themes   = (array) $update_themes['themes'];
		$template = $theme_data['template'];
		if ( $data ) {
			$themes[$template] = array(
				'update'       => $checker->can_update(),
				'theme'        => $template,
				'name'         => $data['theme_name'],
				'description'  => $data['description'],
				'version'      => $data['version'],
				'icon'         => $data['icon'],
				'author'       => $data['author_name'],
				'author_url'   => $data['author_url'],
				'rating'       => $data['rating'],
				'rating_count' => $data['rating_count'],
				'url'          => $data['url'],
				'package'      => '',
			);
		} else {
			unset( $themes[$template] );
		}

		$update_themes['themes'] = $themes;

		update_option( 'phys_core_check_update_themes', $update_themes );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param $page_now
	 *
	 * @since 0.7.0
	 */
	public function enqueue_scripts( $page_now ) {
		if ( strpos( $page_now, Phys_Dashboard::$prefix_slug . 'dashboard' ) === false ) {
			return;
		}

		wp_enqueue_script( 'phys-theme-update', PHYS_CORE_ADMIN_URI . '/assets/js/theme-update.js', array( 'jquery' ), PHYS_CORE_VERSION );

		$this->_localize_script();
	}

	/**
	 * Localize script.
	 *
	 * @since 0.7.0
	 */
	private function _localize_script() {
		$nonce           = wp_create_nonce( 'phys_core_update_theme' );
		$link_deregister = Phys_Dashboard::get_link_main_dashboard(
			array(
				'phys-core-deregister' => true,
			)
		);

		wp_localize_script( 'phys-theme-update', 'phys_theme_update', array(
			'admin_ajax'     => admin_url( 'admin-ajax.php' ),
			'action'         => 'phys_core_update_theme',
			'nonce'          => $nonce,
			'url_deregister' => $link_deregister,
			'i18l'           => array(
				'confirm_deregister' => __( 'Are you sure to remove theme activation??', 'phys-core' ),
				'updating'           => __( 'Updating...', 'phys-core' ),
				'updated'            => __( 'Theme is up to date', 'phys-core' ),
				'wrong'              => __( 'Some thing went wrong. Please try again later!', 'phys-core' ),
				'warning_leave'      => __( 'The update process will cause errors if you leave this page!', 'phys-core' ),
				'text_version'      => __( 'Your Version is', 'phys-core' ),
			),
		) );
	}

}
