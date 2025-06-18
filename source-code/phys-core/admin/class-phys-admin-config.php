<?php

/**
 * Class Phys_Admin_Config
 *
 * @since 1.1.0
 */
class Phys_Admin_Config extends Phys_Singleton {
	/**
	 * @since 1.1.0
	 *
	 * @var null
	 */
	private static $configs = null;

	/**
	 * Phys_Admin_Config constructor.
	 */
	protected function __construct() {
		$this->set_config();
	}

	/**
	 * Set configs.
	 *
	 * @since 1.1.0
	 */
	private function set_config() {
		self::$configs = array(
			'api_check_self_update' => 'https://physcodewp.github.io/demo-data/update-check.json',
			'api_update_plugins'    => 'https://updates.physcode.com/wp-json/thim_em/v1/plugins',
			'api_phys_market'       => 'https://updates.physcode.com/wp-json/phys-market/v1',
 			'personal_token'        => 'R0T4ZOoWPbKpp3nBrL094tE85b2SQ8UX',
			'host_envato_app'       => 'https://updates.physcode.com/thim-envato-market',
			'host_downloads'        => 'https://updates.physcode.com/thim-envato-market',
			'welcome_panel_remote'  => 'https://thimpresswp.github.io/thim-core/newsfeed.json',
			'demo_data'             => 'https://physcodewp.github.io/demo-data/',
			'host_downloads_api'    => 'https://updates.physcode.com/',
		);
	}

	/**
	 * Get config by key.
	 *
	 * @since 1.1.0
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public static function get( $key, $default = null ) {
		if ( ! isset( self::$configs[ $key ] ) ) {
			return $default;
		}

		return apply_filters( "phys_core_ac_$key", self::$configs[ $key ] );
	}
}
