<?php

/**
 * Class Phys_Modal.
 *
 * @since 0.9.0
 */
class Phys_Modal extends Phys_Singleton {
	/**
	 * Phys_Modal constructor.
	 *
	 * @since 0.9.0
	 */
	protected function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 *
	 * @since 0.9.0
	 */
	private function init_hooks() {
		add_action( 'admin_footer', array( $this, 'add_iframe_template' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_footer', array( $this, 'register_modal_place' ) );
	}

	/**
	 * Register modal place.
	 *
	 * @since 1.3.4
	 */
	public function register_modal_place() {
		?>
        <div class="tc-modals-wrapper">
			<?php
			do_action( 'phys_core_list_modals' );
			?>
        </div>
		<?php
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 0.9.1
	 */
	public function enqueue_scripts() {
		wp_register_script( 'phys-modal', PHYS_CORE_ADMIN_URI . '/assets/js/modals/modal.js', array( 'jquery', 'backbone' ), PHYS_CORE_VERSION );
		wp_register_script( 'phys-modal-v2', PHYS_CORE_ADMIN_URI . '/assets/js/modal-v2.js', array( 'jquery', 'backbone' ), PHYS_CORE_VERSION );
	}

	/**
	 * Add iframe template.
	 *
	 * @since 0.9.0
	 */
	public function add_iframe_template() {
		Phys_Template_Helper::template( 'modals/iframe.php', array(), true );
	}

	/**
	 * Add modal.
	 *
	 * @since 0.9.0
	 *
	 * @param array $args
	 *
	 * @return bool
	 */
	public static function render_modal( $args ) {
		$args = wp_parse_args( $args, array(
			'template' => '',
			'id'       => '',
		) );

		$html = Phys_Template_Helper::template( $args['template'], $args );
		if ( ! $html ) {
			return false;
		}
		$args['html'] = $html;

		return Phys_Template_Helper::template( 'modals/master.php', $args, true );
	}

	/**
	 * Enqueue script phys modal. You need call this function if you want to add modal.
	 *
	 * @since 0.9.0
	 */
	public static function enqueue_modal() {
		wp_enqueue_script( 'phys-modal' );
	}
}
