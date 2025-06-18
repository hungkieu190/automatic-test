<?php

/**
 * Class Phys_Core_Customizer
 *
 * @package   Phys_Core
 * @since     0.1.0
 */
if ( ! class_exists( 'Phys_Core_Customizer' ) ) {
	class Phys_Core_Customizer extends Phys_Singleton {

		protected static $fields = array();

		protected function __construct() {
			$this->init();
			$this->init_hooks();
		}

		/**
		 * Init class.
		 *
		 * @since 0.1.0
		 */
		private function init() {
			$this->include_customizer_module();
		}

		/**
		 * Init hooks.
		 *
		 * @since 0.1.0
		 */
		private function init_hooks() {
 			add_action( 'wp_loaded', array( $this, 'customizer_register' ) );

 			add_action( 'after_setup_theme', array( $this, 'add_section_notice_php_version' ) );
		}

		/**
		 * Add section notice PHP version < 7.0
		 *
		 * @since 1.2.0
		 */
		public function add_section_notice_php_version() {
			$php_version = phpversion();

			if ( version_compare( $php_version, '7.0', '>=' ) ) {
				return;
			}

			$guide = sprintf(
				__( 'Your server is running PHP versions %1$s. It is recommended to be on at least PHP 7.0 and preferably PHP 7.x to process all the theme\'s functionalities. See %2$s', 'phys-core' ),
				$php_version,
				'<a href="https://goo.gl/WRBYv3" target="_blank" rel="noopener">' . __( 'How to update your PHP version.', 'phys-core' ) . '</a>'
			);

			$this->add_section(
				array(
					'id'       => 'tc-theme-notice-php-version',
					'title'    => esc_html__( 'Upgrade PHP version', 'phys-core' ),
					'priority' => 0,
					'icon'     => 'dashicons-warning',
				)
			);

			$this->add_field(
				array(
					'id'       => 'tc-theme-notice-php-version',
					'type'     => 'custom',
					'default'  => $guide,
					'priority' => 1,
					'section'  => 'tc-theme-notice-php-version',
				)
			);
		}


		/**
		 * Include Customizer module.
		 */
		private function include_customizer_module() {
			include_once PHYS_CORE_INC_PATH . '/customizer/init.php';
		}

		/**
		 * Register hook register customizer.
		 *
		 * @since 0.1.0
		 */
		public function customizer_register() {
			do_action( 'phys_customizer_register' );
		}

		public function add_panel( array $panel ) {
			if ( class_exists( '\PhysCode\Customizer\Modules\Panel' ) ) {
				new \PhysCode\Customizer\Modules\Panel( $panel['id'], $panel );
			}
		}

		public function add_section( array $section ) {
			if ( class_exists( '\PhysCode\Customizer\Modules\Section' ) ) {
				new \PhysCode\Customizer\Modules\Section( $section['id'], $section );
			}
		}

		public function add_field( array $field ) {
			// In Kirki use 'settings' for field id.
			if ( array_key_exists( 'settings', $field ) ) {
				$field['id'] = $field['settings'];
			}

			if ( $field['type'] == 'code' ) {
				$field['label'] = isset( $field['label'] ) ? $field['label'] : '';
			}

			if ( isset( $field['alpha'] ) && $field['type'] !== 'multicolor' ) {
				unset( $field['alpha'] );

				$choices          = isset( $field['choices'] ) ? $field['choices'] : array();
				$choices['alpha'] = true;

				$field['choices'] = $choices;
			}

			// Fix new line js_var
			if ( ! empty( $field['js_vars'] ) && is_array( $field['js_vars'] ) ) {
				$js_vars     = $field['js_vars'];
				$new_js_vars = array();

				foreach ( $js_vars as $js_var ) {
					$element           = isset( $js_var['element'] ) ? $js_var['element'] : '';
					$str               = preg_replace( '#\s+#', ' ', trim( $element ) );
					$js_var['element'] = $str;

					$new_js_vars[] = $js_var;
				}

				$field['js_vars'] = $new_js_vars;
			}

			self::$fields[ $field['id'] ] = $field;

			$str = str_replace( array( 'tp_', 'kirki-' ), '', $field['type'] ); // replace "tp_notice" to "notice"
			$str = str_replace( array( '-', '_' ), ' ', $str );

			$classname = '\PhysCode\Customizer\Field\\' . str_replace( ' ', '_', ucwords( $str ) );

			if ( $field['type'] === 'switch' ) {
				$classname = '\PhysCode\Customizer\Field\Checkbox_Switch';
			}

			if ( class_exists( $classname ) ) {
				unset( $field['type'] );
				new $classname( $field );
				return;
			}
		}

		public function add_group( array $group ) {
			$section  = $group['section'];
			$groups   = $group['groups'];
			$priority = isset( $group['priority'] ) ? $group['priority'] : 10;

			foreach ( $groups as $group ) {
				$fields   = $group['fields'];
				$group_id = $group['id'];

				foreach ( $fields as $field ) {
					$update_field             = $field;
					$update_field['section']  = $section;
					$update_field['priority'] = $priority;
					$update_field['hide']     = true;

					$this->add_field( $update_field );
				}
			}
		}

		/**
		 * Get options customizer.
		 *
		 * @return array
		 * @since 0.1.0
		 */
		public static function get_options() {
			global $phys_customizer_options;

			if ( empty( $phys_customizer_options ) ) {
				$phys_customizer_options = get_theme_mods();
			}

			return (array) $phys_customizer_options;
		}

		/**
		 * Get option customizer by key.
		 *
		 * @param string  $key
		 * @param        $default
		 *
		 * @return mixed|null
		 * @since 0.1.0
		 */
		public static function get_option( $key, $default = false ) {
			$phys_customizer_options = self::get_options();

			if ( ! array_key_exists( $key, $phys_customizer_options ) ) {
				return $default;
			}

			return $phys_customizer_options[ $key ];
		}

	}
}
