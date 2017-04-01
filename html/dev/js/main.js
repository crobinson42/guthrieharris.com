$(document).ready(function () {
	$('.awards-row').on('mouseover', function() {
		$(this).css({ opacity: '1' })
	})
	$('.awards-row').on('mouseout', function() {
		$(this).css({ opacity: '.2' })
	})

	//contact form logic
	var unlockSubmit_isMouseOver = false
	var $submitButton = $('#contactSubmitContainer')
	$submitButton.on('mouseover', function() {
		unlockSubmit_isMouseOver = true
		setTimeout(function() {
			if (unlockSubmit_isMouseOver) {
				$submitButton.find('button').attr({ disabled: false })
			}
		},500)
	})
	$submitButton.on('mouseout', function() {
		unlockSubmit_isMouseOver = false
	})
})

$( '[data-form="contact"]' ).on( "submit", function( event ) {
	event.preventDefault();
	var name = $(this).find('#inputName').val(),
		phone = $(this).find('#inputName2').val(),
		email = $(this).find('#inputEmail').val(),
		comment = $(this).find('#textarea').val()

	$.post('/email_contact.php', {
		name: name,
		email: email,
		phone: phone,
		comment: comment,
	})
		.success(function (res,data) {
			var $submitButton = $('#contactSubmitContainer')
			$submitButton.after('<p>Thanks! We\'ll contact you soon!</p>')
			$submitButton.remove();
		})
		.fail(function (res) {
			console.log(res)
		})
});
