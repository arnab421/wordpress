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
**************Latest Query 04-08-2024*************************
<?php
                // $args1 = array(
                //     'post_type' => 'services',
                //     'posts_per_page' => -1,
                //     'post_status' => 'publish',
                //     'order' => 'DESC',
                //     'tax_query' => array(
                //         'relation' => 'AND', 
                //         array(
                //             'taxonomy' => 'service_type', 
                //             'field'    => 'slug',
                //             'terms'    => 'cattwo', 
                //         ),
                //         array(
                //             'taxonomy' => 'service_tag', 
                //             'field'    => 'slug',
                //             'terms'    => 'tagtwo', 
                //         ),
                //     ),
                //     'meta_query' => array(
                //         array(
                //             'key'     => 'service_status', 
                //             'value'   => 'open', 
                //             'compare' => '=', 
                //         ),
                //     ),
                // );
                // $services1 = new WP_Query($args1);
                // echo "<pre>";
                // print_r($services1);
                // echo "</pre>";
                // die();
                // Initial query arguments
                $args1 = array(
                    'post_type' => 'services',
                    'posts_per_page' => -1,
                    'post_status' => 'publish',
                    'order' => 'DESC',
                    'tax_query' => array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => 'service_type',
                            'field'    => 'slug',
                            'terms'    => 'cattwo',
                        ),
                        array(
                            'taxonomy' => 'service_tag',
                            'field'    => 'slug',
                            'terms'    => 'tagtwo',
                        ),
                    ),
                    'meta_query' => array(
                        array(
                            'key'     => 'service_status',
                            'value'   => 'open',
                            'compare' => '=',
                        ),
                    ),
                );

                // Run the first query
                $query1 = new WP_Query($args1);

                // Check if there are posts for the first query
                if ($query1->have_posts()) {
                    // Posts exist with the initial criteria
                    $posts = $query1->posts;
                } else {
                    // No posts found with the initial criteria, so run the fallback query
                    $args2 = array(
                        'post_type' => 'services',
                        'posts_per_page' => -1,
                        'post_status' => 'publish',
                        'order' => 'DESC',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'service_type',
                                'field'    => 'slug',
                                'terms'    => 'cattwo',
                            ),
                        ),
                        'meta_query' => array(
                            array(
                                'key'     => 'service_status',
                                'value'   => 'open',
                                'compare' => '=',
                            ),
                        ),
                    );

                    // Run the fallback query
                    $query2 = new WP_Query($args2);
                    $posts = $query2->posts;
                }

                // Process the posts (if any)
                foreach ($posts as $post) {
                    // Output or process each post
                    setup_postdata($post);
                    // Example: Output the title
                    echo '<h2>' . get_the_title($post->ID) . '</h2>';
                }
                wp_reset_postdata();
                ?>
***************Latest Query 04-08-2024************************
