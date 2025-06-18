<?php
/**
 * Class Phys_Core
 *
 * @package   Phys_Core
 * @since     0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class Phys Core.
 *
 * @since 0.1.0
 */
if ( ! class_exists( 'Phys_Core' ) ) {
	class Phys_Core extends Phys_Singleton {
		/**
		 * Phys_Core constructor.
		 *
		 * @since 0.1.0
		 */
		protected function __construct() {
			$this->init_global_variables();
			$this->init();
			$this->run();
		}

		/**
		 * Init global variables.
		 *
		 * @since 0.8.8
		 */
		private function init_global_variables() {
			/**
			 * List notifications in dashboard.
			 */
			global $phys_notifications;
			$phys_notifications = array();
		}

		/**
		 * Init functions.
		 *
		 * @since 0.1.0
		 */
		private function init() {
			$this->includes();
		}

		/**
		 * Run.
		 *
		 * @since 0.5.0
		 */
		private function run() {
			Phys_Notification::instance();
			if ( apply_filters( 'phys-support-customizer', true ) ) {
				Phys_Core_Customizer::instance();
			}
		}

		/**
		 * Include functions.
		 *
		 * @since 0.1.0
		 */
		private function functions() {
			$this->_require( 'functions.php' );
		}

		/**
		 * Include libraries and functions.
		 *
		 * @since 0.1.0
		 */
		private function includes() {
			$this->libraries();
			$this->functions();
		}

		/**
		 * Include libraries.
		 *
		 * @since 0.1.0
		 */
		private function libraries() {
			$this->_require( 'class-phys-register-shortcode.php' );
			$this->_require( 'class-phys-breadcrumb.php' );
			$this->_require( 'class-phys-crop-image-size.php' );
			$this->_require( 'class-phys-core-likes-views.php' );
			$this->_require( 'class-tax-meta.php' );

		}


		/**
		 * Require file.
		 *
		 * @param $file
		 *
		 * @since 0.5.0
		 *
		 */
		private function _require( $file ) {
			$path = PHYS_CORE_INC_PATH . DIRECTORY_SEPARATOR . $file;

 			if ( ! file_exists( $path ) ) {
				return;
			}

			require_once $path;
		}
	}
}

Phys_Core::instance();
