import $ from 'jquery';
import 'bootstrap';
import 'bootstrap-datepicker';
import 'bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
import '../css/global.scss';
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.js';
// To be used on non-react select when proper (for example, a multi-select with a lot of options..)
import 'select2';
import 'select2/dist/css/select2.css';

window.$ = $;

$(document).ready(() => {
	$('[data-toggle="popover"]').popover();

	$('.js-datepicker').datepicker({
		format: 'yyyy-mm-dd'
	});

	$('.js-datepicker.view-mode-months').datepicker( {
		format: 'mm-yyyy',
		viewMode: 'months',
		minViewMode: 'months'
	});

});