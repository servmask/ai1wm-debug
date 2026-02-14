/* ServMask Debug - Scripts */
(function($) {
	'use strict';

	var nonce = ai1wm_debug.nonce;
	var ajaxUrl = ai1wm_debug.ajax.url;
	var realtimeTimer = null;
	var realtimeLastPos = 0;
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
			var $tbody = $('#ai1wm-debug-tables-list tbody');
			$tbody.empty();

			if (data && data.length) {
				$.each(data, function(i, row) {
					$tbody.append(
						'<tr>' +
						'<td><code>' + escHtml(row.name) + '</code></td>' +
						'<td>' + escHtml(row.engine) + '</td>' +
						'<td>' + row.rows + '</td>' +
						'<td>' + escHtml(row.data_size) + '</td>' +
						'<td>' + escHtml(row.index_size) + '</td>' +
						'<td>' + escHtml(row.total_size) + '</td>' +
						'</tr>'
					);
				});
			} else {
				$tbody.append('<tr><td colspan="6">No tables found.</td></tr>');
			}

			$('#ai1wm-debug-tables-list').show();
			$('#ai1wm-debug-tables-loading').hide();
		});
	});

	// Log viewer
	$('#ai1wm-debug-log-load').on('click', function() {
		var fileKey = $('#ai1wm-debug-log-select').val();
		if (!fileKey) return;
		logOffset = -logLines;
		loadLog(fileKey);
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
			$('#ai1wm-debug-log-newer, #ai1wm-debug-log-older').show();
		});
	}

	// Realtime logger toggle
	$('#ai1wm-debug-realtime-save').on('click', function() {
		var enabled = $('#ai1wm-debug-logger-toggle').is(':checked') ? 1 : 0;
		var verbosity = $('#ai1wm-debug-logger-verbosity').val();

		$.post(ajaxUrl, {
			action: 'ai1wm_debug_toggle_logger',
			nonce: nonce,
			enabled: enabled,
			verbosity: verbosity
		}, function() {
			if (enabled) {
				startRealtimePolling();
			} else {
				stopRealtimePolling();
			}
		});
	});

	// Realtime polling
	function startRealtimePolling() {
		if (realtimeTimer) return;
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
			last_pos: realtimeLastPos
		}, function(response) {
			var data = typeof response === 'string' ? JSON.parse(response) : response;
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

	$('#ai1wm-debug-realtime-clear').on('click', function() {
		$('#ai1wm-debug-realtime-log').text('');
	});

	// Auto-start polling if logger is enabled
	if ($('#ai1wm-debug-logger-toggle').is(':checked')) {
		startRealtimePolling();
	}

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
			$btn.prop('disabled', false);

			if (data.error) {
				alert('Error: ' + data.error);
				return;
			}

			$('#ai1wm-debug-access-url').val(data.url);
			$('#ai1wm-debug-access-result').show();
		});
	});

	// Copy access URL
	$('#ai1wm-debug-copy-access-url').on('click', function() {
		var $input = $('#ai1wm-debug-access-url');
		$input.select();
		document.execCommand('copy');
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

	// Audit log
	$('#ai1wm-debug-audit-load').on('click', function() {
		auditOffset = 0;
		loadAuditEntries();
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

	// HTML escaping helper
	function escHtml(str) {
		if (!str) return '';
		return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

})(jQuery);
