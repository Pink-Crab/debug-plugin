<?php
/*
Plugin Name: PinkCrab Debugging Plugin
Plugin URI: https://www.PinkCrab.co.uk
Description: A selection of debugging tools. Should not really be used on production sites. Contains dump(), dd(), adump() & adie() plus custom error messages over WSOD
Author: PinkCrab
Version: 1.0.0
Author URI: https://www.PinkCrab.co.uk
*/


require_once 'vendor/autoload.php';

/**
 * Checks if a request is likely from Rest API.
 *
 * @return boolean
 */
function pinkcrab_is_rest() {
	return defined( 'REST_API_VERSION' ) && strpos( $_SERVER['REQUEST_URI'], '/wp-json/' ) !== false;
}

/**
 * Shows a custom error message in place of the WSOD.
 *
 * Will show a styled view of the error when accessed via the browser.
 * Will show a simple error message when accessed via AJAX or Rest.
 *
 * @param string $message The error message.
 * @param array $error The error array.
 */
add_filter(
	'wp_php_error_args',
	function ( $message, $error ) {
		if ( wp_doing_ajax() || pinkcrab_is_rest() ) {
			include 'views/ajax-error.php';
		} else {
			include 'views/wp-error.php';
		}
	},
	2,
	10
);

/**
 * Allows for the same level of dumping as dump()
 * but using print_r
 * Also shows NULL or TRUE/FALSE as strings not blank or 1.
 *
 * @param mixed ...$data
 * @return void
 */
function adump( ...$data ) {
	if ( ! wp_doing_ajax() && ! pinkcrab_is_rest() ) {
		echo '<pre>';
	}foreach ( $data as $item ) {
		if ( is_null( $item ) ) {
			print( 'NULL' );
		} elseif ( is_bool( $item ) && $item === false ) {
			print( 'FALSE' );
		} elseif ( is_bool( $item ) && $item === true ) {
			print( 'TRUE' );
		} else {
			print_r( $item );
		}
	}
	if ( ! wp_doing_ajax() && ! pinkcrab_is_rest() ) {
		echo '</pre>';
	}
}

/**
 * Ajax friendly version of dd().
 * Users adump to show the output and then dies.
 *
 * @param mixed ...$data
 * @return void
 */
function adie( ...$data ) {
	if ( ! wp_doing_ajax() && ! pinkcrab_is_rest() ) {
		echo '<pre>';
	}
	adump( $data );
	if ( ! wp_doing_ajax() && ! pinkcrab_is_rest() ) {
		echo '</pre>';
	}
	die();
}

/**
 * Shows all the enqueued scripts and styles in header, if set in url.
 * ?show_enqueued
 */
if ( ! empty( $_GET['show_enqueued'] ) ) {
	add_action(
		'wp_head',
		function () {
			// Print all loaded Scripts
			global $wp_scripts;
			global $wp_styles;

			dump(
				array(
					'DEBUG'   => 'Showing enqueued scripts/styles (url.com?show_enqueued=true)',
					'styles'  => array_map(
						function ( $e ) use ( $wp_styles ) {
							return $wp_styles->registered[ $e ];
						},
						$wp_styles->queue
					),
					'scripts' => array_map(
						function ( $e ) use ( $wp_scripts ) {
							return $wp_scripts->registered[ $e ];
						},
						$wp_scripts->queue
					),
				)
			);
		}
	);
}

/**
 * So all of defined hooks if in url.
 * ?show_hooks=hook,hook2
 */
if ( ! empty( $_GET['show_hooks'] ) ) {
	add_action(
		'wp_head',
		function () {
			$hooks = explode( ',', $_GET['show_hooks'] );
			foreach ( $hooks as $hook ) {
				dump(
					array(
						'hook'      => $hook,
						'callbacks' => $GLOBALS['wp_filter'][ $hook ],
					)
				);
			}
		}
	);
}

/**
 * Custom Logger.
 * 
 * Saves to wp-content/pc_debug.log
 *
 * @param mixed ...$data
 *
 * @return void
 */
function pclog( $data, string $type = 'log' ) {
	$log_file = ABSPATH . 'wp-content/pc_debug.log';
	// If the custom log file is not set, set it.
	if ( ! file_exists( $log_file ) ) {
		// Create the log file.
		file_put_contents( $log_file, '' );
	}

	// Get the current log file.
	$log = file_get_contents( $log_file );

	// Add the new data to the log.
	$entry = sprintf(
		'[%s] %s: %s' . PHP_EOL,
		date( 'Y-m-d H:i:s' ),
		$type,
		print_r( $data, true )
	);

	// Add entry to the start of the log.
	file_put_contents( $log_file, $entry . $log );
}

/**
 * Write to the PHP error log.
 *
 * @param mixed $log
 *
 * @return void
 */
if ( ! function_exists( 'write_log' ) ) {
	function write_log( $log ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}
