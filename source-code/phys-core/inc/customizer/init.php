<?php
/**
 * New Customizer Class
 *
 * @package WordPress
 * @subpackage New Customizer Class
 * @author Nhamdv
 */

namespace PhysCode\Customizer;

define( 'PHYS_CUSTOMIZER_DIR', dirname( __FILE__ ) );
define( 'PHYS_CUSTOMIZER_URI', PHYS_CORE_URI . '/inc/customizer' );

class Init {

	private static $instance;

	public function __construct() {
		$this->autoload();
		$this->includes();
		$this->register();
		$this->hooks();
	}

	public function register() {
		$classes = array(
			'\PhysCode\Customizer\Modules\Css',
			'\PhysCode\Customizer\Modules\Webfonts',
			'\PhysCode\Customizer\Modules\Dependencies',
			'\PhysCode\Customizer\Modules\Postmessage',
			'\PhysCode\Customizer\Modules\Tooltips',
			'\PhysCode\Customizer\Modules\Loading',
		);

		foreach ( $classes as $class ) {
			if ( class_exists( $class ) ) {
				new $class();
			}
		}
	}

	public function hooks() {
		add_action(
			'customize_preview_init',
			function() {
				$script_info = include PHYS_CUSTOMIZER_DIR . '/build/preview.asset.php';

				wp_enqueue_script( 'phys-customizer-control', PHYS_CUSTOMIZER_URI . '/build/preview.js', array_merge( $script_info['dependencies'], array( 'jquery', 'customize-preview' ) ), $script_info['version'], true );
			}
		);
 	}

	public function autoload() {
		require_once wp_normalize_path( PHYS_CUSTOMIZER_DIR . '/autoloader.php' );

		$autoloader = new \PhysCode\Customizer\Autoloader();
		$autoloader->add_namespace( 'PhysCode\Customizer\Modules', PHYS_CUSTOMIZER_DIR . '/modules/' );
		$autoloader->add_namespace( 'PhysCode\Customizer\Utils', PHYS_CUSTOMIZER_DIR . '/utils/' );
		$autoloader->register();
	}

	public function includes() {
		foreach ( glob( PHYS_CUSTOMIZER_DIR . '/controls/*/' ) as $control ) {
			if ( file_exists( $control . 'control.php' ) ) {
				require_once wp_normalize_path( $control . 'control.php' );
			}
			if ( file_exists( $control . 'field.php' ) ) {
				require_once wp_normalize_path( $control . 'field.php' );
			}
			if ( file_exists( $control . 'css.php' ) ) {
				require_once wp_normalize_path( $control . 'css.php' );
			}
		}

		// Load Kirki-Font use for in theme.
		require_once wp_normalize_path( PHYS_CUSTOMIZER_DIR . '/class-kirki-fonts.php' );
	}

	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Init::instance();
