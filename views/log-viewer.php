<?php
/**
 * Template for the log viewer.
 *
 * @param array<string> $log Log data.
 */
?>
<style>
#details {
	display: flex;
	align-items: center;
	justify-content: space-between;
	background-color: #fba2b0;
	padding: 10px 20px;
	font-size: large;
	font-weight: 600;
	color: black;
}

#details p {
	margin: 0;
}

#entries {
	border: 4px solid #fba2b0;
	border-top-width: 0;
	padding: 5px;
}

#entries p {
	margin: 0;
	padding-bottom: 5px;
	background-color: #fdf2ed;
	font-family: monospace;
    white-space: pre;
}

#entries p:hover {
	background-color: #fba2b042;
}
</style>
<div class="wrap pclog">

	<div id="details">
		<h1>PC Debug Log</h1>
		<p>Log entries: <span><?php echo count( $log ); ?><span></p>
	</div>
	<div id="entries">
		<?php foreach ( $log as $entry ) : ?>
		<p><?php echo esc_html( $entry ); ?></p>
		<?php endforeach; ?>
	</div>
	<div>
		<a href="<?php echo admin_url( 'admin.php?page=pc_debug_log' ); ?>">Refresh</a>
	</div>
</div>