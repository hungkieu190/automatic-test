<?php

/**
 * Class Phys_Error
 *
 * @package   Phys_Core_Admin
 * @since     0.3.1
 */
class Phys_Error extends Exception {
	/**
	 * @since 0.3.1
	 *
	 * @var string
	 */
	private $how_to = '';

	/**
	 * List error code.
	 *
	 * @since 0.3.1
	 *
	 * @var array
	 */
	public static $errors_code = array(
		'#000_UNKNOWN_ERROR',
		'#001_REQUEST_ERROR',
		'#002_SERVER_ERROR',
		'#003_STEP_NOT_FOUND',
		'#004_BAD_REQUEST',
		'#005_FILE_NOT_FOUND',
		'#006_PERMISSION_DENIED',
		'#007_FILE_BROKEN',
		'#008_DOWNLOAD_FAILED',
		'#009_INSTALL_PLUGIN',
		'#010_PARAM_VALID'
	);

	/**
	 * Set text how to.
	 *
	 * @since 0.3.1
	 *
	 * @param $how_to
	 */
	public function setHowTo( $how_to ) {
		$this->how_to = $how_to;
	}

	/**
	 * Get text how to fixes error.
	 *
	 * @since 0.3.1
	 *
	 * @return string
	 */
	public function getHowTo() {
		return $this->how_to;
	}

	/**
	 * Get error code.
	 *
	 * @since 0.3.1
	 *
	 * @return string
	 */
	public function getErrorCode() {
		$error_code_index = $this->getCode();

		if ( $error_code_index >= count( self::$errors_code ) ) {
			return self::$errors_code[0];
		}

		return self::$errors_code[ $error_code_index ];
	}

	/**
	 * Create Phys_Error.
	 *
	 * @since 0.3.1
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $how_to
	 *
	 * @return Phys_Error
	 */
	public static function create( $message, $code = 0, $how_to = '' ) {
		$exception = new self( $message, $code );
		$exception->setHowTo( $how_to );

		return $exception;
	}
}
