<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<h2>Audit Log</h2>

<?php if ( ! current_user_can( 'manage_options' ) ) : ?>
	<div class="notice notice-warning inline">
		<p>Only administrators can view the audit log.</p>
	</div>
<?php else : ?>
	<?php $session_tokens = Ai1wm_Debug_Audit::get_session_tokens(); ?>

	<p>
		<label for="ai1wm-debug-audit-filter"><strong>Filter by session:</strong></label>
		<select id="ai1wm-debug-audit-filter">
			<option value="">All sessions</option>
			<?php foreach ( $session_tokens as $token_prefix ) : ?>
				<option value="<?php echo esc_attr( $token_prefix ); ?>">
					Session <?php echo esc_html( $token_prefix ); ?>...
				</option>
			<?php endforeach; ?>
		</select>
		<button type="button" class="button" id="ai1wm-debug-audit-load">Load Entries</button>
	</p>

	<div id="ai1wm-debug-audit-content">
		<pre id="ai1wm-debug-audit-log" class="ai1wm-debug-log-viewer" style="display:none;"></pre>
	</div>

	<p id="ai1wm-debug-audit-pagination" style="display:none;">
		<button type="button" class="button" id="ai1wm-debug-audit-more">Load More</button>
		<span id="ai1wm-debug-audit-info"></span>
	</p>
<?php endif; ?>
