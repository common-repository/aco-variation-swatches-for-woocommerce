// Deactivation Form
jQuery(document).ready(function() { 

    jQuery(document).on("click", function(e) {
        let popup = document.getElementById('vsw-survey-form');
        let overlay = document.getElementById('vsw-survey-form-wrap');
        let openButton = document.getElementById('deactivate-aco-variation-swatches-for-woocommerce'); 
        if(e.target.id == 'vsw-survey-form-wrap'){
            vswClose();
        }
        if(e.target === openButton){ 
            e.preventDefault();
            popup.style.display = 'block';
            overlay.style.display = 'block';
        }
        if(e.target.id == 'vsw_skip'){ 
            e.preventDefault();
            let urlRedirect = document.querySelector('a#deactivate-aco-variation-swatches-for-woocommerce').getAttribute('href');
            window.location = urlRedirect;
        }
        if(e.target.id == 'vsw_cancel'){ 
            e.preventDefault();
            vswClose();
        }
    });

	function vswClose() {
		let popup = document.getElementById('vsw-survey-form');
        let overlay = document.getElementById('vsw-survey-form-wrap');
		popup.style.display = 'none';
		overlay.style.display = 'none';
		jQuery('#vsw-survey-form form')[0].reset();
		jQuery("#vsw-survey-form form .vsw-comments").hide();
		jQuery('#vsw-error').html('');
	}

    jQuery("#vsw-survey-form form").on('submit', function(e) {
        e.preventDefault();
        let valid = vswValidate();
		if (valid) {
            jQuery('#vsw_deactivate').prop('disabled', true);
            let urlRedirect = document.querySelector('a#deactivate-aco-variation-swatches-for-woocommerce').getAttribute('href');
            let form = jQuery(this);
            let serializeArray = form.serializeArray();
            let actionUrl = 'https://feedback.acowebs.com/plugin.php';
            jQuery.ajax({
                type: "post",
                url: actionUrl,
                data: serializeArray,
                contentType: "application/javascript",
                dataType: 'jsonp',
                success: function(data)
                {
                    window.location = urlRedirect;
                },
                error: function (jqXHR, textStatus, errorThrown) { 
                    window.location = urlRedirect;
                }
            });
        }
    });

    jQuery('#vsw-survey-form .vsw-comments textarea').on('keyup', function () {
		vswValidate();
	});

    jQuery("#vsw-survey-form form input[type='radio']").on('change', function(){
        vswValidate();
        let val = jQuery(this).val();
        if ( val == 'I found a bug' || val == 'Plugin suddenly stopped working' || val == 'Plugin broke my site' || val == 'Other' || val == 'Plugin doesn\'t meets my requirement' ) {
            jQuery("#vsw-survey-form form .vsw-comments").show();
        } else {
            jQuery("#vsw-survey-form form .vsw-comments").hide();
        }
    });

    function vswValidate() {
		let error = '';
		let reason = jQuery("#vsw-survey-form form input[name='Reason']:checked").val();
		if ( !reason ) {
			error += 'Please select your reason for deactivation';
		}
		if ( error === '' && ( reason == 'I found a bug' || reason == 'Plugin suddenly stopped working' || reason == 'Plugin broke my site' || reason == 'Other' || reason == 'Plugin doesn\'t meets my requirement' ) ) {
			let comments = jQuery('#vsw-survey-form .vsw-comments textarea').val();
			if (comments.length <= 0) {
				error += 'Please specify';
			}
		}
		if ( error !== '' ) {
			jQuery('#vsw-error').html(error);
			return false;
		}
		jQuery('#vsw-error').html('');
		return true;
	}

});