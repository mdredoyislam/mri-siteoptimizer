/* global SwiftOptData, jQuery */
(function ($) {
	'use strict';

	const { nonce, ajaxurl, strings } = SwiftOptData;

	// -----------------------------------------------------------------------
	// Utilities
	// -----------------------------------------------------------------------

	function ajax(action, data, $btn) {
		if ($btn) $btn.prop('disabled', true).prepend('<span class="so-spinner" style="margin-right:6px"></span>');
		return $.post(ajaxurl, Object.assign({ action, nonce }, data))
			.always(function () {
				if ($btn) {
					$btn.prop('disabled', false).find('.so-spinner').remove();
				}
			});
	}

	function notice(msg, type, $target) {
		const cls = { success: 'so-notice-success', error: 'so-notice-error', info: 'so-notice-info' };
		const $n  = $('<div class="so-notice ' + (cls[type] || cls.info) + '">' + msg + '</div>');
		if ($target) {
			$target.prepend($n);
		} else {
			$('.swiftopt-wrap .so-header').after($n);
		}
		setTimeout(() => $n.fadeOut(400, () => $n.remove()), 4000);
	}

	function formatBytes(b) {
		if (!b) return '0 B';
		const sizes = ['B','KB','MB','GB'];
		const i = Math.floor(Math.log(b) / Math.log(1024));
		return parseFloat((b / Math.pow(1024, i)).toFixed(1)) + ' ' + sizes[i];
	}

	// -----------------------------------------------------------------------
	// Dashboard page
	// -----------------------------------------------------------------------

	function loadDashboard() {
		if (!$('#so-dashboard-stats').length) return;
		ajax('swiftopt_get_dashboard_stats', {}).done(function (res) {
			if (!res.success) return;
			const d = res.data;

			$('#so-stat-unlinked').text(d.unlinked_count);
			$('#so-stat-orphans').text(d.orphan_count);
			$('#so-stat-db').text(d.db_items);
			$('#so-stat-saved').text(formatBytes(d.total_saved));

			if (d.unlinked_count > 0) $('#so-stat-unlinked-sub').addClass('warn').text(formatBytes(d.unlinked_size) + ' wasted');
			if (d.orphan_count  > 0) $('#so-stat-orphans-sub').addClass('warn').text(formatBytes(d.orphan_size) + ' recoverable');
			if (d.db_items > 0)      $('#so-stat-db-sub').addClass('warn').text('Needs cleanup');
			if (d.total_saved > 0)   $('#so-stat-saved-sub').addClass('ok').text('Since install');

			// Health score (rough)
			let score = 100;
			if (d.unlinked_count > 50)  score -= 15;
			else if (d.unlinked_count > 10) score -= 8;
			if (d.orphan_count > 50)    score -= 10;
			if (d.db_items > 1000)      score -= 15;
			else if (d.db_items > 100)  score -= 8;
			score = Math.max(score, 10);
			$('#so-health-num').text(score);

			// Perf bars
			const imgPct   = Math.max(10, 100 - d.unlinked_count * 0.5);
			const dbPct    = Math.max(10, 100 - (d.db_items / 20));
			const filePct  = Math.max(10, 100 - d.orphan_count * 0.8);
			$('#pb-images').css('width', Math.min(100, imgPct) + '%');
			$('#pb-db').css('width', Math.min(100, dbPct) + '%');
			$('#pb-files').css('width', Math.min(100, filePct) + '%');

			// Activity
			const $log = $('#so-activity-log').empty();
			if (d.activity && d.activity.length) {
				d.activity.forEach(function (row) {
					const colors = { scan: '#4F46E5', image_delete: '#EF4444', compress_upload: '#10B981', db_optimize: '#F59E0B', file_delete: '#F59E0B', bulk_compress: '#10B981' };
					const color  = colors[row.action_type] || '#6B7280';
					$log.append(
						'<div class="so-activity-item">' +
						'<div class="so-act-dot" style="background:' + color + '"></div>' +
						'<div><div class="so-act-msg">' + row.message + '</div>' +
						'<div class="so-act-time">' + row.created_at + '</div></div></div>'
					);
				});
			} else {
				$log.append('<p style="font-size:12px;color:#9CA3AF;padding:8px 0">No activity yet.</p>');
			}

			if (d.last_scan) $('#so-last-scan').text('Last scan: ' + d.last_scan);
		});
	}

	// -----------------------------------------------------------------------
	// Image scanner page
	// -----------------------------------------------------------------------

	$(document).on('click', '#so-btn-scan-images', function () {
		const $btn = $(this);
		const $res = $('#so-images-results').html('<div class="so-loading-row"><span class="so-spinner"></span> ' + strings.scanning + '</div>');

		ajax('swiftopt_scan_images', {}, $btn).done(function (res) {
			if (!res.success) { notice(res.data.message, 'error'); return; }
			const d = res.data;

			if (!d.count) {
				$res.html('<div class="so-notice so-notice-success">' + strings.no_items + ' Your media library looks clean!</div>');
				return;
			}

			let html = '<div class="so-actions-bar">' +
				'<span style="font-size:13px;color:#6B7280">Found <strong>' + d.count + '</strong> unlinked images (' + formatBytes(d.total_size) + ')</span>' +
				'<div style="display:flex;gap:8px">' +
				'<button class="so-btn so-btn-outline so-btn-sm" id="so-select-all-images">Select all</button>' +
				'<button class="so-btn so-btn-danger so-btn-sm" id="so-delete-selected-images" disabled>Delete selected</button>' +
				'</div></div>' +
				'<div class="so-table-wrap"><table class="so-table">' +
				'<thead><tr><th><input type="checkbox" class="so-checkbox" id="so-check-all-img"></th>' +
				'<th>Preview</th><th>File</th><th>Size</th><th>Date</th></tr></thead><tbody>';

			d.items.forEach(function (img) {
				html += '<tr><td><input type="checkbox" class="so-checkbox so-img-check" value="' + img.id + '"></td>' +
					'<td>' + (img.thumbnail ? '<img src="' + img.thumbnail + '" class="thumb" loading="lazy">' : '<span style="color:#D1D5DB">No preview</span>') + '</td>' +
					'<td><div class="filename">' + (img.title || '(no title)') + '</div><div style="font-size:11px;color:#9CA3AF">' + (img.url || '') + '</div></td>' +
					'<td class="filesize">' + formatBytes(img.size) + '</td>' +
					'<td style="font-size:12px;color:#6B7280">' + (img.date || '') + '</td></tr>';
			});

			html += '</tbody></table></div>';
			$res.html(html);
		});
	});

	$(document).on('change', '#so-check-all-img', function () {
		$('.so-img-check').prop('checked', $(this).is(':checked'));
		updateDeleteBtn();
	});
	$(document).on('change', '.so-img-check', updateDeleteBtn);
	function updateDeleteBtn() {
		const count = $('.so-img-check:checked').length;
		$('#so-delete-selected-images').prop('disabled', count === 0).text('Delete selected (' + count + ')');
	}

	$(document).on('click', '#so-select-all-images', function () {
		const allChecked = $('.so-img-check').length === $('.so-img-check:checked').length;
		$('.so-img-check, #so-check-all-img').prop('checked', !allChecked);
		updateDeleteBtn();
	});

	$(document).on('click', '#so-delete-selected-images', function () {
		if (!confirm(strings.confirm_del)) return;
		const ids = $('.so-img-check:checked').map((i, el) => el.value).get();
		ajax('swiftopt_delete_images', { ids }, $(this)).done(function (res) {
			if (res.success) {
				notice('Deleted ' + res.data.deleted + ' images, freed ' + formatBytes(res.data.bytes) + '.', 'success');
				$('#so-btn-scan-images').trigger('click');
			}
		});
	});

	// -----------------------------------------------------------------------
	// Bulk compress
	// -----------------------------------------------------------------------

	$(document).on('click', '#so-btn-bulk-compress', function () {
		const $btn = $(this);
		ajax('swiftopt_bulk_compress', { batch: 50 }, $btn).done(function (res) {
			if (res.success) {
				notice('Compressed ' + res.data.processed + ' images, saved ' + formatBytes(res.data.saved) + '.', 'success');
			}
		});
	});

	// -----------------------------------------------------------------------
	// Files page
	// -----------------------------------------------------------------------

	$(document).on('click', '#so-btn-scan-orphans', function () {
		const $btn = $(this);
		const $res = $('#so-orphan-results').html('<div class="so-loading-row"><span class="so-spinner"></span> ' + strings.scanning + '</div>');
		ajax('swiftopt_scan_orphans', {}, $btn).done(function (res) {
			if (!res.success) return;
			renderFileTable($res, res.data, 'orphan', 'paths');
		});
	});

	$(document).on('click', '#so-btn-scan-junk', function () {
		const $btn = $(this);
		const $res = $('#so-junk-results').html('<div class="so-loading-row"><span class="so-spinner"></span> ' + strings.scanning + '</div>');
		ajax('swiftopt_scan_junk', {}, $btn).done(function (res) {
			if (!res.success) return;
			renderFileTable($res, res.data, 'junk', 'paths');
		});
	});

	function renderFileTable($container, d, prefix, valKey) {
		if (!d.count) {
			$container.html('<div class="so-notice so-notice-success">' + strings.no_items + '</div>');
			return;
		}
		let html = '<div class="so-actions-bar">' +
			'<span style="font-size:13px;color:#6B7280">Found <strong>' + d.count + '</strong> files (' + formatBytes(d.total_size) + ')</span>' +
			'<button class="so-btn so-btn-danger so-btn-sm so-delete-files-btn" data-prefix="' + prefix + '" disabled>Delete selected</button>' +
			'</div><div class="so-table-wrap"><table class="so-table">' +
			'<thead><tr><th><input type="checkbox" class="so-checkbox so-check-all-file" data-prefix="' + prefix + '"></th>' +
			'<th>Path</th><th>Size</th><th>Ext</th></tr></thead><tbody>';

		d.items.forEach(function (f) {
			const pathVal = f.path || '';
			html += '<tr><td><input type="checkbox" class="so-checkbox so-file-check-' + prefix + '" value="' + pathVal + '"></td>' +
				'<td style="font-size:12px;word-break:break-all">' + pathVal + '</td>' +
				'<td class="filesize">' + formatBytes(f.size) + '</td>' +
				'<td><span class="so-badge so-badge-info">' + (f.ext || '—') + '</span></td></tr>';
		});
		html += '</tbody></table></div>';
		$container.html(html);
	}

	$(document).on('change', '.so-check-all-file', function () {
		const prefix = $(this).data('prefix');
		$('.so-file-check-' + prefix).prop('checked', $(this).is(':checked'));
		updateFileDeleteBtn(prefix);
	});
	$(document).on('change', '[class^="so-file-check-"]', function () {
		const prefix = $(this).attr('class').replace('so-file-check-', '').replace('so-checkbox ', '').trim();
		updateFileDeleteBtn(prefix);
	});
	function updateFileDeleteBtn(prefix) {
		const count = $('.so-file-check-' + prefix + ':checked').length;
		$('[data-prefix="' + prefix + '"].so-delete-files-btn').prop('disabled', count === 0).text('Delete selected (' + count + ')');
	}

	$(document).on('click', '.so-delete-files-btn', function () {
		if (!confirm(strings.confirm_del)) return;
		const prefix = $(this).data('prefix');
		const paths  = $('.so-file-check-' + prefix + ':checked').map((i, el) => el.value).get();
		const action = prefix === 'orphan' ? 'swiftopt_delete_orphans' : 'swiftopt_delete_junk';
		ajax(action, { paths }, $(this)).done(function (res) {
			if (res.success) {
				notice('Deleted ' + res.data.deleted + ' files, freed ' + formatBytes(res.data.bytes) + '.', 'success');
				$('#so-btn-scan-' + (prefix === 'orphan' ? 'orphans' : 'junk')).trigger('click');
			}
		});
	});

	// -----------------------------------------------------------------------
	// Database page
	// -----------------------------------------------------------------------

	function loadDbStats() {
		if (!$('#so-db-stats-wrap').length) return;
		ajax('swiftopt_db_stats', {}).done(function (res) {
			if (!res.success) return;
			const s = res.data;
			$('#so-db-revisions').text(s.revisions);
			$('#so-db-auto-drafts').text(s.auto_drafts);
			$('#so-db-trash-posts').text(s.trash_posts);
			$('#so-db-spam').text(s.spam_comments);
			$('#so-db-trash-comments').text(s.trash_comments);
			$('#so-db-transients').text(s.expired_transients);
			$('#so-db-orphan-meta').text(s.orphan_postmeta);
		});
	}

	$(document).on('click', '#so-btn-optimize-db', function () {
		if (!confirm('This will clean selected database items. Continue?')) return;
		ajax('swiftopt_db_optimize', {}, $(this)).done(function (res) {
			if (res.success) {
				notice(res.data.message, 'success');
				loadDbStats();
			}
		});
	});

	// -----------------------------------------------------------------------
	// Settings
	// -----------------------------------------------------------------------

	$(document).on('click', '#so-btn-save-settings', function () {
		const data = {};
		$('[data-setting]').each(function () {
			const key = $(this).data('setting');
			if ($(this).is(':checkbox')) {
				data[key] = $(this).is(':checked') ? '1' : '0';
			} else {
				data[key] = $(this).val();
			}
		});
		ajax('swiftopt_save_settings', data, $(this)).done(function (res) {
			if (res.success) notice(strings.saved, 'success');
		});
	});

	// -----------------------------------------------------------------------
	// Activity log page
	// -----------------------------------------------------------------------

	function loadLog() {
		if (!$('#so-log-table-body').length) return;
		ajax('swiftopt_get_log', { limit: 50 }).done(function (res) {
			if (!res.success) return;
			const colors = { scan: '#4F46E5', image_delete: '#EF4444', compress_upload: '#10B981', db_optimize: '#F59E0B', file_delete: '#F59E0B', bulk_compress: '#10B981' };
			const $tbody = $('#so-log-table-body').empty();
			if (!res.data.length) {
				$tbody.append('<tr><td colspan="4" style="text-align:center;padding:30px;color:#9CA3AF">No activity logged yet.</td></tr>');
				return;
			}
			res.data.forEach(function (row) {
				const color = colors[row.action_type] || '#6B7280';
				$tbody.append(
					'<tr><td><span class="so-badge so-badge-info" style="background:' + color + '20;color:' + color + '">' + row.action_type + '</span></td>' +
					'<td>' + row.message + '</td>' +
					'<td>' + (row.saved_bytes > 0 ? formatBytes(row.saved_bytes) : '—') + '</td>' +
					'<td style="font-size:12px;color:#9CA3AF">' + row.created_at + '</td></tr>'
				);
			});
		});
	}

	// -----------------------------------------------------------------------
	// Quick-action buttons (sidebar/dashboard)
	// -----------------------------------------------------------------------

	$(document).on('click', '#so-quick-scan', function () {
		ajax('swiftopt_run_full_scan', {}, $(this)).done(function (res) {
			if (res.success) { notice(res.data.message, 'success'); loadDashboard(); }
		});
	});

	$(document).on('click', '#so-quick-compress', function () {
		ajax('swiftopt_bulk_compress', { batch: 50 }, $(this)).done(function (res) {
			if (res.success) notice('Compressed ' + res.data.processed + ' images, saved ' + formatBytes(res.data.saved) + '.', 'success');
		});
	});

	$(document).on('click', '#so-quick-db', function () {
		ajax('swiftopt_db_optimize', {}, $(this)).done(function (res) {
			if (res.success) { notice(res.data.message, 'success'); loadDashboard(); }
		});
	});

	// -----------------------------------------------------------------------
	// Tabs
	// -----------------------------------------------------------------------

	$(document).on('click', '.so-tab', function () {
		const target = $(this).data('tab');
		$('.so-tab').removeClass('active');
		$('.so-tab-content').removeClass('active');
		$(this).addClass('active');
		$('#' + target).addClass('active');
	});

	// -----------------------------------------------------------------------
	// Init
	// -----------------------------------------------------------------------

	$(document).ready(function () {
		loadDashboard();
		loadDbStats();
		loadLog();
	});

})(jQuery);
