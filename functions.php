<?php

ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/my-debug.log');

define('MBWVERSION', rand());
include_once('settings/theme-settings.php');
include_once('settings/sendEmail.php');

//loading styles and scripts
add_action('wp_enqueue_scripts', 'MBWScripts');
function MBWScripts()
{
    // Enqueue CSS
    wp_enqueue_style('main-style', get_template_directory_uri() . '/assets/dart-scss/style.css', array(), MBWVERSION, 'all');
    wp_enqueue_style('smooth-scrollbar-style', get_template_directory_uri() . '/assets/dart-scss/smooth-scrollbar.css', array(), MBWVERSION, 'all');
    wp_enqueue_style('swiper-style', 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css', array(), null, 'all');
    // Enqueue JS
    wp_enqueue_script('jquery', get_template_directory_uri() . '/assets/js/jquery.js', __FILE__, ['jquery'], true);
    wp_enqueue_script('jquery-validation', get_template_directory_uri() . '/assets/js/jquery-validator.js', __FILE__, ['jquery'], '1.19.5');
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.7.0.min.js', array(), null, true);
    wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', array(), null, true);
    wp_enqueue_script('scrolltrigger', get_template_directory_uri() . '/assets/js/ScrollTrigger.min.js', array(), MBWVERSION, true);
    wp_enqueue_script('drawsvg', get_template_directory_uri() . '/assets/js/DrawSVGPlugin.min.js', array(), MBWVERSION, true);
    wp_enqueue_script('motionpath', get_template_directory_uri() . '/assets/js/MotionPathPlugin.min.js', array(), MBWVERSION, true);
    wp_enqueue_script('frontend', get_template_directory_uri() . '/assets/js/frontend.js', array(), MBWVERSION, true);
    wp_enqueue_script('smooth-scrollbar', get_template_directory_uri() . '/assets/js/smooth-scrollbar.js', array('jquery'), MBWVERSION, true);
    wp_enqueue_script('splittext', get_template_directory_uri() . '/assets/js/SplitText.min.js', array(), MBWVERSION, true);
    wp_enqueue_script('swiper', 'https://cdnjs.cloudflare.com/ajax/libs/Swiper/6.8.4/swiper-bundle.min.js', array(), null, true);
    wp_enqueue_script('functions', get_stylesheet_directory_uri() . '/assets/js/functions.js', array(), MBWVERSION, true);
    // wp_localize_script('jquery', 'myAjax', array('ajax_url' => admin_url('admin-ajax.php')));
    wp_enqueue_script('lenis', 'https://unpkg.com/@studio-freight/lenis@1.0.33/dist/lenis.min.js', array(), null, true);
    // wp_enqueue_script('functions', get_template_directory_uri() . '/assets/js/functions.js', array(), MBWVERSION, true);
    // wp_localize_script('jquery', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

    // wp_enqueue_script('functions',  get_template_directory_uri() . '/assets/js/functions.js', array(), MBWVERSION, true);
}

//Navigation menus 
register_nav_menus([
    'header_menu' => 'Header Menu',
    'footer_menu' => 'Footer Menu',
    'footer_menu_one' => 'Footer Menu One',
    'footer_menu_two' => 'Footer Menu Two',
    'footer_menu_three' => 'Footer Menu Three',
    'footer_menu_four' => 'Footer Menu Four'
]);

//SMTP Settings
// function wpse8170_phpmailer_init(PHPMailer $phpmailer)
// {
// 	$phpmailer->isMail();
// 	$phpmailer->Host = 'smtp.sundewlabs.com';
// 	$phpmailer->Port = 587;
// 	$phpmailer->Username = 'development@sundewlabs.com';
// 	$phpmailer->Password = '(0}ZFd!SO@vo';
// 	$phpmailer->SMTPAuth = true;
// 	$phpmailer->SMTPSecure = 'tls';
// }

// add_filter('wp_mail_from_name', 'new_mail_from_name');

add_action('phpmailer_init', function ($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = 'localhost';
    $phpmailer->Port = 1025;
    $phpmailer->SMTPAuth = false;
    $phpmailer->SMTPSecure = '';
    $phpmailer->From = 'test@example.com';
    $phpmailer->FromName = 'Local WordPress';
});

add_filter('wp_mail_from', function ($email) {
    return 'noreply@example.com';
});

add_filter('wp_mail_from_name', 'new_mail_from_name');
function new_mail_from_name($old)
{
    return 'Marble Box Landing Page';
}

//Allow Span tags in editor
function myextensionTinyMCE($init)
{
    // Command separated string of extended elements
    $ext = 'span[id|name|class|style]';

    // Add to extended_valid_elements if it alreay exists
    if (isset($init['extended_valid_elements'])) {
        $init['extended_valid_elements'] .= ',' . $ext;
    } else {
        $init['extended_valid_elements'] = $ext;
    }

    // Super important: return $init!
    return $init;
}
add_filter('tiny_mce_before_init', 'myextensionTinyMCE');

// To upload photos in format WebP
function wpdocs_custom_mime_types($mimes)
{
    // New allowed mime types.
    $mimes['webp'] = 'image/webp';
    return $mimes;
}

add_filter('upload_mimes', 'wpdocs_custom_mime_types');

// Adding A class to anchor tag of footer menu
function add_additional_class_on_a($classes, $item, $args)
{
    if (isset($args->add_a_class)) {
        $classes['class'] = $args->add_a_class;
    }
    return $classes;
}

add_filter('nav_menu_link_attributes', 'add_additional_class_on_a', 1, 3);

//Forgot PSW Limit added -->
function limit_forgot_password_attempts()
{
    session_start();

    $max_attempts = 3;
    $lockout_time = 15 * 60; // 15 minutes

    if (!isset($_SESSION['forgot_password_attempts'])) {
        $_SESSION['forgot_password_attempts'] = [
            'count' => 0,
            'time' => time()
        ];
    }

    $attempts = &$_SESSION['forgot_password_attempts'];

    if (time() - $attempts['time'] > $lockout_time) {
        $attempts['count'] = 0;
        $attempts['time'] = time();
    }

    if ($attempts['count'] >= $max_attempts) {
        wp_die(__('Too many forgot password attempts. Please try again later.'));
    }
}

add_action('lostpassword_post', function () {
    session_start();

    if (!isset($_SESSION['forgot_password_attempts'])) {
        $_SESSION['forgot_password_attempts'] = [
            'count' => 0,
            'time' => time()
        ];
    }

    $attempts = &$_SESSION['forgot_password_attempts'];

    $attempts['count']++;
    $attempts['time'] = time();
});

add_action('login_form_lostpassword', 'limit_forgot_password_attempts');


//Disable Link
function disable_embeds_code_init()
{
    // Remove the REST API endpoint.
    remove_action('rest_api_init', 'wp_oembed_register_route');

    // Turn off oEmbed auto discovery.
    add_filter('embed_oembed_discover', '__return_false');

    // Don't filter oEmbed results.
    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

    // Remove oEmbed discovery links.
    remove_action('wp_head', 'wp_oembed_add_discovery_links');

    // Remove oEmbed-specific JavaScript from the front-end and back-end.
    remove_action('wp_head', 'wp_oembed_add_host_js');
    add_filter('tiny_mce_plugins', 'disable_embeds_tiny_mce_plugin');

    // Remove all embeds rewrite rules.
    add_filter('rewrite_rules_array', 'disable_embeds_rewrites');
}
add_action('init', 'disable_embeds_code_init', 9999);

function disable_embeds_tiny_mce_plugin($plugins)
{
    return array_diff($plugins, array('wpembed'));
}

function disable_embeds_rewrites($rules)
{
    foreach ($rules as $rule => $rewrite) {
        if (false !== strpos($rewrite, 'embed=true')) {
            unset($rules[$rule]);
        }
    }
    return $rules;
}

// Disable Rest API link
function disable_rest_endpoints($endpoints)
{
    if (isset($endpoints['/oembed/1.0/embed'])) {
        unset($endpoints['/oembed/1.0/embed']);
    }
    return $endpoints;
}
add_filter('rest_endpoints', 'disable_rest_endpoints');

add_action('send_headers', 'add_x_frame_options_header');
function add_x_frame_options_header()
{
    header('X-Frame-Options: SAMEORIGIN');
}

add_action('send_headers', 'add_csp_frame_ancestors_header');
function add_csp_frame_ancestors_header()
{
    header("Content-Security-Policy: frame-ancestors 'self'");
}

function dm_remove_wp_block_library_css()
{
    wp_dequeue_style('wp-block-library');
}
add_action('wp_enqueue_scripts', 'dm_remove_wp_block_library_css');


// Remove Jquery Min //
function buffer_and_remove_script()
{
    ob_start(function ($buffer) {
        // Get the current site URL
        $site_url = get_site_url();

        // Define the pattern to remove the script
        $pattern = sprintf(
            '/<script[^>]*src="%s\/wp-includes\/js\/jquery\/jquery.min.js[^"]*"[^>]*><\/script>/i',
            preg_quote($site_url, '/')
        );

        // Remove the script tag from the buffer
        $buffer = preg_replace($pattern, '', $buffer);

        return $buffer;
    });
}
add_action('wp_head', 'buffer_and_remove_script', 0);

// Remove Jquery Migrate //
function isa_remove_jquery_migrate(&$scripts)
{
    if (!is_admin()) {
        $scripts->remove('jquery');
        $scripts->add('jquery', false, array('jquery-core'), '1.12.4');
    }
}
add_filter('wp_default_scripts', 'isa_remove_jquery_migrate');

function enqueue_intl_tel_input()
{
    // Enqueue the intl-tel-input CSS
    wp_enqueue_style('intl-tel-input-css', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css');

    // Enqueue the intl-tel-input JS
    wp_enqueue_script('intl-tel-input-js', 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js', array('jquery'), null, true);

    $random_version = rand();
    // Enqueue your custom script
    wp_enqueue_script('custom-form-validation', get_stylesheet_directory_uri() . '/assets/js/custom-form-validation.js', array('intl-tel-input-js'), $random_version, null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_intl_tel_input');

function custom_cf7_radio_fix_css()
{
    echo '<style>
    .wpcf7-list-item input[type="radio"] {
      display: inline-block !important;
      opacity: 1 !important;
      position: static !important;
      visibility: visible !important;
      margin-right: 6px;
      vertical-align: middle;
    }
    </style>';
}
add_action('wp_head', 'custom_cf7_radio_fix_css');


// Contact Form Default email skip
add_filter('wpcf7_skip_mail', function ($skip_mail, $cf7) {
    return $cf7->id() == 17 ? true : $skip_mail;
}, 10, 2);
// Contact Form Default email skip


add_action('wpcf7_mail_sent', 'send_custom_email_after_quiz_submit');

function send_custom_email_after_quiz_submit($cf7)
{
    if ($cf7->id() != 17)
        return;
    $submission = WPCF7_Submission::get_instance();
    if (!$submission)
        return;

    $data = $submission->get_posted_data();

    $user_email = $data['your-email'] ?? '';
    $total_score = $data['total-marks'] ?? '';
    $grade = $data['grade-result'] ?? '';
    $section1 = $data['section1-score'] ?? '';
    $section2 = $data['section2-score'] ?? '';
    $section3 = $data['section3-score'] ?? '';
    $section4 = $data['section4-score'] ?? '';
    $response1 = $data['sec1-response'] ?? '';
    $response2 = $data['sec2-response'] ?? '';
    $response3 = $data['sec3-response'] ?? '';
    $response4 = $data['sec4-response'] ?? '';
    $overall = $data['overall-response'] ?? '';

    $q1 = is_array($data['q1']) ? implode(', ', $data['q1']) : ($data['q1'] ?? '');
    $q2 = is_array($data['q2']) ? implode(', ', $data['q2']) : ($data['q2'] ?? '');
    $q3 = is_array($data['q3']) ? implode(', ', $data['q3']) : ($data['q3'] ?? '');
    $q4 = is_array($data['q4']) ? implode(', ', $data['q4']) : ($data['q4'] ?? '');
    $q5 = is_array($data['q5']) ? implode(', ', $data['q5']) : ($data['q5'] ?? '');
    $q6 = is_array($data['q6']) ? implode(', ', $data['q6']) : ($data['q6'] ?? '');
    $q7 = is_array($data['q7']) ? implode(', ', $data['q7']) : ($data['q7'] ?? '');
    $q8 = is_array($data['q8']) ? implode(', ', $data['q8']) : ($data['q8'] ?? '');
    $q9 = is_array($data['q9']) ? implode(', ', $data['q9']) : ($data['q9'] ?? '');
    $q10 = is_array($data['q10']) ? implode(', ', $data['q10']) : ($data['q10'] ?? '');

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: Your Site <noreply@example.com>'
    ];

    // User email content
    $user_subject = 'Your Score and AI Readiness Report';
    $user_message = <<<EOT
Hi,

Thank you for completing the AI Readiness Quiz!

Here is your personalized report:

Section Scores:
- Process: $section1
- Success Criteria: $section2
- Data Readiness: $section3
- AI Maturity: $section4

AI Insights:
- Process Feedback: $response1
- Success Criteria Feedback: $response2
- Data Readiness Feedback: $response3
- AI Maturity Feedback: $response4
- Overall Feedback: $overall

Total Marks: $total_score
Grade: $grade

Thanks again!
EOT;

    // Admin email content
    $admin_email = 'arnab.das@sundewsolutions.com'; // Static email
    $admin_subject = 'New Quiz Submission - ' . $user_email;
    $admin_message = <<<EOT
New submission received from: $user_email

Answers:
1. $q1
2. $q2
3. $q3
4. $q4
5. $q5
6. $q6
7. $q7
8. $q8
9. $q9
10. $q10

Section Scores:
- Process: $section1
- Success Criteria: $section2
- Data Readiness: $section3
- AI Maturity: $section4

AI Insights:
- Process Feedback: $response1
- Success Criteria Feedback: $response2
- Data Readiness Feedback: $response3
- AI Maturity Feedback: $response4
- Overall Feedback: $overall

Total Marks: $total_score
Grade: $grade
EOT;

    // Send emails
    if (!empty($user_email)) {
        wp_mail($user_email, $user_subject, $user_message, $headers);
    }

    wp_mail($admin_email, $admin_subject, $admin_message, $headers);
}
add_action('wpcf7_mail_sent', 'send_custom_email_after_quiz_submit');


function create_faculty_role()
{
    add_role('faculty', 'Faculty', [
        'read' => true,
        'edit_posts' => false,
        'delete_posts' => false,
        'edit_profile' => true,
    ]);
}
add_action('init', 'create_faculty_role');

function restrict_faculty_user_access()
{
    if (is_admin() && current_user_can('faculty') && !current_user_can('administrator')) {
        global $pagenow;

        // Prevent access to users list
        if ($pagenow == 'users.php') {
            wp_redirect(admin_url('profile.php'));
            exit;
        }

        // Prevent access to other users' profiles
        if ($pagenow == 'user-edit.php' || $pagenow == 'profile.php') {
            if (isset($_GET['user_id']) && get_current_user_id() != $_GET['user_id']) {
                wp_redirect(admin_url('profile.php'));
                exit;
            }
        }
    }
}
add_action('admin_init', 'restrict_faculty_user_access');

function hide_users_menu_for_faculty()
{
    if (current_user_can('faculty') && !current_user_can('administrator')) {
        remove_menu_page('users.php');
    }
}
add_action('admin_menu', 'hide_users_menu_for_faculty');

function customize_admin_bar_for_faculty($wp_admin_bar)
{
    if (current_user_can('faculty') && !current_user_can('administrator')) {
        $wp_admin_bar->remove_node('new-user');
        $wp_admin_bar->remove_node('edit-profile'); // Optional
    }
}
add_action('admin_bar_menu', 'customize_admin_bar_for_faculty', 999);


function enqueue_custom_tooltip_css()
{

    wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet/dist/leaflet.css');
    wp_add_inline_style('leaflet-css', '
        .leaflet-tooltip.custom-tooltip {
            font-size: 15px !important;
            padding: 12px !important;
            background-color: #fff !important;
            color: #000 !important;
            border: 2px solid #444 !important;
            border-radius: 8px !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15) !important;
            width:200px !important;
            height:200px !important;
            white-space: normal !important;
            z-index: 10000 !important;
            line-height: 1.4 !important;
        }
        .leaflet-tooltip.custom-tooltip img {
            display: block !important;
            width:200px !important;
            height: auto !important;
            border-radius: 5px !important;
            margin-top: 8px !important;
        }
    ');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_tooltip_css');


function register_location_post_type_and_taxonomy()
{
    // Register Custom Post Type: Location
    register_post_type('location', array(
        'labels' => array(
            'name' => 'Locations',
            'singular_name' => 'Location',
            'add_new' => 'Add New Location',
            'add_new_item' => 'Add New Location',
            'edit_item' => 'Edit Location',
            'new_item' => 'New Location',
            'view_item' => 'View Location',
            'search_items' => 'Search Locations',
            'not_found' => 'No locations found',
            'not_found_in_trash' => 'No locations found in Trash',
            'all_items' => 'All Locations',
        ),
        'public' => true,
        'has_archive' => true,
        'rewrite' => array('slug' => 'locations'),
        'menu_icon' => 'dashicons-location-alt',
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
        'taxonomies' => array('location_category'), // Assign taxonomy
    ));

    // Register Custom Taxonomy: Location Category
    register_taxonomy('location_category', 'location', array(
        'labels' => array(
            'name' => 'Location Categories',
            'singular_name' => 'Location Category',
            'search_items' => 'Search Location Categories',
            'all_items' => 'All Location Categories',
            'edit_item' => 'Edit Location Category',
            'update_item' => 'Update Location Category',
            'add_new_item' => 'Add New Location Category',
            'new_item_name' => 'New Location Category Name',
        ),
        'hierarchical' => true,
        'public' => true,
        'show_admin_column' => true,
        'rewrite' => array('slug' => 'location-category'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'register_location_post_type_and_taxonomy');




function shortcode_location_categories_map()
{
    ob_start();

    // Load categories
    $terms = get_terms([
        'taxonomy' => 'location_category',
        'hide_empty' => false,
    ]);
    ?>

    <div class="category-map-wrapper">
        <div id="locations-map" style="height: 500px; margin-bottom: 20px;"></div>

        <div class="category-cards">
            <?php foreach ($terms as $term):
                $lat = get_field('latitude', 'location_category_' . $term->term_id);
                $lng = get_field('longitude', 'location_category_' . $term->term_id);
                $image = get_field('image', 'location_category_' . $term->term_id); // ACF image field
                $phone = get_field('phone_number', 'location_category_' . $term->term_id);
                if (!$lat || !$lng)
                    continue;
                ?>
                <div class="category-card" data-lat="<?php echo esc_attr($lat); ?>" data-lng="<?php echo esc_attr($lng); ?>"
                    data-name="<?php echo esc_attr($term->name); ?>" data-image="<?php echo esc_url($image); ?>"
                    data-phone="<?php echo esc_attr($phone); ?>"
                    onclick="window.location.href='<?php echo esc_url(get_term_link($term)); ?>'">
                    <?php if ($image): ?>
                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($term->name); ?>"
                            class="category-image" />
                    <?php endif; ?>
                    <?php if ($phone): ?>
                        <p class="category-phone"><?php echo esc_html($phone); ?></p>
                    <?php endif; ?>
                    <h3><?php echo esc_html($term->name); ?></h3>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Leaflet CSS + JS via CDN -->
    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" /> -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('locations-map').setView([37.0902, -95.7129], 4); // USA center

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const markers = [];

            document.querySelectorAll('.category-card').forEach(card => {
                const lat = parseFloat(card.dataset.lat);
                const lng = parseFloat(card.dataset.lng);
                const name = card.dataset.name;
                const image = card.dataset.image;
                const phone = card.dataset.phone;

                let tooltipContent = `<strong>${name}</strong>`;
                if (image) {
                    tooltipContent += `<br><img src="${image}" alt="${name}" style="width:100px;height:auto;">`;
                }
                if (phone) {
                    tooltipContent += `<br><span style='font-size:12px;'>${phone}</span>`;
                }
                const marker = L.marker([lat, lng]).addTo(map).bindTooltip(tooltipContent, {
                    direction: 'top',
                    permanent: false,
                    sticky: true,
                    opacity: 1,
                    className: 'custom-tooltip'
                });
                // console.log(marker);
                markers.push({ marker, card });
                card.addEventListener('mouseenter', () => {
                    map.setView([lat, lng], 1);
                    marker.openTooltip();
                });
                marker.on('click', () => {
                    marker.openTooltip();
                });
                card.addEventListener('mouseleave', () => {
                    marker.closeTooltip();
                });
            });
        });
    </script>

    <style>
        .category-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .category-card {
            border: 1px solid #ccc;
            padding: 15px;
            width: 200px;
            cursor: pointer;
            background: #f8f8f8;
            transition: 0.3s ease;
            text-align: center;
        }

        .category-card:hover {
            background: #e2f0ff;
            transform: scale(1.02);
        }

        .category-card h3 {
            margin: 10px 0 0;
        }

        .category-card img.category-image {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 10px;
            border-radius: 4px;
        }
    </style>

    <?php
    return ob_get_clean();
}
add_shortcode('location_categories_map', 'shortcode_location_categories_map');













