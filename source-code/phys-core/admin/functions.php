<?php

/**
 * Admin functions
 *
 * @package   Phys_Core
 * @since     0.1.0
 */

/**
 * Clean all keys which is a number, e.g: Array( [0] => ..., ..., [69] => ...);
 *
 * @since 0.4.0
 *
 * @param $theme_mods
 *
 * @return mixed
 */
if ( ! function_exists( 'phys_clean_theme_mods' ) ) {
	function phys_clean_theme_mods( $theme_mods ) {
		// Gets mods keys
		$mod_keys = array_keys( $theme_mods );
		foreach ( $mod_keys as $mod_key ) {
			// Removes from array if the key is a number
			if ( is_numeric( $mod_key ) ) {
				unset( $theme_mods[ $mod_key ] );
			}
		}

		return $theme_mods;
	}
}

if ( ! function_exists( '_phys_export_skip_object_meta' ) ) {
	function _phys_export_skip_object_meta( $return_me, $meta_key, $meta_value = false ) {
		if ( '_phys_demo_content' == $meta_key ) {
			$return_me = true;
		}

		return $return_me;
	}

	/**
	 * Skip export object's meta data if it's _phys_demo_content
	 */
	add_filter( 'wxr_export_skip_postmeta', '_phys_export_skip_object_meta', 1000, 2 );
	add_filter( 'wxr_export_skip_commentmeta', '_phys_export_skip_object_meta', 1000, 2 );
	add_filter( 'wxr_export_skip_termmeta', '_phys_export_skip_object_meta', 1000, 3 );
}


/**
 * Redirect to url.
 *
 * @since 0.8.9
 *
 * @param $url
 */
if ( ! function_exists( 'phys_core_redirect' ) ) {
	function phys_core_redirect( $url ) {
		if ( headers_sent() ) {
			echo "<meta http-equiv='refresh' content='0;URL=$url' />";
		} else {
			wp_redirect( $url );
		}

		exit();
	}
}

/**
 * Unserialize (avoid whitespace string).
 *
 * @since 1.0.0
 *
 * @param $string
 *
 * @return mixed
 */
if ( ! function_exists( 'phys_maybe_unserialize' ) ) {
	function phys_maybe_unserialize( $string ) {
		$value = maybe_unserialize( $string );

		if ( ! $value && strlen( $string ) ) {
			$string = trim( $string );
			$value  = maybe_unserialize( $string );
		}

		return $value;
	}
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 *
 * @since 1.1.1
 *
 * @param $limit integer
 */
function phys_core_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) ) {
		set_time_limit( $limit );
	}
}


/**
 * Get is child theme.
 *
 * @since 1.0.3
 *
 * @return bool
 */
function phys_core_is_child_theme() {
	$stylesheet = get_stylesheet();
	$template   = get_template();

	return ( $stylesheet != $template );
}

/**
 * Generate token.
 *
 * @since 1.2.1
 *
 * @return string
 */
function phys_core_generate_token() {
	$text  = bin2hex( openssl_random_pseudo_bytes( 16 ) );
	$token = md5( $text );

	return $token;
}

/**
 * Test request.
 *
 * @since 1.4.3
 *
 * @param $url
 *
 * @return array
 */
function phys_core_test_request( $url ) {
	$response         = wp_remote_get($url, ['timeout' => 60]);
	$successful       = true;
	$message_response = 'success';

	if ( is_wp_error( $response ) ) {
		$successful       = false;
		$message_response = $response->get_error_message();
	}

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( $status_code == 403 || $status_code >= 500 ) {
		$successful       = false;
		$message_response = wp_remote_retrieve_response_message( $response );
	}

	return array(
		'return'  => $successful,
		'message' => $message_response,
		'url'     => $url
	);
}

function phys_core_get_content_json_url( $json_url ) {
	$response = wp_remote_get($json_url, ['timeout' => 60]);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$body   = wp_remote_retrieve_body( $response );
	$object = json_decode( $body );
	$arr    = (array) $object;

	return ! empty( $arr ) ? $arr : false;
}
/**
 * Check import demo data page-builder
 */
add_action( 'wp_ajax_phys_update_chosen_builder', 'phys_core_page_builder' );
if ( ! function_exists( 'phys_core_page_builder' ) ) {
	function phys_core_page_builder() {
		$phys_key   = sanitize_text_field( $_POST["phys_key"] );
		$phys_value = sanitize_text_field( $_POST["phys_value"] );

		if ( ! is_multisite() ) {
			$active_plugins = get_option( 'active_plugins' );

			if ( $phys_value == 'visual_composer' ) {
				if ( $site_origin = array_search( 'siteorigin-panels/siteorigin-panels.php', $active_plugins ) ) {
					unset( $active_plugins[$site_origin] );
				}

				if ( $elementor = array_search( 'elementor/elementor.php', $active_plugins ) ) {
					unset( $active_plugins[$elementor] );
				}

				if ( ! in_array( 'js_composer/js_composer.php', $active_plugins ) ) {
					$active_plugins[] = 'js_composer/js_composer.php';
				}
			} else {
				if ( $phys_value == 'site_origin' ) {
					if ( $visual_composer = array_search( 'js_composer/js_composer.php', $active_plugins ) ) {
						unset( $active_plugins[$visual_composer] );
					}

					if ( $elementor = array_search( 'elementor/elementor.php', $active_plugins ) ) {
						unset( $active_plugins[$elementor] );
					}

					if ( ! in_array( 'siteorigin-panels/siteorigin-panels.php', $active_plugins ) ) {
						$active_plugins[] = 'siteorigin-panels/siteorigin-panels.php';
					}
				} else {
					if ( $phys_value == 'elementor' ) {
						if ( $visual_composer = array_search( 'js_composer/js_composer.php', $active_plugins ) ) {
							unset( $active_plugins[$visual_composer] );
						}

						if ( $site_origin = array_search( 'siteorigin-panels/siteorigin-panels.php', $active_plugins ) ) {
							unset( $active_plugins[$site_origin] );
						}

						if ( ! in_array( 'elementor/elementor.php', $active_plugins ) ) {
							$active_plugins[] = 'elementor/elementor.php';
						}
					}
				}
			}

			update_option( 'active_plugins', $active_plugins );
		}

		if ( empty( $phys_key ) || empty( $phys_value ) ) {
			$output = 'update fail';
		} else {
			set_theme_mod( $phys_key, $phys_value );
			$output = 'update success';
		}

		echo ent2ncr( $output );
		die();
	}
}

/**
 * Do other tasks before import demo data
 */
add_action( 'phys_core_importer_start_import_demo', 'phys_core_before_start_import_demo', 10, 1 );
if ( ! function_exists( 'phys_core_before_start_import_demo' ) ) {
	function phys_core_before_start_import_demo( $demo ) {
		if ( isset( $demo['child_theme_required'] ) ) {
			$child_themes = Phys_Child_Themes::child_themes();
			foreach ( $child_themes as $theme ) {
				$theme_slug   = $theme->get( 'slug' );
				$theme_status = $theme->get_status();
				if ( $demo['child_theme_required'] == $theme_slug ) {
					if ( $theme_status == 'not_installed' ) {
						$result_install = $theme->install();
					}
					$result_activate = $theme->activate();
					break;
				}
			}
		}
	}
}
// add function when import template
add_filter('thim-el-kit/create-template/product-registration',function(){
	return array(
		'class'  => '\Phys_Product_Registration'
	);
});
