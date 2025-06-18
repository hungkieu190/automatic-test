<?php
/**
 * Class Phys_Logger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uploadir = wp_upload_dir();
define( 'PHYS_LOG_PATH', $uploadir['basedir'] . '/phys-logger/' );

if ( ! class_exists( 'Phys_Logger' ) ) {
	class Phys_Logger {

		/**
		 * @var array Stores open file _handles.
		 * @access private
		 */
		private $_handles;

		/**
		 * @var null
		 */
		private static $_instance = null;

		/**
		 * @var array
		 */
		private static $_time = array();

		/**
		 * @var string
		 */
		private static $_debug_mode = 'yes';

		/**
		 * Maximum size of file in bytes a file can store. Set to 0 (zero) to unlimited
		 *
		 * @var int
		 */
		private static $_max_log_size = 1048576; // 1M

		/**
		 * Constructor for the logger.
		 */
		public function __construct() {
			self::$_debug_mode = PC::is_debug() ? 'yes' : 'no';
			$this->_handles    = array();
		}


		/**
		 * Destructor.
		 */
		public function __destruct() {
			foreach ( $this->_handles as $handle ) {
				@fclose( $handle );
			}
		}


		/**
		 * Open log file for writing.
		 *
		 * @access private
		 *
		 * @param mixed $handle
		 *
		 * @return bool success
		 */
		private function open( $handle ) {
			if ( isset( $this->_handles[ $handle ] ) ) {
				return true;
			}

			$path = self::_get_log_file( $handle );
			$f    = @fopen( $path, 'a' );

			// if path is not exists, creates path and try again!
			if ( ! $f ) {
				self::_create_log_files();
				$f = @fopen( $path, 'a' );
			}

			if ( $f ) {
				if ( self::$_max_log_size > 0 && filesize( $path ) >= self::$_max_log_size ) {
					ftruncate( $f, 0 );
				}
				$this->_handles[ $handle ] = $f;

				return true;
			}

			return false;
		}

		/**
		 * Add a log entry to chosen file.
		 *
		 * @param string $handle
		 * @param string $message
		 * @param bool $clear
		 */
		public function add( $message, $handle = 'log', $clear = false ) {
			if ( ! $handle ) {
				$handle = 'log';
			}
			if ( self::$_debug_mode == 'yes' && $this->open( $handle ) && is_resource( $this->_handles[ $handle ] ) ) {
				if ( $clear ) {
					$this->clear( $handle );
				}
				$time = date_i18n( 'm-d-Y @ H:i:s -' );
				if ( ! is_string( $message ) ) {
					ob_start();
					print_r( $message );
					$message = ob_get_clean();
				}
				fwrite( $this->_handles[ $handle ], "-----" . $time . "-----\n" . $message . "\n" );
			}
			do_action( 'phys_logger_add', $handle, $message );
		}


		/**
		 * Clear entries from chosen file.
		 *
		 * @param mixed $handle
		 */
		public function clear( $handle ) {
			if ( $this->open( $handle ) && is_resource( $this->_handles[ $handle ] ) ) {
				@ftruncate( $this->_handles[ $handle ], 0 );
			}

			do_action( 'phys_logger_clear', $handle );
		}

		/**
		 * @return Phys_Logger|null
		 */
		public static function instance() {
			if ( ! self::$_instance ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Prints what you need to browser
		 */
		public static function debug() {

			if ( self::$_debug_mode != 'yes' ) {
				return;
			}

			if ( $args = func_get_args() ) {
				echo '<pre>';
				foreach ( $args as $arg ) {
					print_r( $arg );
				}
				echo '</pre>';
			}
		}

		public static function exception( $message ) {
			if ( self::$_debug_mode != 'yes' ) {
				return;
			}
			throw new Exception( $message );
		}

		public static function timeStart( $name ) {
			if ( self::$_debug_mode != 'yes' ) {
				return false;
			}
			self::$_time[ $name ] = microtime();
		}

		public static function timeEnd( $name ) {
			if ( self::$_debug_mode != 'yes' || empty( self::$_time[ $name ] ) ) {
				return false;
			}
			$time = microtime() - self::$_time[ $name ];
			echo "{$name} execution time = " . $time;
			unset( self::$_time[ $name ] );
		}

		/**
		 * Creates log files
		 */
		private function _create_log_files() {
			$files = array(
				array(
					'base'    => PHYS_LOG_PATH,
					'file'    => '.htaccess',
					'content' => 'deny from all'
				),
				array(
					'base'    => PHYS_LOG_PATH,
					'file'    => 'index.html',
					'content' => ''
				)
			);
			foreach ( $files as $file ) {
				if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
					if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
						fwrite( $file_handle, $file['content'] );
						fclose( $file_handle );
					}
				}
			}
		}

		/**
		 * Get path of log file by handle
		 *
		 * @param $handle
		 *
		 * @return string
		 */
		private function _get_log_file( $handle ) {
			return trailingslashit( PHYS_LOG_PATH ) . $handle . '-' . sanitize_file_name( md5( $handle ) ) . '.log';
		}
	}
}


function phys_log_query( $query ) {
	if ( ! did_action( 'init' ) ) {
		return $query;
	}
	/*if ( empty( $GLOBALS['phys_queries'] ) ) {
		$GLOBALS['phys_queries']       = array();
		$GLOBALS['phys_total_queries'] = 0;
	}
	$debug_backtrace = debug_backtrace();

	$q = preg_replace( '/(\r\n|\r|\n|\s)+/', ' ', $query );
	$k = md5( $q );
	if ( empty( $GLOBALS['phys_queries'][$k] ) ) {
		$GLOBALS['phys_queries'][$k] = array(
			$q,
			1,
			'file' => $debug_backtrace[4]['file'],
			'line' => $debug_backtrace[4]['line'],
			'func' => $debug_backtrace[5]['function']
		);
	} else {
		$GLOBALS['phys_queries'][$k][1] ++;
	}

	$GLOBALS['phys_total_queries'] += 1;

	$log = array(
		$q,
		1,
		'file' => $debug_backtrace[4]['file'],
		'line' => $debug_backtrace[4]['line'],
		'func' => $debug_backtrace[5]['function']
	);*/
	phys_add_log( $query, 'query' );

	//echo $query;die();
	return $query;
}

add_filter( 'query', 'phys_log_query' );

function phys_save_queries() {

	$queries = $GLOBALS['phys_queries'];
	usort( $queries, create_function( '$a, $b', 'return $a[0] < $b[0];' ) );
	phys_add_log( $queries, 'query', true );
	phys_add_log( "Total of queries " . $GLOBALS['phys_total_queries'], 'query' );
}

//add_action( 'wp_footer', 'phys_save_queries', 9999999 );
//add_action( 'admin_footer', 'phys_save_queries', 9999999 );
//add_action( 'wp_ajax_phys_importer', 'phys_save_queries', 9999999 );

