<?php
/*
Plugin Name: PinkCrab Debugging Plugin
Plugin URI: https://www.PinkCrab.co.uk
Description: A selection of debugging tools. Should not really be used on production sites. Contains dump(), dd(), adump() & adie() plus custom error messages over WSOD
Author: PinkCrab
Version: 1.1.0
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
$r = add_filter(
	'wp_php_error_args',
	function ( $message, $error ) {
		if ( wp_doing_ajax() || pinkcrab_is_rest() ) {
			include 'views/ajax-error.php';
		} else {
			include 'views/web-error.php';
		}
	},
	2,
	10
);

/**
 * Adds an admin page to view the log under tools.
 *
 * @return void
 */
add_action(
	'admin_menu',
	function () {
		add_submenu_page(
			'tools.php',
			'PC Debug Log',
			'PC Debug Log',
			'manage_options',
			'pc_debug_log',
			function () {
				$log_file = ABSPATH . 'wp-content/pc_debug.log';
				$log      = explode( "\x1F", file_get_contents( $log_file ) );

				require 'views/log-viewer.php';
			}
		);
	}
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
 * ?pc_show_enqueued
 */
if ( ! empty( $_GET['pc_show_enqueued'] ) ) {
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
 * ?pc_show_hooks=hook,hook2
 */
if ( ! empty( $_GET['pc_show_hooks'] ) ) {
	add_action(
		'wp_head',
		function () {
			$hooks = explode( ',', $_GET['pc_show_hooks'] );
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
 * @param mixed  $data Data to log.
 * @param string $type The type of log.
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

	$delimiter = "\x1F";

	// Add the new data to the log.
	$entry = sprintf(
		'[%s] %s: %s' . $delimiter,
		gmdate( 'Y-m-d H:i:s' ),
		$type,
		print_r( $data, true )
	);

	// Add entry to the start of the log.
	file_put_contents( $log_file, $entry . $log );
}


if ( ! function_exists( 'write_log' ) ) {
	/**
	 * Write to the PHP error log.
	 *
	 * @param mixed $log
	 *
	 * @return void
	 */
	function write_log( $log ) {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
}

// create wp cli command to test error message.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_hook(
		'before_wp_load',
		/**
		 * The custom error handler for WP CLI.
		 *
		 * @return void
		 */
		function (): void {
			// Handle duplicate error messages.
			static $errors = array();

			/**
			 * Gets the terminal width.
			 *
			 * @return integer
			 */
			function pc_get_terminal_width(): int {
				$width = exec( 'tput cols' ); // Fetch terminal width
				return is_numeric( $width ) ? (int) $width : 80; // Default to 80 if unavailable
			}

			/**
			 * Formats a string to be centered in the terminal.
			 *
			 * @param string $text The text to center.
			 *
			 * @return string
			 */
			function pc_center_text_with_equals( string $text, $border = '==' ): string {
				// Fetch the terminal width dynamically
				$terminal_width = exec( 'tput cols' );
				if ( ! is_numeric( $terminal_width ) ) {
					$terminal_width = 80; // Default to 80 if terminal width cannot be determined
				}

				$terminal_width = (int) $terminal_width;

				// Add space for "==  ==" on either side
				$padding_width   = 4; // '==  ' and '  ==' add 4 characters
				$text_width      = strlen( $text );
				$available_space = $terminal_width - $text_width - $padding_width;

				if ( $available_space < 0 ) {
					// If text is too wide, truncate it and adjust
					$text            = substr( $text, 0, $terminal_width - $padding_width - 3 ) . '...';
					$text_width      = strlen( $text );
					$available_space = $terminal_width - $text_width - $padding_width;
				}

				// Calculate padding on both sides
				$left_padding  = str_repeat( ' ', floor( $available_space / 2 ) );
				$right_padding = str_repeat( ' ', ceil( $available_space / 2 ) );

				// Return the centered line
				return "{$border}{$left_padding}{$text}{$right_padding}{$border}";
			}

			set_error_handler(
				/**
				 * The custom error handler for WP CLI.
				 *
				 * @param integer $errno The error number.
				 * @param string $errstr The error message.
				 * @param string $errfile The file the error occurred in.
				 * @param integer $errline The line the error occurred on.
				 *
				 * @return void
				 */
				function ( $errno, $errstr, $errfile, $errline ) use ( &$errors ) {

					// Get the error code.

					// If we have no messages, show a header in pink.
					if ( empty( $errors ) ) {
						WP_CLI::line( str_repeat( '=', pc_get_terminal_width() ) );
						WP_CLI::line( pc_center_text_with_equals( 'WP CLI ERROR' ) );
						WP_CLI::line( str_repeat( '=', pc_get_terminal_width() ) );
					}

					$message = "Error [$errno]: $errstr in $errfile on line $errline";

					// If the error has not been displayed, show it.
					if ( ! in_array( $message, $errors, true ) ) {
						$errors[] = $message;
						// Hide the link to the WP debugging page.
						$errstr = str_replace( 'Please see <a href="https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/">Debugging in WordPress</a> for more information.', '', $errstr );
						// Replace common HTML in error messages with terminal colors.
						$replacements = array(
							'<strong>'  => "\033[1;37m",
							'</strong>' => "\033[34m",
							'<code>'    => "\033[33m",
							'</code>'   => "\033[34m",
						);
						$errstr       = strip_tags( str_replace( array_keys( $replacements ), array_values( $replacements ), $errstr ) );

						WP_CLI::line( WP_CLI::colorize( "%WError [$errno]:%B$errstr in $errfile on line $errline%N" ) );
						WP_CLI::line( str_repeat( '=', pc_get_terminal_width() ) );
					}
				}
			);
		}
	);
}
