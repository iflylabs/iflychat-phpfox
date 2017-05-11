$(document).ready(function() {
	$('select[name="val[value][path_visibility][value]"]').change(function() {
		if($('select[name="val[value][path_visibility][value]"]').val() == 'Only the listed pages') {
			$('textarea[name="val[value][path_pages]"]').val('/*');
		}
		else {
			$('textarea[name="val[value][path_pages]"]').val('');
		}
	});
	$('<div id="js_form_msg"></div>').insertBefore($('#api_key').parent());
	$('#api_key').parent().attr('onsubmit', 'return Validation_js_form();');
});

function Validation_js_form() {
	$('#js_form_msg').hide('');
	$('#js_form_msg').html('');
	var bIsValid = true;
	var bReturn = false;

	if (($('select[name="val[value][path_visibility][value]"]').val() == 'Only the listed pages') && ($('textarea[name="val[value][path_pages]"]').val() == ''))
	{
		bIsValid = false; 
		$('#js_form_msg').message('Specify path pages.', 'error');
		$('textarea[name="val[value][path_pages]"]').addClass('alert_input');
	}

	if (bReturn) return false;
	 if ( bIsValid ) { return true; } $('#js_form_msg').show(); window.location.hash = '#'; return false;
}