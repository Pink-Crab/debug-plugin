<?php
/**
 * The template used to display a fatal error when running under WP CLI.
 */
$stack_trace = explode( "\n", $error['message'] );
// Extract main error message
$subheader = array_shift( $stack_trace );
?>
************************************************************ <?php echo "\n"; ?>
CLI ERROR: <?php echo ( $message['response'] ); ?> <?php echo "\n"; ?>
************************************************************ <?php echo "\n"; ?>
<?php echo ( $subheader ); ?> <?php echo "\n"; ?>
************************************************************<?php echo "\n"; ?>
File: <?php echo "{$error['file']} on line {$error['line']}"; ?><?php echo "\n"; ?>
************************************************************<?php echo "\n"; ?>
