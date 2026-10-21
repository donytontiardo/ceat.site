// Counts down the seconds the server says are left in this section, then moves
// on. The server keeps the real deadline: answers after it are not recorded.
(function () {
	var el = document.getElementById('timer');
	if (!el) {
		return;
	}
	var deadline = Date.now() + parseInt(el.dataset.seconds, 10) * 1000;
	var next = el.dataset.next;

	function pad(n) {
		return (n < 10 ? '0' : '') + n;
	}

	function tick() {
		var left = Math.max(0, Math.round((deadline - Date.now()) / 1000));
		el.textContent = pad(Math.floor(left / 60)) + ' : ' + pad(left % 60);
		if (left === 0) {
			window.location.href = next;
			return;
		}
		setTimeout(tick, 250);
	}
	tick();
})();
