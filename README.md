# wordpress
wordpress template
template 
<?php /*Template Name: Layout: Test-Page*/?>
<?php get_header(); ?> 
<h1>Test Page</h1>
<form id="custom-form">
        <input type="text" name="name" placeholder="Your Name" required />
        <input type="email" name="email" placeholder="Your Email" required />
        <div class="g-recaptcha" data-sitekey=""></div>
        <button type="submit">Submit</button>
        <div id="form-message"></div>
    </form>
<?php get_footer(); ?> 

functions
function handle_custom_form() {
		if (isset($_POST['recaptcha_response']) && !empty($_POST['recaptcha_response'])) {
			// wp_send_json_error(array('message' => 'reCAPTCHA is required.'));
			$secretKey = '';
			$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$_POST['recaptcha_response']);
            $response = json_decode($verifyResponse);
			// print_r($response);
			// die();
			// wp_send_json($response);
			// if($response->success == 1){
			// 	global $wpdb;
			// 	$table_name = $wpdb->prefix . 'custom_table';
			
			// 	$data = array(
			// 		'name' => sanitize_text_field($_POST['form_data']['name']),
			// 		'email' => sanitize_email($_POST['form_data']['email'])
			// 	);
			//     print_r($data);
			// 	die();
			// 	$wpdb->insert($table_name, $data);
			// 	wp_send_json($response);
			//  } 
			 wp_send_json($response);
		} 
		else{
			wp_send_json_error(array('message' => 'reCAPTCHA verification failed.'));
		}
	
		// $recaptcha_secret = '';
		// $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
		// $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify", array(
		// 	'body' => array(
		// 		'secret' => $recaptcha_secret,
		// 		'response' => $recaptcha_response
		// 	)
		// ));
		// $response_body = wp_remote_retrieve_body($response);
		// $result = json_decode($response_body);
		
		// if (!$result->success) {
		// 	wp_send_json_error(array('message' => 'reCAPTCHA verification failed.'));
			
		// }
	  
		// // Process the form data
		// global $wpdb;
		// $table_name = $wpdb->prefix . 'custom_table';
	
		// $data = array(
		// 	'name' => sanitize_text_field($_POST['form_data']['name']),
		// 	'email' => sanitize_email($_POST['form_data']['email'])
		// );
	
		// $wpdb->insert($table_name, $data);
	wp_die();
		
	}
	add_action('wp_ajax_handle_custom_form', 'handle_custom_form');
	add_action('wp_ajax_nopriv_handle_custom_form', 'handle_custom_form');

 js file
jQuery(document).ready(function($) {
    $('#custom-form').submit(function(event) {
        event.preventDefault();

        var formData = $(this).serialize();
        var recaptchaResponse = grecaptcha.getResponse();
        console.log(recaptchaResponse);
        if (recaptchaResponse.length === 0) {
            $('#form-message').text('Please complete the reCAPTCHA.');
            return;
        }

        $.ajax({
            type: 'POST',
            url: ajaxadmin.ajax_url,
            data: {
                action: 'handle_custom_form',
                form_data: formData,
                recaptcha_response: recaptchaResponse
            },
            success: function(response) {
                console.log('AJAX response:', response); // Log the response
                if (response.success) {
                    $('#custom-form')[0].reset();
                     grecaptcha.reset();
                    $('#form-message').text('Form submitted successfully.');
                } else {
                    // console.log(response);
                    $('#form-message').text('Form submission failed12234.');
                }
            }
        });
    });
});

