// The result page's chart: the student's potential per section next to the
// "Computers and IT" reference profile, from the JSON the page embeds. Colours
// come from the stylesheet's tokens, so the chart follows light and dark mode.
(function () {
	var data = JSON.parse(document.getElementById('chart-data').textContent);
	var canvas = document.getElementById('chart-potential');
	var css = getComputedStyle(document.documentElement);

	function token(name) {
		return css.getPropertyValue(name).trim();
	}

	function series(label, values, color) {
		return {
			label: label, data: values, backgroundColor: color, borderColor: token('--surface'),
			borderWidth: {right: 2, top: 1, bottom: 1}, borderRadius: 4, borderSkipped: 'start',
			maxBarThickness: 12, categoryPercentage: 0.7, barPercentage: 0.9
		};
	}

	Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
	Chart.defaults.color = token('--muted');

	new Chart(canvas, {
		type: 'bar',
		data: {
			labels: data.labels,
			datasets: [
				series('Your potential', data.potential, token('--series-1')),
				series('Computers and IT', data.reference, token('--series-2'))
			]
		},
		options: {
			indexAxis: 'y',
			maintainAspectRatio: false,
			interaction: {mode: 'index', axis: 'y', intersect: false},
			scales: {
				x: {beginAtZero: true, suggestedMax: 10, border: {display: false},
					grid: {color: token('--line')}, ticks: {stepSize: 2}},
				y: {grid: {display: false}, border: {color: token('--line')},
					ticks: {color: token('--ink-2'), font: {weight: 600}}}
			},
			plugins: {
				legend: {position: 'top', align: 'start',
					labels: {usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 10, boxHeight: 10,
						color: token('--ink-2')}},
				tooltip: {backgroundColor: token('--surface'), titleColor: token('--ink'),
					bodyColor: token('--ink-2'), borderColor: token('--line'), borderWidth: 1,
					padding: 10, boxPadding: 4, usePointStyle: true}
			}
		}
	});
})();
