<?php
/**
 * Phys Core Plugin.
 * Plugin Name:       PhysCode Core
 * Plugin URI:        https://physcode.com
 * Description:       The Ultimate Core Processor of all WordPress themes by PhysCode - Manage your website easier with Phys Core.
 * Author:            PhysCode
 * Version:           2.0.7.beta
 * Author URI:        https://physcode.com
 * Text Domain:       phys-core
 * Domain Path:       /languages
 * Tested up to: 6.0
 * Requires PHP: 7.0
 * @package   Phys_Core
 * @since     0.2.0
 * @author    PhysCode <contact@physcode.com>
 * @link      https://physcode.com
 * @copyright 2016 PhysCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Class TP
 *
 * @since 0.1.0
 */
if ( ! class_exists( 'PC' ) ) {
	class PC {
		/**
		 * @var null
		 *
		 * @since 0.1.0
		 */
		protected static $_instance = null;

		/**
		 * @var string
		 *
		 * @since 0.1.0
		 */
		public static $prefix = 'phys_';

		/**
		 * @var string
		 *
		 * @since 0.8.5
		 */
		public static $slug = 'phys-core';

		/**
		 * @var string
		 *
		 * @since 0.2.0
		 */
		private static $option_version = 'phys_core_version';

		/**
		 * Return unique instance of TP.
		 *
		 * @since 0.1.0
		 */
		static function instance() {
			if ( ! self::$_instance ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Phys_Framework constructor.
		 *
		 * @since 0.1.0
		 */
		private function __construct() {
			$this->init();
			$this->hooks();

			do_action( 'phys_core_loaded' );
		}

		/**
		 * Get is debug?
		 *
		 * @return bool
		 * @since 0.1.0
		 */
		public static function is_debug() {
			if ( ! defined( 'PHYS_DEBUG' ) ) {
				return false;
			}

			return (bool) PHYS_DEBUG;
		}

		/**
		 * Init class.
		 *
		 * @since 0.1.0
		 */
		public function init() {
			do_action( 'before_phys_core_init' );

			$this->define_constants();
			$this->providers();

			spl_autoload_register( array( $this, 'autoload' ) );

			$this->inc();
			$this->admin();

			do_action( 'phys_core_init' );
		}

		/**
		 * Define constants.
		 *
		 * @since 0.2.0
		 */
		private function define_constants() {
			$this->define( 'PHYS_CORE_FILE', __FILE__ );
			$this->define( 'PHYS_CORE_PATH', dirname( __FILE__ ) );

			$this->define( 'PHYS_CORE_URI', untrailingslashit( plugins_url( '/', PHYS_CORE_FILE ) ) );
			$this->define( 'PHYS_CORE_ASSETS_URI', PHYS_CORE_URI . '/assets' );

			$this->define( 'PHYS_CORE_VERSION', '2.0.7.beta' );

			$this->define( 'PHYS_CORE_ADMIN_PATH', PHYS_CORE_PATH . '/admin' );
			$this->define( 'PHYS_CORE_ADMIN_URI', PHYS_CORE_URI . '/admin' );
			$this->define( 'PHYS_CORE_INC_PATH', PHYS_CORE_PATH . '/inc' );
			$this->define( 'PHYS_CORE_INC_URI', PHYS_CORE_URI . '/inc' );

			$this->define( 'TP_THEME_PHYS_DIR', trailingslashit( get_template_directory() ) );
			$this->define( 'TP_THEME_PHYS_URI', trailingslashit( get_template_directory_uri() ) );
			$this->define( 'TP_CHILD_THEME_PHYS_DIR', trailingslashit( get_stylesheet_directory() ) );
			$this->define( 'TP_CHILD_THEME_PHYS_URI', trailingslashit( get_stylesheet_directory_uri() ) );
		}

		/**
		 * Define constant.
		 *
		 * @param $name
		 * @param $value
		 *
		 * @since 1.0.0
		 *
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Init hooks.
		 *
		 * @since 0.1.0
		 */
		private function hooks() {
			register_activation_hook( __FILE__, array( $this, 'install' ) );
			add_action( 'activated_plugin', array( $this, 'activated' ) );
			add_action( 'plugins_loaded', array( $this, 'text_domain' ), 1 );
		}

		/**
		 * Autoload classes.
		 *
		 * @param $class
		 *
		 * @return bool
		 * @since 1.0.0
		 *
		 */
		public function autoload( $class ) {
			$class = strtolower( $class );

			$file_name = 'class-' . str_replace( '_', '-', $class ) . '.php';

			/**
			 * Helper classes.
			 */
			if ( strpos( $class, 'helper' ) !== false ) {
				$path = PHYS_CORE_PATH . '/helpers/' . $file_name;

				return $this->_require( $path );
			}

			/**
			 * Inc
			 */
			$path = PHYS_CORE_INC_PATH . DIRECTORY_SEPARATOR . $file_name;
			if ( is_readable( $path ) ) {
				return $this->_require( $path );
			}

			/**
			 * Admin
			 */
			$path = PHYS_CORE_ADMIN_PATH . DIRECTORY_SEPARATOR . $file_name;
			if ( is_readable( $path ) ) {
				return $this->_require( $path );
			}

			return false;
		}

		/**
		 * Require file.
		 *
		 * @param $path
		 *
		 * @return bool
		 * @since 1.0.5
		 *
		 */
		private function _require( $path ) {
			if ( ! is_readable( $path ) ) {
				return false;
			}

			require_once $path;

			return true;
		}

		/**
		 * Providers.
		 *
		 * @since 0.8.5
		 */
		private function providers() {
			require_once PHYS_CORE_PATH . '/providers/class-phys-singleton.php';
		}

		/**
		 * Core.
		 *
		 * @since 0.1.0
		 */
		private function inc() {
			require_once PHYS_CORE_INC_PATH . '/class-phys-core.php';
		}

		/**
		 * Admin.
		 *
		 * @since 0.1.0
		 */
		private function admin() {
			require_once PHYS_CORE_PATH . '/admin/class-phys-core-admin.php';
		}

		/**
		 * Activation hook.
		 *
		 * @since 0.2.0
		 */
		public function install() {
			add_option( self::$option_version, PHYS_CORE_VERSION );

			do_action( 'phys_core_activation' );
		}

		/**
		 * Hook after plugin was activated.
		 *
		 * @param $plugin
		 *
		 * @since 0.2.0
		 *
		 */
		public function activated( $plugin ) {
			$plugins_are_activating = isset( $_POST['checked'] ) ? $_POST['checked'] : array();

			if ( count( $plugins_are_activating ) > 1 ) {
				return;
			}

			if ( 'phys-core/phys-core.php' !== $plugin ) {
				return;
			}

			Phys_Core_Admin::go_to_theme_dashboard();
		}

		/**
		 * Get active network.
		 *
		 * @return bool
		 * @since 0.8.1
		 *
		 */
		public static function is_active_network() {
			if ( ! is_multisite() ) {
				return true;
			}

			$plugin_file            = 'phys-core/phys-core.php';
			$active_plugins_network = get_site_option( 'active_sitewide_plugins' );

			if ( isset( $active_plugins_network[$plugin_file] ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Load text domain.
		 *
		 * @since 0.1.0
		 *
		 */
		function text_domain() {
			$locale = apply_filters( 'plugin_locale', get_locale(), 'phys-core' );

			load_textdomain(
				'phys-core',
				trailingslashit( WP_LANG_DIR ) . 'phys-core' . '/' . 'phys-core' . '-' . $locale . '.mo'
			);
			load_plugin_textdomain(
				'phys-core', false,
				basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/'
			);
		}
	}

	PC::instance();
}// End if().
