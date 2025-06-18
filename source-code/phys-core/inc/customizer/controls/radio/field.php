<?php 
namespace PhysCode\Customizer\Field;

use PhysCode\Customizer\Modules\Field;

class Radio extends Field {

	public $type = 'phys-radio';

	protected $control_class = '\PhysCode\Customizer\Control\Radio';

	protected $control_has_js_template = true;

	public function filter_setting_args( $args, $wp_customize ) {
		if ( $args['id'] === $this->args['id'] ) {
			$args = parent::filter_setting_args( $args, $wp_customize );

			if ( ! isset( $args['sanitize_callback'] ) || ! $args['sanitize_callback'] ) {
				$args['sanitize_callback'] = function( $value ) {
					if ( ! isset( $this->args['choices'][ $value ] ) ) {
						return ( isset( $this->args['default'] ) ) ? $this->args['default'] : '';
					}

					return $value;
				};
			}
		}

		return $args;
	}

	public function filter_control_args( $args, $wp_customize ) {
		if ( $args['id'] === $this->args['id'] ) {
			$args         = parent::filter_control_args( $args, $wp_customize );
			$args['type'] = 'phys-radio';
		}

		return $args;
	}
}
