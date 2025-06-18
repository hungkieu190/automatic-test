<?php
namespace PhysCode\Customizer\Field;

class Toggle extends Checkbox_Switch {

	public $type = 'phys-toggle';

	protected $control_class = '\PhysCode\Customizer\Control\Toggle';

	public function filter_control_args( $args, $wp_customize ) {
		if ( $args['id'] === $this->args['id'] ) {
			$args         = parent::filter_control_args( $args, $wp_customize );
			$args['type'] = 'phys-toggle';
		}

		return $args;
	}
}
