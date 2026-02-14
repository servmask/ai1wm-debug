<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<?php $log_files = Ai1wm_Debug_Logs::get_log_files(); ?>

<h2>Log Viewer</h2>

<?php if ( empty( $log_files ) ) : ?>
	<div class="notice notice-info inline">
		<p>No log files found. Enable <code>WP_DEBUG_LOG</code> in <code>wp-config.php</code> to start capturing logs.</p>
	</div>
<?php else : ?>
	<p>
		<label for="ai1wm-debug-log-select"><strong>Select Log File:</strong></label>
		<select id="ai1wm-debug-log-select">
			<option value="">-- Select --</option>
			<?php foreach ( $log_files as $file ) : ?>
				<option value="<?php echo esc_attr( $file['key'] ); ?>">
					<?php echo esc_html( $file['label'] . ' (' . $file['size'] . ')' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<button type="button" class="button" id="ai1wm-debug-log-load" disabled>Load</button>
	</p>
	<p id="ai1wm-debug-log-info" style="display:none;">
		<button type="button" class="button" id="ai1wm-debug-log-older" style="display:none;">Older</button>
		<button type="button" class="button" id="ai1wm-debug-log-newer" style="display:none;">Newer</button>
		&nbsp;
		Showing lines <span id="ai1wm-debug-log-offset">0</span>-<span id="ai1wm-debug-log-end">0</span>
		of <span id="ai1wm-debug-log-total">0</span>
	</p>
	<pre id="ai1wm-debug-log-content" class="ai1wm-debug-log-viewer" style="display:none;"></pre>
<?php endif; ?>
