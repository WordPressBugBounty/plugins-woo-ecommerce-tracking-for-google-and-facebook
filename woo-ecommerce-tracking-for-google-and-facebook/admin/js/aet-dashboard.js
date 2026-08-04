/**
 * GA4 Overview dashboard UI + OAuth setup.
 */
(function ($) {
	'use strict';

	if (typeof aetGa4Dash === 'undefined') {
		return;
	}

	function setResult(message, ok) {
		var $el = $('#aet_ga4_dash_result');
		if (!$el.length) {
			return;
		}
		$el.removeClass('is-ok is-error').addClass(ok ? 'is-ok' : 'is-error').text(message || '');
	}

	function initSetupHandlers() {
		$(document).on('click', '#aet_ga4_dash_save_property', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var propertyId = '';
			var propertyName = '';

			if ($('#aet_ga4_property_select').length) {
				var $opt = $('#aet_ga4_property_select option:selected');
				propertyId = $opt.val() || '';
				propertyName = $opt.data('name') || '';
			} else {
				propertyId = $('#aet_ga4_property_id_manual').val() || '';
			}

			if (!propertyId) {
				setResult(aetGa4Dash.i18n.pick, false);
				return;
			}

			$btn.prop('disabled', true);
			setResult(aetGa4Dash.i18n.saving, true);

			$.post(aetGa4Dash.ajaxurl, {
				action: 'aet_ga4_dashboard_save_property',
				nonce: aetGa4Dash.nonce,
				property_id: propertyId,
				property_name: propertyName
			})
				.done(function (res) {
					if (res && res.success) {
						setResult((res.data && res.data.message) || aetGa4Dash.i18n.saved, true);
						window.location.reload();
					} else {
						setResult((res && res.data && res.data.message) || aetGa4Dash.i18n.failed, false);
					}
				})
				.fail(function () {
					setResult(aetGa4Dash.i18n.failed, false);
				})
				.always(function () {
					$btn.prop('disabled', false);
				});
		});

		$(document).on('click', '#aet_ga4_dash_disconnect', function (e) {
			e.preventDefault();
			var $btn = $(this);
			$btn.prop('disabled', true);

			$.post(aetGa4Dash.ajaxurl, {
				action: 'aet_ga4_dashboard_disconnect',
				nonce: aetGa4Dash.nonce
			})
				.done(function () {
					window.location.href = aetGa4Dash.dashboardUrl || (window.location.pathname + '?page=aet-dashboard');
				})
				.fail(function () {
					setResult(aetGa4Dash.i18n.failed, false);
					$btn.prop('disabled', false);
				});
		});
	}

	initSetupHandlers();

	if (!aetGa4Dash.connected) {
		return;
	}

	var state = {
		data: null,
		charts: {},
		deviceTab: 'os',
		heatmapTab: 'sessions',
		pagesTab: 'top'
	};

	var sparkColors = {
		sessions: '#28a745',
		page_views: '#7c5cfc',
		avg_duration: '#f0a202',
		bounce_rate: '#e35d6a'
	};

	function formatNumber(n) {
		n = Number(n) || 0;
		return Math.round(n).toLocaleString();
	}

	function formatDuration(seconds) {
		seconds = Math.max(0, Math.round(Number(seconds) || 0));
		var m = Math.floor(seconds / 60);
		var s = seconds % 60;
		return m + 'm ' + (s < 10 ? '0' : '') + s + 's';
	}

	function formatPercent(n) {
		return (Math.round((Number(n) || 0) * 1000) / 10).toFixed(1) + '%';
	}

	function formatChange(change) {
		var val = Number(change) || 0;
		var cls = val >= 0 ? 'is-up' : 'is-down';
		var arrow = val >= 0 ? '▲' : '▼';
		return '<span class="' + cls + '">' + arrow + ' ' + Math.abs(val).toFixed(1) + '%</span> ' + aetGa4Dash.i18n.vs_previous;
	}

	function setDatesFromDefaults() {
		$('#aet-dash-start').val(aetGa4Dash.defaults.start);
		$('#aet-dash-end').val(aetGa4Dash.defaults.end);
		$('#aet-dash-cmp-start').val(aetGa4Dash.defaults.cmp_start);
		$('#aet-dash-cmp-end').val(aetGa4Dash.defaults.cmp_end);
	}

	function destroyChart(key) {
		if (state.charts[key]) {
			state.charts[key].destroy();
			delete state.charts[key];
		}
	}

	function renderSparkline(canvas, values, color) {
		if (!canvas || typeof Chart === 'undefined') {
			return;
		}
		var key = canvas.getAttribute('data-key') || Math.random().toString(36);
		canvas.setAttribute('data-key', key);
		destroyChart(key);
		state.charts[key] = new Chart(canvas.getContext('2d'), {
			type: 'line',
			data: {
				labels: (values || []).map(function (_, i) { return i; }),
				datasets: [{
					data: values || [],
					borderColor: color,
					backgroundColor: 'transparent',
					borderWidth: 2,
					pointRadius: 0,
					tension: 0.35
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { display: false }, tooltip: { enabled: false } },
				scales: {
					x: { display: false },
					y: { display: false }
				}
			}
		});
	}

	function renderKpis(kpis) {
		Object.keys(kpis || {}).forEach(function (key) {
			var card = document.querySelector('.aet-kpi-card[data-kpi="' + key + '"]');
			if (!card) {
				return;
			}
			var item = kpis[key] || {};
			var valueEl = card.querySelector('.aet-kpi-card__value');
			var changeEl = card.querySelector('.aet-kpi-card__change');
			var canvas = card.querySelector('canvas');

			if (key === 'avg_duration') {
				valueEl.textContent = formatDuration(item.value);
			} else if (key === 'bounce_rate') {
				valueEl.textContent = formatPercent(item.value);
			} else {
				valueEl.textContent = formatNumber(item.value);
			}
			changeEl.innerHTML = formatChange(item.change);
			renderSparkline(canvas, item.sparkline || [], sparkColors[key] || '#28a745');
		});
	}

	function renderDonut(data) {
		var canvas = document.getElementById('aet-donut-chart');
		if (!canvas || typeof Chart === 'undefined') {
			return;
		}
		destroyChart('donut');
		state.charts.donut = new Chart(canvas.getContext('2d'), {
			type: 'doughnut',
			data: {
				labels: [aetGa4Dash.i18n.new_users, aetGa4Dash.i18n.returning],
				datasets: [{
					data: [data.new || 0, data.returning || 0],
					backgroundColor: ['#3b82f6', '#28a745'],
					borderWidth: 0,
					hoverOffset: 2
				}]
			},
			options: {
				cutout: '68%',
				plugins: { legend: { display: false } }
			}
		});

		$('#aet-donut-legend').html(
			'<div class="aet-donut-legend__item">' +
				'<span class="aet-donut-legend__dot" style="background:#3b82f6"></span>' +
				'<div><div class="aet-donut-legend__name">' + aetGa4Dash.i18n.new_users + '</div>' +
				'<div class="aet-donut-legend__meta">' + formatNumber(data.new) + ' (' + (data.new_pct || 0) + '%)</div></div>' +
			'</div>' +
			'<div class="aet-donut-legend__item">' +
				'<span class="aet-donut-legend__dot" style="background:#28a745"></span>' +
				'<div><div class="aet-donut-legend__name">' + aetGa4Dash.i18n.returning + '</div>' +
				'<div class="aet-donut-legend__meta">' + formatNumber(data.returning) + ' (' + (data.ret_pct || 0) + '%)</div></div>' +
			'</div>'
		);
	}

	function renderDevices(tab) {
		var list = (state.data && state.data.devices && state.data.devices[tab]) ? state.data.devices[tab] : [];
		var max = 0;
		list.forEach(function (row) {
			max = Math.max(max, Number(row.value) || 0);
		});

		var html = list.map(function (row) {
			var pctWidth = max > 0 ? ((Number(row.value) || 0) / max) * 100 : 0;
			return '<div class="aet-hbar-row">' +
				'<div class="aet-hbar-row__name" title="' + $('<div>').text(row.name || '').html() + '">' + $('<div>').text(row.name || '').html() + '</div>' +
				'<div class="aet-hbar-row__track"><div class="aet-hbar-row__bar" style="width:' + pctWidth + '%"></div></div>' +
				'<div class="aet-hbar-row__meta">' + formatNumber(row.value) + ' (' + (row.pct || 0) + '%)</div>' +
			'</div>';
		}).join('');

		$('#aet-devices-list').html(html || '<p>—</p>');
	}

	function renderHeatmap(tab) {
		var payload = (state.data && state.data.heatmap && state.data.heatmap[tab]) ? state.data.heatmap[tab] : { cells: [], max: 0 };
		var max = Number(payload.max) || 0;
		var map = {};
		(payload.cells || []).forEach(function (c) {
			map[c.day + '-' + c.hour] = Number(c.value) || 0;
		});

		var days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
		var html = '<div class="aet-heatmap__corner"></div>';
		days.forEach(function (d) {
			html += '<div class="aet-heatmap__xlabel">' + d + '</div>';
		});

		for (var hour = 0; hour < 24; hour += 2) {
			var label = hour === 0 ? '12am' : (hour < 12 ? hour + 'am' : (hour === 12 ? '12pm' : (hour - 12) + 'pm'));
			html += '<div class="aet-heatmap__ylabel">' + label + '</div>';
			for (var day = 0; day < 7; day++) {
				var v1 = map[day + '-' + hour] || 0;
				var v2 = map[day + '-' + (hour + 1)] || 0;
				var val = v1 + v2;
				var intensity = max > 0 ? val / max : 0;
				var alpha = 0.15 + (intensity * 0.85);
				html += '<div class="aet-heatmap__cell" style="background:rgba(40,167,69,' + alpha.toFixed(2) + ')" title="' + formatNumber(val) + '"></div>';
			}
		}

		$('#aet-heatmap').html(html);
	}

	function renderPages(tab) {
		var list = (state.data && state.data.pages && state.data.pages[tab]) ? state.data.pages[tab] : [];
		var html = list.map(function (row) {
			return '<tr><td>' + $('<div>').text(row.name || '').html() + '</td><td>' + formatNumber(row.value) + '</td></tr>';
		}).join('');
		$('#aet-pages-body').html(html || '<tr><td colspan="2">—</td></tr>');
	}

	function renderChannels() {
		var list = (state.data && state.data.channels) ? state.data.channels : [];
		var html = list.map(function (row) {
			return '<tr><td>' + $('<div>').text(row.name || '').html() + '</td><td>' + formatNumber(row.value) + '</td><td>' + (row.pct || 0) + '%</td></tr>';
		}).join('');
		$('#aet-channels-body').html(html || '<tr><td colspan="3">—</td></tr>');
	}

	function renderAll(data) {
		state.data = data;
		renderKpis(data.kpis || {});
		renderDonut(data.new_vs_returning || {});
		renderDevices(state.deviceTab);
		renderHeatmap(state.heatmapTab);
		renderPages(state.pagesTab);
		renderChannels();
	}

	function loadOverview() {
		$('#aet-dashboard-loading').show();
		$('#aet-dashboard-error').prop('hidden', true).text('');
		$('#aet-dashboard-content').prop('hidden', true);

		$.post(aetGa4Dash.ajaxurl, {
			action: 'aet_ga4_dashboard_overview',
			nonce: aetGa4Dash.nonce,
			start: $('#aet-dash-start').val(),
			end: $('#aet-dash-end').val(),
			cmp_start: $('#aet-dash-cmp-start').val(),
			cmp_end: $('#aet-dash-cmp-end').val()
		})
			.done(function (res) {
				if (res && res.success && res.data) {
					renderAll(res.data);
					$('#aet-dashboard-content').prop('hidden', false);
				} else {
					$('#aet-dashboard-error').prop('hidden', false).text((res && res.data && res.data.message) || aetGa4Dash.i18n.failed);
				}
			})
			.fail(function () {
				$('#aet-dashboard-error').prop('hidden', false).text(aetGa4Dash.i18n.failed);
			})
			.always(function () {
				$('#aet-dashboard-loading').hide();
			});
	}

	$(function () {
		setDatesFromDefaults();
		loadOverview();

		$('#aet-dash-refresh').on('click', function (e) {
			e.preventDefault();
			loadOverview();
		});

		$(document).on('click', '.aet-tabs button', function () {
			var $btn = $(this);
			var group = $btn.closest('.aet-tabs').data('tabs');
			$btn.addClass('is-active').siblings().removeClass('is-active');
			var tab = $btn.data('tab');
			if (group === 'devices') {
				state.deviceTab = tab;
				renderDevices(tab);
			} else if (group === 'heatmap') {
				state.heatmapTab = tab;
				renderHeatmap(tab);
			} else if (group === 'pages') {
				state.pagesTab = tab;
				renderPages(tab);
			}
		});
	});
})(jQuery);
