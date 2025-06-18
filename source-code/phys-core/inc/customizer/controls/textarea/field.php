<?php
namespace PhysCode\Customizer\Field;

class Textarea extends Generic {

	public $type = 'phys-textarea';

	public function filter_control_args( $args, $wp_customize ) {
		if ( $args['id'] === $this->args['id'] ) {
			$args = parent::filter_control_args( $args, $wp_customize );

			$args['type'] = 'phys-generic';

			$args['choices']            = isset( $args['choices'] ) ? $args['choices'] : array();
			$args['choices']['element'] = 'textarea';
			$args['choices']['rows']    = '5';
		}

		return $args;
	}
}
