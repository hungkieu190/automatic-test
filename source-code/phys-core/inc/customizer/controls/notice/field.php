<?php
namespace PhysCode\Customizer\Field;

use PhysCode\Customizer\Modules\Field;

class Notice extends Field {

	public $type = 'phys-notice';

	protected $control_class = '\PhysCode\Customizer\Control\Notice';

	protected $control_has_js_template = true;

	public function filter_control_args( $args, $wp_customize ) {
		if ( $args['id'] === $this->args['id'] ) {
			$args         = parent::filter_control_args( $args, $wp_customize );
			$args['type'] = 'phys-notice';
		}

		return $args;
	}
}
