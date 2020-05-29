$(function () {
	$.nette.ext('live').after(function ($element) {
		$element.find('.js-input-currency').each(function () {
			new AutoNumeric(this, JSON.parse(this.dataset.options));
		});
	});
})