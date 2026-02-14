<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<h2>Real-time AI1WM Logging</h2>

<?php if ( ! ai1wm_debug_is_ai1wm_active() ) : ?>
	<div class="notice notice-warning inline">
		<p>All-in-One WP Migration is not active. Real-time logging requires AI1WM to be installed and activated.</p>
	</div>
<?php else : ?>
	<?php
		$enabled   = Ai1wm_Debug_Logger::is_enabled();
		$verbosity = Ai1wm_Debug_Logger::get_verbosity();
	?>

	<table class="form-table">
		<tr>
			<th scope="row">Logger Status</th>
			<td>
				<label>
					<input type="checkbox" id="ai1wm-debug-logger-toggle" <?php checked( $enabled ); ?> />
					Enable real-time logging of AI1WM operations
				</label>
			</td>
		</tr>
		<tr>
			<th scope="row">Verbosity Level</th>
			<td>
				<select id="ai1wm-debug-logger-verbosity">
					<option value="<?php echo AI1WM_DEBUG_VERBOSITY_ERROR; ?>" <?php selected( $verbosity, AI1WM_DEBUG_VERBOSITY_ERROR ); ?>>Error</option>
					<option value="<?php echo AI1WM_DEBUG_VERBOSITY_WARNING; ?>" <?php selected( $verbosity, AI1WM_DEBUG_VERBOSITY_WARNING ); ?>>Warning</option>
					<option value="<?php echo AI1WM_DEBUG_VERBOSITY_INFO; ?>" <?php selected( $verbosity, AI1WM_DEBUG_VERBOSITY_INFO ); ?>>Info</option>
					<option value="<?php echo AI1WM_DEBUG_VERBOSITY_DEBUG; ?>" <?php selected( $verbosity, AI1WM_DEBUG_VERBOSITY_DEBUG ); ?>>Debug</option>
				</select>
			</td>
		</tr>
	</table>

	<p>
		<button type="button" class="button" id="ai1wm-debug-realtime-save">Save Settings</button>
	</p>

	<h3>Live Log</h3>
	<div id="ai1wm-debug-realtime-controls">
		<label>
			<input type="checkbox" id="ai1wm-debug-realtime-autoscroll" checked />
			Auto-scroll
		</label>
		<button type="button" class="button" id="ai1wm-debug-realtime-clear">Clear Display</button>
	</div>
	<pre id="ai1wm-debug-realtime-log" class="ai1wm-debug-log-viewer"></pre>
<?php endif; ?>
