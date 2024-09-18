<?php 
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
?>

************************************************************ <?php echo "\n"; ?>
Response: <?php echo esc_html( $message['response'] ); ?> <?php echo "\n"; ?>
************************************************************ <?php echo "\n"; ?>
<?php echo esc_html( $subheader ); ?> <?php echo "\n"; ?>
************************************************************ <?php echo "\n"; ?>
Stack Trace: <?php echo "\n"; ?>
<?php foreach ( $stack_trace as $stack_trace_entry ) : ?> 
    --<?php echo esc_html( $stack_trace_entry ); ?> <?php echo "\n"; ?>
<?php endforeach; ?>
************************************************************<?php echo "\n"; ?>
File: <?php echo "{$error['file']} on line {$error['line']}"; ?><?php echo "\n"; ?>
************************************************************<?php echo "\n"; ?>
Full Backtrace:<?php echo "\n"; ?>
<?php adump( debug_backtrace() ); ?>
************************************************************<?php echo "\n"; ?>

