// Small helps while typing an answer. Answers are marked case-sensitively, so
// fields with data-case are kept in the case the instructions ask for, and a
// letter box (data-advance) moves on to the next box once a letter is typed.
(function () {
	var fields = Array.prototype.slice.call(document.querySelectorAll('input[data-case]'));

	fields.forEach(function (field, i) {
		field.addEventListener('input', function () {
			var value = field.dataset.case === 'upper' ? field.value.toUpperCase() : field.value.toLowerCase();
			if (value !== field.value) {
				field.value = value;
			}
			if (field.hasAttribute('data-advance') && value.trim().length >= 1 && fields[i + 1]) {
				fields[i + 1].focus();
				fields[i + 1].select();
			}
		});
		field.addEventListener('keydown', function (event) {
			if (event.key === 'Backspace' && field.value === '' && field.hasAttribute('data-advance') && i > 0) {
				fields[i - 1].focus();
			}
		});
	});
})();
