/* global $ */

$(() => {
	$('#save-employee').on('click', () => {
		$('form[name="employee"]').submit();
	});

	$('.delete-collection-item').on('click', (e) => {
		$(e.target).closest('.collection-item').remove();
	});

	const prototypeAddItem = (callback) => (e) => {
		let $list = $($(e.target).attr('data-list'));
		// Try to find the counter of the list or use the length of the list
		let counter = $list.data('widget-counter') | $list.children().length;
		let newWidget = $list.attr('data-prototype');
		// replace the "__name__" used in the id and name of the prototype
		newWidget = newWidget.replace(/__name__/g, counter);
		// Increase the counter
		counter++;
		// And store it, the length cannot be used if deleting widgets is allowed
		$list.data('widget-counter', counter);

		// create a new list element and add it to the list
		let $newElem = $(newWidget);
		$newElem.appendTo($list);

		if (callback instanceof Function) {
			callback($newElem);
		}
	};

	$('#add-skill').on('click', prototypeAddItem());
	$('#add-address').on('click', prototypeAddItem());
	$('#add-payment').on('click', prototypeAddItem(($item) => {
		$item.find('.js-datepicker.view-mode-months').datepicker( {
			format: 'yyyy-mm-dd',
			viewMode: 'months',
			minViewMode: 'months'
		});
	}));
});
