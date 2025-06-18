<?php
namespace PhysCode\Customizer\Field;

use PhysCode\Customizer\Modules\Field;

class Dimension extends Field {

	public $type = 'phys-dimension';

	protected $control_class = '\PhysCode\Customizer\Control\Dimension';

	protected $control_has_js_template = true;

	public function filter_setting_args( $args, $wp_customize ) {
		if ( $args['id'] === $this->args['id'] ) {
			$args = parent::filter_setting_args( $args, $wp_customize );

			if ( ! isset( $args['sanitize_callback'] ) || ! $args['sanitize_callback'] ) {
				$args['sanitize_callback'] = 'sanitize_text_field';
			}
		}

		return $args;
	}

	public function filter_control_args( $args, $wp_customize ) {
		if ( $args['id'] === $this->args['id'] ) {
			$args         = parent::filter_control_args( $args, $wp_customize );
			$args['type'] = 'phys-dimension';
		}
		
		return $args;
	}
}
