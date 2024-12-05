<?php
/**
 * The template used to display wp-error opposed to WSOD!
 */
// Get all needed values from messages.
$stack_trace = explode( "\n", $error['message'] );
// Extract main error message
$subheader = array_shift( $stack_trace );
// Remove stack trace title from remainder.
$stack_trace = array_filter(
	$stack_trace,
	function( $message ) {
		return $message !== 'Stack trace:';
	}
);
// Remove root from paths to cut down on size.
// $stack_trace = array_map(
// 	function( $message ) {
// 		return str_replace( get_home_path(), '../', $message );
// 	},
// 	$stack_trace
// );
?>
<style>
#pinkcrab-error {
	background-color: #fba2b0;
	padding: 0 10px;
}

.header {
	display: flex;
	justify-content: space-between;
	padding: 10px 0px;
	font-size: 26px;
	color: cornsilk;
}

.sub-header {
	background-color: #fdf2ed;
	padding: 10px;
	line-height: 1.6;
	color: black;
}

.block__title {
	margin: 0 !important;
	padding-top: 20px;
	font-size: 26px !important;
	color: cornsilk;
}

.stack-trace__entry,
.file__details {
	margin: 0 !important;
	font-family: monospace;
	color: black;
	background-color: #fdf2ed;
	padding: 10px;
}

.full-debug {
	padding-bottom: 5px;
}
</style>

<div id="pinkcrab-error">
	<div class="header block">
		<span>PinkCrab Error</span>
		<span><?php echo esc_html( $message['response'] ); ?></span>
	</div>
	<div class="sub-header block">
		<?php echo esc_html( $subheader ); ?>
	</div>
	<div class="trace block">
		<p class="block__title">Stack Trace</p>
		<?php foreach ( $stack_trace as $stack_trace_entry ) : ?>
			<p class="stack-trace__entry"><?php echo esc_html( $stack_trace_entry ); ?></p>
		<?php endforeach; ?>
	</div>
	<div class="file block">
		<p class="block__title">File</p>
		<p class="file__details"><?php echo "{$error['file']} on line {$error['line']}"; ?></p>
	</div>
	<div class="full-debug">
		<p class="block__title">Full Backtrace</p>
		<?php dump( debug_backtrace() ); ?>
	</div>
</div>
