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
 * Shows error messages rather than the critical errors screen.
 */
add_filter(
	'wp_php_error_args',
	function ( $message, $error ) {
		include 'views/wp-error.php';
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
	if ( ! wp_doing_ajax() ) {
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
	if ( ! wp_doing_ajax() ) {
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
	if ( ! wp_doing_ajax() ) {
		echo '<pre>';
	}
	adump( $data );
	if ( ! wp_doing_ajax() ) {
		echo '</pre>';
	}
	die();
}

/**
 * Shows all the enqueued scripts and styles in header, if set in url.
 */
if ( ! empty( $_GET['show_enqueued'] ) && $_GET['show_enqueued'] === 'true' ) {
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
 * Logger.
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

if ( ! function_exists( 'write_log' ) ) {
	function write_log( $log ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}
