<?php
namespace PhysCode\Customizer\Field;

if ( ! class_exists( '\PhysCode\Customizer\Field\Radio' ) ) {
	require_once PHYS_CUSTOMIZER_DIR . '/controls/radio/field.php';
}

class Radio_Image extends Radio {

	public $type = 'phys-radio-image';

	protected $control_class = '\PhysCode\Customizer\Control\Radio_Image';

	public function filter_control_args( $args, $wp_customize ) {
		if ( $args['id'] === $this->args['id'] ) {
			$args         = parent::filter_control_args( $args, $wp_customize );
			$args['type'] = 'phys-radio-image';
		}

		return $args;
	}
}
