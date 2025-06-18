<?php

/**
 * Class Phys_Envato_Service.
 *
 * @since 0.7.0
 */
class Phys_Envato_API {


	/**
	 * Get url file theme (zip file).
	 *
	 * @since 0.7.0
	 *
	 * @param $item_id
	 * @param $refresh_token
	 *
	 * @return WP_Error|string
	 */
	public static function get_url_download_item( $item_id ) {
		$personal_token = Phys_Product_Registration::get_data_theme_register('personal_token');
 		if ( $personal_token ) {
			$url_api = 'https://api.envato.com/v3/market/buyer/download?item_id=' . $item_id . '&shorten_url=true';

			$response = self::request( $url_api, $personal_token );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$detect_error = ! empty( $response['error'] ) ? true : false;

			if ( $detect_error ) {
				return new WP_Error( 404, __( 'Some thing went wrong!', 'phys-core' ) );
			}

			$url = ! empty( $response['wordpress_theme'] ) ? $response['wordpress_theme'] : false;

			return apply_filters( 'phys_core_envato_api_get_url_download_item', $url, $item_id );
		}
	}

	public static function list_all_purchase_codes( $personal_token ) {
		$url_api = 'https://api.envato.com/v3/market/buyer/list-purchases?filter_by=wordpress-themes';

		$response = self::request( $url_api, $personal_token );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$detect_error = ! empty( $response['error'] ) ? true : false;

		if ( $detect_error ) {
			return array();
		}

		$purchase_codes = ! empty( $response['results'] ) ? array_column( $response['results'], 'code' ) : array();

		return apply_filters( 'phys_core_envato_api_list_all_purchase_code', $purchase_codes );
	}

	/**
	 * Get theme metadata.
	 *
	 * @since 0.7.0
	 *
	 * @param $id
	 * @param $token
	 * @param $refresh_token
	 *
	 * @return array|WP_Error
	 */
	public static function get_theme_metadata( $id, $token) {
		$url_api = 'https://api.envato.com/v3/market/catalog/item?id=' . $id;

		$response = self::request( $url_api, $token );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$detect_error = ! empty( $response['error'] ) ? true : false;
		if ( $detect_error ) {
			return new WP_Error( 404, __( 'Some thing went wrong!', 'phys-core' ) );
		}

		$metadata                 = ! empty( $response['wordpress_theme_metadata'] ) ? $response['wordpress_theme_metadata'] : array();
		$metadata['icon']         = ! empty( $response['previews']['icon_with_landscape_preview']['icon_url'] ) ? $response['previews']['icon_with_landscape_preview']['icon_url'] : false;
		$metadata['author_url']   = ! empty( $response['author_url'] ) ? $response['author_url'] : false;
		$metadata['rating']       = ! empty( $response['rating'] ) ? $response['rating'] : false;
		$metadata['rating_count'] = ! empty( $response['rating_count'] ) ? $response['rating_count'] : false;
		$metadata['url']          = ! empty( $response['url'] ) ? $response['url'] : false;

		$data = wp_parse_args( $metadata, array(
			'theme_name'  => '',
			'author_name' => '',
			'version'     => false,
			'description' => ''
		) );

		return apply_filters( 'phys_core_envato_api_get_theme_metadata', $data, $id );
	}

	/**
	 * Request to Envato API.
	 *
	 * @since 0.7.0
	 *
	 * @param $url
	 * @param $token
	 * @param $refresh_token
	 *
	 * @return array|WP_Error
	 */
	private static function request( $url, $token, $refresh_token = '' ) {
		if ( empty( $token ) ) {
			return new WP_Error( 'api_token_error', __( 'An API token is required.', 'phys-core' ) );
		}

//		if ( ! empty( $refresh_token ) ) {
//			$token = self::get_access_token( $refresh_token );
//		}

		$args = array(
			'headers'   => array(
				'Authorization' => "Bearer $token",
				'User-Agent'    => sprintf( 'WordPress - Phys Core %s', PHYS_CORE_VERSION ),
			),
			'timeout'   => 30,
		);

		$response      = wp_remote_get( esc_url_raw( $url ), $args );
		$response_code = wp_remote_retrieve_response_code( $response );
		$return        = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $response_code ) {
			if ( null === $return || empty( $return['error_description'] ) ) {
				return new WP_Error( 'api_error', __( 'An unknown API error occurred.', 'phys-core' ) );
			}

			return new WP_Error( $response_code, $return['error_description'] );
		}

		return $return;
	}
}
