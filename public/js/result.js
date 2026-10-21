// The three bar charts of the result page, from the JSON the page embeds.
(function () {
	var data = JSON.parse(document.getElementById('chart-data').textContent);
	var blue = 'rgba(0, 99, 132, 0.6)';

	function bars(id, label, values, colors) {
		new Chart(document.getElementById(id), {
			type: 'bar',
			data: {
				labels: data.labels,
				datasets: [{label: label, data: values, backgroundColor: colors, borderWidth: 2}]
			},
			options: {indexAxis: 'y'}
		});
	}

	bars('chart-progress', 'Grafik Progress', data.progress, blue);
	bars('chart-potential', 'Grafik Potensi', data.potential, data.potentialColors);
	bars('chart-reference', 'Computer dan IT', data.reference, blue);
})();
