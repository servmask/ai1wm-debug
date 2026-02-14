<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Kangaroos cannot fly!' ); } ?>

<h2>Real-time AI1WM Logging</h2>

<?php if ( ! ai1wm_debug_is_ai1wm_active() ) : ?>
	<div class="notice notice-warning inline">
		<p>All-in-One WP Migration is not active. Real-time logging requires AI1WM to be installed and activated.</p>
	</div>
<?php else : ?>
	<?php
		$enabled  = Ai1wm_Debug_Logger::is_enabled();
		$channels = Ai1wm_Debug_Logger::get_channels();
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
			<th scope="row">Logging Channels</th>
			<td>
				<fieldset>
					<label>
						<input type="checkbox" class="ai1wm-debug-channel" data-channel="pipeline" <?php checked( $channels['pipeline'] ); ?> />
						<strong>Pipeline Stages</strong> — log each export/import stage with priority and timing
					</label><br />
					<label>
						<input type="checkbox" class="ai1wm-debug-channel" data-channel="params" <?php checked( $channels['params'] ); ?> />
						<strong>Params Snapshot</strong> — dump full $params array at each stage <em>(very verbose)</em>
					</label><br />
					<label>
						<input type="checkbox" class="ai1wm-debug-channel" data-channel="status" <?php checked( $channels['status'] ); ?> />
						<strong>Status Messages</strong> — capture AI1WM progress messages (Ai1wm_Status writes)
					</label><br />
					<label>
						<input type="checkbox" class="ai1wm-debug-channel" data-channel="exclusions" <?php checked( $channels['exclusions'] ); ?> />
						<strong>File Exclusions</strong> — log content/media/plugin/theme exclusion filters
					</label><br />
					<label>
						<input type="checkbox" class="ai1wm-debug-channel" data-channel="http" <?php checked( $channels['http'] ); ?> />
						<strong>HTTP Loopback</strong> — log WP_CLI / cron pipeline continuation requests
					</label><br />
					<label>
						<input type="checkbox" class="ai1wm-debug-channel" data-channel="errors" <?php checked( $channels['errors'] ); ?> disabled />
						<strong>Errors</strong> — full exception details with params <em>(always on)</em>
					</label>
				</fieldset>
			</td>
		</tr>
	</table>

	<h3>Run Logs</h3>

	<?php $run_logs = Ai1wm_Debug_Logger::get_run_logs(); ?>

	<div id="ai1wm-debug-realtime-controls">
		<select id="ai1wm-debug-run-select">
			<option value="">Current run (live)</option>
			<?php foreach ( $run_logs as $log ) : ?>
				<?php if ( $log['current'] ) continue; ?>
				<option value="<?php echo esc_attr( $log['filename'] ); ?>">
					<?php echo esc_html( ucfirst( $log['type'] ) . ' — ' . $log['date'] . ' (' . ai1wm_debug_size_format( $log['size'], 2 ) . ')' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<label>
			<input type="checkbox" id="ai1wm-debug-realtime-autoscroll" checked />
			Auto-scroll
		</label>
		<button type="button" class="button" id="ai1wm-debug-realtime-download-log">Download</button>
		<button type="button" class="button" id="ai1wm-debug-realtime-delete-log" style="display:none;">Delete</button>
		<button type="button" class="button ai1wm-debug-button-danger" id="ai1wm-debug-realtime-clear-log">Delete All</button>
	</div>
	<pre id="ai1wm-debug-realtime-log" class="ai1wm-debug-log-viewer"></pre>

	<hr style="margin: 2em 0;" />

	<?php
		$overrides      = Ai1wm_Debug_Filters::get_overrides();
		$presets        = Ai1wm_Debug_Filters::get_preset_definitions();
		$export_stages  = Ai1wm_Debug_Filters::get_export_stages();
		$import_stages  = Ai1wm_Debug_Filters::get_import_stages();
	?>

	<h2>Filter Overrides</h2>
	<p class="description">Override AI1WM filter values during export/import operations. Active overrides are logged with [OVERRIDE] tag. Changes take effect on the next operation.</p>

	<h3>Preset Overrides</h3>
	<table class="widefat striped ai1wm-debug-table" id="ai1wm-debug-preset-overrides">
		<thead>
			<tr>
				<th style="width: 30px;">On</th>
				<th>Filter</th>
				<th style="width: 150px;">Value</th>
				<th style="width: 140px;">Steps</th>
				<th>Description</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $presets as $key => $def ) :
				$saved   = isset( $overrides['presets'][ $key ] ) ? $overrides['presets'][ $key ] : array();
				$on      = ! empty( $saved['enabled'] );
				$value   = isset( $saved['value'] ) ? $saved['value'] : $def['default'];
				$steps   = isset( $saved['steps'] ) ? $saved['steps'] : '';
			?>
			<tr>
				<td><input type="checkbox" class="ai1wm-debug-preset-enabled" data-key="<?php echo esc_attr( $key ); ?>" <?php checked( $on ); ?> /></td>
				<td>
					<strong><?php echo esc_html( $def['label'] ); ?></strong><br />
					<code><?php echo esc_html( $def['filter'] ); ?></code>
				</td>
				<td>
					<?php if ( $def['type'] === 'bool' ) : ?>
						<select class="ai1wm-debug-preset-value" data-key="<?php echo esc_attr( $key ); ?>">
							<option value="0" <?php selected( $value, 0 ); ?>>false</option>
							<option value="1" <?php selected( $value, 1 ); ?>>true</option>
						</select>
					<?php else : ?>
						<input type="number" class="ai1wm-debug-preset-value" data-key="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" style="width: 120px;" />
						<?php if ( $def['unit'] ) : ?>
							<span class="description"><?php echo esc_html( $def['unit'] ); ?></span>
						<?php endif; ?>
					<?php endif; ?>
					<br /><span class="description">default: <?php echo esc_html( $def['type'] === 'bool' ? ( $def['default'] ? 'true' : 'false' ) : $def['default'] ); ?></span>
				</td>
				<td>
					<input type="text" class="ai1wm-debug-preset-steps" data-key="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $steps ); ?>" placeholder="All steps" style="width: 120px;" />
				</td>
				<td><?php echo esc_html( $def['description'] ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<details style="margin: 1em 0;">
		<summary style="cursor: pointer;"><strong>Pipeline Step Reference</strong> <span class="description">(priority numbers for the Steps column)</span></summary>
		<div style="display: flex; gap: 2em; margin-top: 0.5em;">
			<div>
				<strong>Export</strong>
				<table class="widefat" style="width: auto;">
					<?php foreach ( $export_stages as $priority => $name ) : ?>
					<tr><td style="padding: 2px 8px;"><code><?php echo $priority; ?></code></td><td style="padding: 2px 8px;"><?php echo esc_html( $name ); ?></td></tr>
					<?php endforeach; ?>
				</table>
			</div>
			<div>
				<strong>Import</strong>
				<table class="widefat" style="width: auto;">
					<?php foreach ( $import_stages as $priority => $name ) : ?>
					<tr><td style="padding: 2px 8px;"><code><?php echo $priority; ?></code></td><td style="padding: 2px 8px;"><?php echo esc_html( $name ); ?></td></tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	</details>

	<h3>Exclusion Patterns</h3>
	<p class="description">Add file/directory patterns to exclude from export (one per line). These are appended to existing exclusions.</p>
	<table class="form-table">
		<?php
		$exclusion_labels = array(
			'content' => 'Content Exclusions',
			'media'   => 'Media Exclusions',
			'plugins' => 'Plugin Exclusions',
			'themes'  => 'Theme Exclusions',
		);
		foreach ( $exclusion_labels as $key => $label ) :
			$val = isset( $overrides['exclusions'][ $key ] ) ? $overrides['exclusions'][ $key ] : '';
		?>
		<tr>
			<th scope="row"><?php echo esc_html( $label ); ?></th>
			<td><textarea class="ai1wm-debug-exclusion" data-key="<?php echo esc_attr( $key ); ?>" rows="3" cols="50" placeholder="e.g. path/to/folder"><?php echo esc_textarea( $val ); ?></textarea></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<h3>Custom Filter Overrides</h3>
	<p class="description">Override any <code>ai1wm_*</code> filter. Use type <strong>php</strong> for custom code — receives <code>$value</code> (current filter value) and <code>$params</code> (AI1WM params), must <code>return</code> the new value.</p>
	<table class="widefat striped ai1wm-debug-table" id="ai1wm-debug-custom-filters">
		<thead>
			<tr>
				<th style="width: 30px;">On</th>
				<th>Filter Name</th>
				<th>Value</th>
				<th style="width: 80px;">Type</th>
				<th style="width: 140px;">Steps</th>
				<th style="width: 30px;"></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $overrides['custom'] ) ) : ?>
				<?php foreach ( $overrides['custom'] as $i => $custom ) : ?>
				<tr class="ai1wm-debug-custom-row">
					<td><input type="checkbox" class="ai1wm-debug-custom-enabled" <?php checked( ! empty( $custom['enabled'] ) ); ?> /></td>
					<td><input type="text" class="ai1wm-debug-custom-filter regular-text" value="<?php echo esc_attr( $custom['filter'] ); ?>" placeholder="ai1wm_filter_name" /></td>
					<td>
						<?php if ( isset( $custom['type'] ) && $custom['type'] === 'php' ) : ?>
							<textarea class="ai1wm-debug-custom-value" rows="3" cols="40" placeholder="return $value;"><?php echo esc_textarea( $custom['value'] ); ?></textarea>
						<?php else : ?>
							<input type="text" class="ai1wm-debug-custom-value" value="<?php echo esc_attr( $custom['value'] ); ?>" />
						<?php endif; ?>
					</td>
					<td>
						<select class="ai1wm-debug-custom-type">
							<option value="int" <?php selected( $custom['type'], 'int' ); ?>>int</option>
							<option value="string" <?php selected( $custom['type'], 'string' ); ?>>string</option>
							<option value="bool" <?php selected( $custom['type'], 'bool' ); ?>>bool</option>
							<option value="php" <?php selected( $custom['type'], 'php' ); ?>>php</option>
						</select>
					</td>
					<td><input type="text" class="ai1wm-debug-custom-steps" value="<?php echo esc_attr( isset( $custom['steps'] ) ? $custom['steps'] : '' ); ?>" placeholder="All steps" style="width: 120px;" /></td>
					<td><button type="button" class="button ai1wm-debug-custom-remove">&times;</button></td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6">
					<button type="button" class="button" id="ai1wm-debug-custom-add">+ Add Filter</button>
				</td>
			</tr>
		</tfoot>
	</table>

	<p style="margin-top: 1em;">
		<button type="button" class="button button-primary" id="ai1wm-debug-save-overrides">Save Overrides</button>
		<span id="ai1wm-debug-overrides-saved" style="display:none; color: green; margin-left: 10px;">Saved! Changes take effect on next operation.</span>
	</p>

<?php endif; ?>
