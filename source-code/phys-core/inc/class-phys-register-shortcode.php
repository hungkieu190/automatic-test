<?php

namespace PhysCoreVCAddon;
class PC_VC_Addon {
	/**
	 * @var string
	 * @since 1.0
	 */

	/**
	 * @var object The single instance of the class
	 * @since 1.0
	 */


	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		// Register ShortCode With js_composer
		if ( function_exists( 'vc_map' ) ) {
			if ( function_exists( 'vc_add_shortcode_param' ) ) {
				vc_add_shortcode_param( 'number', array( $this, '_number_param' ) );
				vc_add_shortcode_param( 'radioimage', array( $this, '_radio_image_param' ) );
				vc_add_shortcode_param( 'datepicker', array( $this, '_datetimepicker_param' ) );
				vc_add_shortcode_param( 'multi_dropdown', array( $this, '_dropdown_multiple_param' ) );
			}
			add_action( 'vc_before_init', array( $this, 'get_elements' ) );
		} else {
			add_action( 'init', array( $this, 'get_elements' ) );
		}
	}

	/**
	 * Get all features.
	 *
	 * @return mixed
	 */
	public static function get_elements() {
 		$elements = apply_filters( 'phys_register_shortcode', array() );
 		foreach ( $elements as $prefix => $_elements ) {
			foreach ( $_elements as $element ) {
				add_shortcode( $element, $prefix . '_shortcode_' . $element );
			}
		}
	}

	/**
	 * @param $settings
	 * @param $value
	 *
	 * @return string
	 */
	public function _datetimepicker_param( $settings, $value ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		$value  = (string) $value;
		$output = '<div class="date_param_block">'
			. '<input name="' . esc_attr( $settings['param_name'] ) . '" class="vc_param_datepicker wpb_vc_param_value wpb-textinput ' .
			esc_attr( $settings['param_name'] ) . ' ' .
			esc_attr( $settings['type'] ) . '_field" type="text" value="' . esc_attr( $value ) . '" />'
			. '</div>';
		$output .= '<script>jQuery(\'.vc_param_datepicker\').datepicker();</script>';

		return $output;
	}

	/**
	 * @param $settings
	 * @param $value
	 *
	 * @return string
	 */
	public function _number_param( $settings, $value ) {
		$param_name = isset( $settings['param_name'] ) ? $settings['param_name'] : '';
		$type       = isset( $settings['type'] ) ? $settings['type'] : '';
		$min        = isset( $settings['min'] ) ? $settings['min'] : '';
		$max        = isset( $settings['max'] ) ? $settings['max'] : '';
		$suffix     = isset( $settings['suffix'] ) ? $settings['suffix'] : '';
		$class      = isset( $settings['class'] ) ? $settings['class'] : '';
		$value      = isset( $value ) ? $value : $settings['value'];
		$output     = '<input type="number" min="' . $min . '" max="' . $max . '" class="wpb_vc_param_value ' . $param_name . ' ' . $type . ' ' . $class . '" name="' . $param_name . '" value="' . $value . '" style="max-width:100px; margin-right: 10px;" />' . $suffix;

		return $output;
	}

	/**
	 * @param $settings
	 * @param $value
	 *
	 * @return string
	 */
	public function _radio_image_param( $settings, $value ) {
		$param_name = isset( $settings['param_name'] ) ? $settings['param_name'] : '';
		$type       = isset( $settings['type'] ) ? $settings['type'] : '';
		$radios     = isset( $settings['options'] ) ? $settings['options'] : '';
		$class      = isset( $settings['class'] ) ? $settings['class'] : '';
		$output     = '<input type="hidden" name="' . $param_name . '" id="' . $param_name . '" class="wpb_vc_param_value ' . $param_name . ' ' . $type . '_field ' . $class . '" value="' . $value . '"  ' . ' />';
		$output     .= '<div id="' . $param_name . '_wrap" class="icon_style_wrap ' . $class . '" >';
		if ( $radios != '' && is_array( $radios ) ) {
			$i = 0;
			foreach ( $radios as $key => $image_url ) {
				$class   = ( $key == $value ) ? ' class="selected" ' : '';
				$image   = '<img id="' . $param_name . $i . '_img' . $key . '" src="' . $image_url . '" ' . $class . '/>';
				$checked = ( $key == $value ) ? ' checked="checked" ' : '';
				$output  .= '<input name="' . $param_name . '_option" id="' . $param_name . $i . '" value="' . $key . '" type="radio" '
					. 'onchange="document.getElementById(\'' . $param_name . '\').value=this.value;'
					. 'jQuery(\'#' . $param_name . '_wrap img\').removeClass(\'selected\');'
					. 'jQuery(\'#' . $param_name . $i . '_img' . $key . '\').addClass(\'selected\');'
					. 'jQuery(\'#' . $param_name . '\').trigger(\'change\');" '
					. $checked . ' style="display:none;" />';
				$output  .= '<label for="' . $param_name . $i . '">' . $image . '</label>';
				$i ++;
			}
		}
		$output .= '</div>';

		return $output;
	}

	/**
	 * @param $settings
	 * @param $value
	 *
	 * @return string
	 */
	public function _dropdown_multiple_param( $param, $value ) {
		if ( ! is_array( $value ) ) {
			$param_value_arr = explode( ',', $value );
		} else {
			$param_value_arr = $value;
		}

		$param_line = '';
		$param_line .= '<select multiple name="' . esc_attr( $param['param_name'] ) . '" class="wpb_vc_param_value wpb-input wpb-select ' . esc_attr( $param['param_name'] ) . ' ' . esc_attr( $param['type'] ) . '">';
		foreach ( $param['value'] as $text_val => $val ) {
			if ( is_numeric( $text_val ) && ( is_string( $val ) || is_numeric( $val ) ) ) {
				$text_val = $val;
			}
			$selected = '';
			if ( ! empty( $param_value_arr ) && in_array( $val, $param_value_arr ) ) {
				$selected = ' selected="selected"';
			}
			$param_line .= '<option class="' . $val . '" value="' . $val . '"' . $selected . '>' . $text_val . '</option>';
		}
		$param_line .= '</select>';

		return $param_line;
	}

}

add_action( 'plugins_loaded', function () {
	PC_VC_Addon::instance();
}, 90 );
