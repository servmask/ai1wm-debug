/* ServMask Debug - Scripts */
(function($) {
	'use strict';

	var nonce = ai1wm_debug.nonce;
	var ajaxUrl = ai1wm_debug.ajax.url;
	var realtimeTimer = null;
	var realtimeLastPos = 0;
	var realtimeCurrentFile = '';
	var logOffset = 0;
	var logLines = 100;
	var auditOffset = 0;

	// Database tables loading
	$('#ai1wm-debug-load-tables').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true);
		$('#ai1wm-debug-tables-loading').show();

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_get_database_tables',
			nonce: nonce
		}, function(response) {
			var data = typeof response === 'string' ? JSON.parse(response) : response;

			// Prefixed tables
			var $tbody = $('#ai1wm-debug-tables-list tbody');
			$tbody.empty();
			if (data.prefixed && data.prefixed.length) {
				$.each(data.prefixed, function(i, row) {
					$tbody.append(renderTableRow(row));
				});
			} else {
				$tbody.append('<tr><td colspan="6">No tables found.</td></tr>');
			}
			$('#ai1wm-debug-tables-list').show();

			// Non-prefixed tables
			var $npTbody = $('#ai1wm-debug-non-prefixed-list tbody');
			$npTbody.empty();
			if (data.non_prefixed && data.non_prefixed.length) {
				$.each(data.non_prefixed, function(i, row) {
					$npTbody.append(renderTableRow(row));
				});
				$('#ai1wm-debug-non-prefixed-wrapper').show();
			}

			$('#ai1wm-debug-tables-loading').hide();
		});
	});

	function renderTableRow(row) {
		return '<tr>' +
			'<td><code>' + escHtml(row.name) + '</code></td>' +
			'<td>' + escHtml(row.engine) + '</td>' +
			'<td>' + escHtml(String(row.rows)) + '</td>' +
			'<td>' + escHtml(row.data_size) + '</td>' +
			'<td>' + escHtml(row.index_size) + '</td>' +
			'<td>' + escHtml(row.total_size) + '</td>' +
			'</tr>';
	}

	// Log viewer
	$('#ai1wm-debug-log-select').on('change', function() {
		$('#ai1wm-debug-log-load').prop('disabled', !$(this).val());
	});

	$('#ai1wm-debug-log-load').on('click', function() {
		var fileKey = $('#ai1wm-debug-log-select').val();
		if (!fileKey) return;
		logOffset = -logLines;
		loadLog(fileKey);
		$(this).prop('disabled', true);
	});

	$('#ai1wm-debug-log-newer').on('click', function() {
		var fileKey = $('#ai1wm-debug-log-select').val();
		logOffset = Math.min(logOffset + logLines, parseInt($('#ai1wm-debug-log-total').text()) - logLines);
		loadLog(fileKey);
	});

	$('#ai1wm-debug-log-older').on('click', function() {
		var fileKey = $('#ai1wm-debug-log-select').val();
		logOffset = Math.max(logOffset - logLines, 0);
		loadLog(fileKey);
	});

	function loadLog(fileKey) {
		$.post(ajaxUrl, {
			action: 'ai1wm_debug_read_log',
			nonce: nonce,
			file: fileKey,
			offset: logOffset,
			lines: logLines
		}, function(response) {
			var data = typeof response === 'string' ? JSON.parse(response) : response;
			$('#ai1wm-debug-log-content').text(data.content || 'Empty log file.').show();

			var actualOffset = data.offset >= 0 ? data.offset : Math.max(0, data.total_lines + data.offset);
			logOffset = actualOffset;

			$('#ai1wm-debug-log-offset').text(actualOffset + 1);
			$('#ai1wm-debug-log-end').text(Math.min(actualOffset + logLines, data.total_lines));
			$('#ai1wm-debug-log-total').text(data.total_lines);
			$('#ai1wm-debug-log-info').show();
			$('#ai1wm-debug-log-older').prop('disabled', actualOffset <= 0);
			$('#ai1wm-debug-log-newer').prop('disabled', actualOffset + logLines >= data.total_lines);
			$('#ai1wm-debug-log-newer, #ai1wm-debug-log-older').show();
		});
	}

	// Realtime logger - auto-save on any checkbox change
	function saveLoggerSettings() {
		var enabled = $('#ai1wm-debug-logger-toggle').is(':checked') ? 1 : 0;
		var channels = {};
		$('.ai1wm-debug-channel').each(function() {
			channels[$(this).data('channel')] = $(this).is(':checked') ? 1 : 0;
		});
		channels.errors = 1; // Always on

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_toggle_logger',
			nonce: nonce,
			enabled: enabled,
			channels: channels
		}, function() {
			if (enabled) {
				startRealtimePolling();
			} else {
				stopRealtimePolling();
			}
		});
	}

	$('#ai1wm-debug-logger-toggle, .ai1wm-debug-channel').on('change', saveLoggerSettings);

	// Realtime polling
	var viewingHistorical = false;

	function startRealtimePolling() {
		if (realtimeTimer || viewingHistorical) return;
		realtimeTimer = setInterval(pollRealtime, 2000);
	}

	function stopRealtimePolling() {
		if (realtimeTimer) {
			clearInterval(realtimeTimer);
			realtimeTimer = null;
		}
	}

	function pollRealtime() {
		$.post(ajaxUrl, {
			action: 'ai1wm_debug_poll_realtime_log',
			nonce: nonce,
			last_pos: realtimeLastPos,
			filename: realtimeCurrentFile
		}, function(response) {
			var data = typeof response === 'string' ? JSON.parse(response) : response;

			// Detect new run — reset log display and update selector
			if (data.current_file && data.current_file !== realtimeCurrentFile) {
				realtimeCurrentFile = data.current_file;
				realtimeLastPos = 0;
				$('#ai1wm-debug-realtime-log').text('');
				refreshRunSelector();
				return;
			}

			if (data.entries && data.entries.length) {
				var $log = $('#ai1wm-debug-realtime-log');
				var text = $log.text() + data.entries.join('\n') + '\n';
				$log.text(text);

				if ($('#ai1wm-debug-realtime-autoscroll').is(':checked')) {
					$log[0].scrollTop = $log[0].scrollHeight;
				}
			}
			realtimeLastPos = data.last_pos;
		});
	}

	// Load a historical log file fully
	function loadRunLog(filename) {
		$('#ai1wm-debug-realtime-log').text('Loading...');
		$.post(ajaxUrl, {
			action: 'ai1wm_debug_poll_realtime_log',
			nonce: nonce,
			last_pos: 0,
			filename: filename
		}, function(response) {
			var data = typeof response === 'string' ? JSON.parse(response) : response;
			if (data.entries && data.entries.length) {
				$('#ai1wm-debug-realtime-log').text(data.entries.join('\n') + '\n');
			} else {
				$('#ai1wm-debug-realtime-log').text('Empty log file.');
			}
		});
	}

	// Refresh the run selector dropdown
	function refreshRunSelector() {
		$.post(ajaxUrl, {
			action: 'ai1wm_debug_get_run_logs',
			nonce: nonce
		}, function(response) {
			var logs = typeof response === 'string' ? JSON.parse(response) : response;
			var $select = $('#ai1wm-debug-run-select');
			var currentVal = $select.val();
			$select.find('option:not(:first)').remove();
			$.each(logs, function(i, log) {
				if (log.current) return;
				var label = log.type.charAt(0).toUpperCase() + log.type.slice(1) +
					' — ' + log.date + ' (' + formatSize(log.size) + ')';
				$select.append('<option value="' + escHtml(log.filename) + '">' +
					escHtml(label) + '</option>');
			});
			$select.val(currentVal);
		});
	}

	// Run selector change
	$('#ai1wm-debug-run-select').on('change', function() {
		var filename = $(this).val();
		if (filename) {
			// Viewing a specific log
			viewingHistorical = true;
			stopRealtimePolling();
			loadRunLog(filename);
			$('#ai1wm-debug-realtime-delete-log').show();
			$('#ai1wm-debug-realtime-autoscroll').closest('label').hide();
		} else {
			// Back to live mode
			viewingHistorical = false;
			realtimeLastPos = 0;
			realtimeCurrentFile = '';
			$('#ai1wm-debug-realtime-log').text('');
			$('#ai1wm-debug-realtime-delete-log').hide();
			$('#ai1wm-debug-realtime-autoscroll').closest('label').show();
			if ($('#ai1wm-debug-logger-toggle').is(':checked')) {
				startRealtimePolling();
			}
		}
	});

	// Download current/selected log
	$('#ai1wm-debug-realtime-download-log').on('click', function() {
		var filename = $('#ai1wm-debug-run-select').val() || realtimeCurrentFile;
		window.location = ajaxUrl + '?action=ai1wm_debug_download_realtime_log&nonce=' + nonce + '&filename=' + encodeURIComponent(filename);
	});

	// Delete selected log
	$('#ai1wm-debug-realtime-delete-log').on('click', function() {
		var filename = $('#ai1wm-debug-run-select').val();
		if (!filename || !confirm('Delete this log file?')) return;

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_delete_run_log',
			nonce: nonce,
			filename: filename
		}, function() {
			$('#ai1wm-debug-run-select').val('').trigger('change');
			refreshRunSelector();
		});
	});

	// Delete all logs
	$('#ai1wm-debug-realtime-clear-log').on('click', function() {
		if (!confirm('Delete ALL log files? This cannot be undone.')) return;

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_clear_realtime_log',
			nonce: nonce
		}, function() {
			$('#ai1wm-debug-realtime-log').text('');
			$('#ai1wm-debug-run-select').val('');
			realtimeLastPos = 0;
			realtimeCurrentFile = '';
			viewingHistorical = false;
			refreshRunSelector();
		});
	});

	// Auto-start polling if logger is enabled
	if ($('#ai1wm-debug-logger-toggle').is(':checked')) {
		startRealtimePolling();
	}

	// Filter overrides - add custom row
	$('#ai1wm-debug-custom-add').on('click', function() {
		var $row = $(
			'<tr class="ai1wm-debug-custom-row">' +
			'<td><input type="checkbox" class="ai1wm-debug-custom-enabled" checked /></td>' +
			'<td><input type="text" class="ai1wm-debug-custom-filter regular-text" placeholder="ai1wm_filter_name" /></td>' +
			'<td><input type="text" class="ai1wm-debug-custom-value" /></td>' +
			'<td><select class="ai1wm-debug-custom-type"><option value="int">int</option><option value="string">string</option><option value="bool">bool</option><option value="php">php</option></select></td>' +
			'<td><input type="text" class="ai1wm-debug-custom-steps" placeholder="All steps" style="width: 120px;" /></td>' +
			'<td><button type="button" class="button ai1wm-debug-custom-remove">&times;</button></td>' +
			'</tr>'
		);
		$('#ai1wm-debug-custom-filters tbody').append($row);
	});

	// Switch value field between input and textarea when type changes
	$(document).on('change', '.ai1wm-debug-custom-type', function() {
		var $td = $(this).closest('tr').find('.ai1wm-debug-custom-value').closest('td');
		var currentVal = $td.find('.ai1wm-debug-custom-value').val();

		if ($(this).val() === 'php') {
			$td.html('<textarea class="ai1wm-debug-custom-value" rows="3" cols="40" placeholder="return $value;"></textarea>');
		} else {
			$td.html('<input type="text" class="ai1wm-debug-custom-value" />');
		}
		$td.find('.ai1wm-debug-custom-value').val(currentVal);
	});

	// Filter overrides - remove custom row
	$(document).on('click', '.ai1wm-debug-custom-remove', function() {
		$(this).closest('tr').remove();
	});

	// Filter overrides - save
	$('#ai1wm-debug-save-overrides').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).text('Saving...');

		// Collect presets
		var presets = {};
		$('.ai1wm-debug-preset-enabled').each(function() {
			var key = $(this).data('key');
			presets[key] = {
				enabled: $(this).is(':checked') ? 1 : 0,
				value: $('.ai1wm-debug-preset-value[data-key="' + key + '"]').val(),
				steps: $('.ai1wm-debug-preset-steps[data-key="' + key + '"]').val()
			};
		});

		// Collect exclusions
		var exclusions = {};
		$('.ai1wm-debug-exclusion').each(function() {
			exclusions[$(this).data('key')] = $(this).val();
		});

		// Collect custom filters
		var custom = [];
		$('.ai1wm-debug-custom-row').each(function() {
			custom.push({
				enabled: $(this).find('.ai1wm-debug-custom-enabled').is(':checked') ? 1 : 0,
				filter: $(this).find('.ai1wm-debug-custom-filter').val(),
				value: $(this).find('.ai1wm-debug-custom-value').val(),
				type: $(this).find('.ai1wm-debug-custom-type').val(),
				steps: $(this).find('.ai1wm-debug-custom-steps').val()
			});
		});

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_save_filter_overrides',
			nonce: nonce,
			presets: presets,
			exclusions: exclusions,
			custom: custom
		}, function() {
			$btn.prop('disabled', false).text('Save Overrides');
			$('#ai1wm-debug-overrides-saved').show().delay(3000).fadeOut();
		});
	});

	// Support access - toggle info panels and reset confirmation
	$('#ai1wm-debug-access-level').on('change', function() {
		$('.ai1wm-debug-access-info').hide();
		$('#ai1wm-debug-access-info-' + $(this).val()).show();
		$('#ai1wm-debug-access-confirm').prop('checked', false);
		$('#ai1wm-debug-generate-access').prop('disabled', true);
	});

	// Support access - confirmation checkbox
	$('#ai1wm-debug-access-confirm').on('change', function() {
		$('#ai1wm-debug-generate-access').prop('disabled', !$(this).is(':checked'));
	});

	// Support access - generate
	$('#ai1wm-debug-generate-access').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true);
		var level = $('#ai1wm-debug-access-level').val();

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_generate_access',
			nonce: nonce,
			level: level
		}, function(response) {
			var data = typeof response === 'string' ? JSON.parse(response) : response;

			if (data.error) {
				alert('Error: ' + data.error);
				$btn.prop('disabled', false);
				return;
			}

			$('#ai1wm-debug-access-url').val(data.url);
			$('#ai1wm-debug-access-result').show();
			$('#ai1wm-debug-access-confirm').prop('checked', false);
		});
	});

	// Copy access URL
	$('#ai1wm-debug-copy-access-url').on('click', function() {
		var $input = $('#ai1wm-debug-access-url');
		$input.select();
		document.execCommand('copy');
	});

	// Copy token URL from active tokens table
	$(document).on('click', '.ai1wm-debug-copy-token-url', function() {
		var url = $(this).data('url');
		var $btn = $(this);
		var $temp = $('<input>').val(url).appendTo('body').select();
		document.execCommand('copy');
		$temp.remove();
		$btn.text('Copied!');
		setTimeout(function() { $btn.text('Copy Link'); }, 2000);
	});

	// Revoke single token
	$('.ai1wm-debug-revoke-access').on('click', function() {
		if (!confirm('Revoke this support access? The temporary user will be deleted.')) return;

		var token = $(this).data('token');
		var $row = $(this).closest('tr');

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_revoke_access',
			nonce: nonce,
			token: token
		}, function() {
			$row.fadeOut(300, function() { $(this).remove(); });
		});
	});

	// Revoke all
	$('#ai1wm-debug-revoke-all-access').on('click', function() {
		if (!confirm('Revoke ALL support access? All temporary users will be deleted.')) return;

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_revoke_all_access',
			nonce: nonce
		}, function() {
			location.reload();
		});
	});

	// Audit log - enable load and show/hide delete on selection change
	$('#ai1wm-debug-audit-filter').on('change', function() {
		$('#ai1wm-debug-audit-load').prop('disabled', false);
		if ($(this).val()) {
			$('#ai1wm-debug-audit-delete').show();
		} else {
			$('#ai1wm-debug-audit-delete').hide();
		}
	});

	$('#ai1wm-debug-audit-load').on('click', function() {
		auditOffset = 0;
		loadAuditEntries();
	});

	$('#ai1wm-debug-audit-delete').on('click', function() {
		if (!confirm('Delete this audit log?')) return;

		var token = $('#ai1wm-debug-audit-filter').val();
		$.post(ajaxUrl, {
			action: 'ai1wm_debug_delete_audit_log',
			nonce: nonce,
			token: token
		}, function() {
			location.reload();
		});
	});

	$('#ai1wm-debug-audit-more').on('click', function() {
		loadAuditEntries(true);
	});

	function loadAuditEntries(append) {
		var token = $('#ai1wm-debug-audit-filter').val();

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_get_audit_entries',
			nonce: nonce,
			token: token,
			offset: auditOffset,
			limit: 100
		}, function(response) {
			var data = typeof response === 'string' ? JSON.parse(response) : response;
			var $log = $('#ai1wm-debug-audit-log');

			if (append) {
				$log.text($log.text() + '\n' + data.entries.join('\n'));
			} else {
				$log.text(data.entries.join('\n'));
			}

			$log.show();
			auditOffset += data.entries.length;

			$('#ai1wm-debug-audit-info').text(
				'Showing ' + auditOffset + ' of ' + data.total + ' entries'
			);
			$('#ai1wm-debug-audit-pagination').show();

			if (auditOffset >= data.total) {
				$('#ai1wm-debug-audit-more').hide();
			} else {
				$('#ai1wm-debug-audit-more').show();
			}
		});
	}

	// Report - copy to clipboard
	$('#ai1wm-debug-copy-report').on('click', function() {
		var $btn = $(this);
		$btn.prop('disabled', true).text('Generating...');

		$.get(ajaxUrl, {
			action: 'ai1wm_debug_download_report',
			nonce: nonce,
			format: 'text'
		}, function(text) {
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function() {
					$btn.text('Copied!');
					setTimeout(function() {
						$btn.prop('disabled', false).text('Copy to Clipboard');
					}, 2000);
				});
			} else {
				var $temp = $('<textarea>').val(text).appendTo('body');
				$temp.select();
				document.execCommand('copy');
				$temp.remove();
				$btn.text('Copied!');
				setTimeout(function() {
					$btn.prop('disabled', false).text('Copy to Clipboard');
				}, 2000);
			}
		});
	});

	// Size formatting helper
	function formatSize(bytes) {
		if (bytes === 0) return '0 B';
		var units = ['B', 'KB', 'MB', 'GB'];
		var i = Math.floor(Math.log(bytes) / Math.log(1024));
		return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + units[i];
	}

	// HTML escaping helper
	function escHtml(str) {
		if (!str) return '';
		return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

})(jQuery);
