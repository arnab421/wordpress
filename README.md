# wordpress
wordpress template

functions.php code 
******************************
function load_posts_callback()
{
	// Retrieve the category from the AJAX request
	// Retrieve the category name from the AJAX request
	$category_name = $_POST['category_name'];
    echo $category_name;
	echo "category name";
	
	// Retrieve the page number from the AJAX request
	$page = $_POST['page'];
    echo "page number";
	echo $page;
	// Calculate the offset based on the page number
	$offset = ($page - 1) * 2;
    echo "offset";
	echo $offset;
	if ($category_name == 'all') {
		$args = array(
			'post_type' => 'team_members', // Adjust post type if needed
			'posts_per_page' => 2, // Posts per page setting
			'offset' => $offset, // Offset for pagination
		);
	} else {
		$args = array(
			'post_type' => 'team_members', // Adjust post type if needed
			'category_name' => $category_name, // Filter by category name
			'posts_per_page' => 2, // Posts per page setting
			'offset' => $offset, // Offset for pagination
		);
	}
	// Construct the query args


	// Run the query
	$query = new WP_Query($args);
	echo "<pre>";
	print_r($query);
	echo "</pre>";
	die();
	$post = [];
	// Check if there are posts
	if ($query->have_posts()) {
		// Start the loop
		while ($query->have_posts()) {
			$query->the_post();
			$postdata_image = wp_get_attachment_image_src(get_post_thumbnail_id($query->ID), 'full');
			$post[] = [
				"title" => get_the_title(),
				"image" => $postdata_image[0]
			];
		}
		// Restore original post data
		wp_reset_postdata();
	} else {
		// If no more posts are found, return a message
		echo 'no_more_posts';
	}
	if ($post) {
		$return = array(
			'message'  => 'posts',
			'status' => 1,
			'result' => $post,
			'maxPost' => $query->max_num_pages,
			// 'currentPage' => $currentPage,
			// 'req' => $_REQUEST['currentPage']
		);
		wp_send_json($return);
	} else {
		$return = array(
			'message'  => 'No Post Found',
			'status'       => 0
		);

		wp_send_json($return);
	}
	// Always exit to avoid further execution
	wp_die();
} 

add_action('wp_ajax_load_posts_callback', 'load_posts_callback');
add_action('wp_ajax_nopriv_load_posts_callback', 'load_posts_callback'); 
*************************************************************************
js code 
***************
jQuery(document).ready(function($) {
    var page = 1; // Initialize page number
    var loading = false; // Flag to prevent multiple AJAX requests
    var category_name = 'all'; // Initialize category name

    // Event listener for the "Load More" button click
    jQuery('#load-more-btn').on('click', function() {
        if (!loading) { // Check if a request is not already in progress
            loading = true; // Set loading flag to true

            // AJAX request to load more posts
            jQuery.ajax({
                url: ajax.ajaxurl,
                data: {
                    action: 'load_posts_callback',
                    category_name: category_name,
                    page: page,
                },
                dataType: 'json',
                type:'post',
                success: function(response) {
                    // if (response !== 'no_more_posts') { 
                    //     jQuery('#posts-container').append(response); 
                    //     page++; 
                    // } else {
                    //     jQuery('#load-more-btn').hide(); 
                    // }
                    // loading = false; 
                    console.log(response.result);
                },
                // error: function(xhr, status, error) {
                //     console.error(error); 
                //     loading = false; 
                // }
            });
        }
    });

    // Event listener for category click
    jQuery('.category').on('click', function(e) {
        // Reset page number to 1
        e.preventDefault()
        page = 1;

        // Get the category name
        category_name = jQuery(this).data('team');
        console.log("Button category clicked");
        console.log(category_name);

        // Clear existing posts
        // jQuery('#posts-container').empty();

        // Show the "Load More" button
        jQuery('#load-more-btn').show();

        // Trigger click event to load posts for the selected category
        jQuery('#load-more-btn').click();
    });
});
************************ 

template page code 
**********************
<div class="categories">
        <a href="javascript:void(0)" class="category active" data-team="all">All</a>
        <?php 
            foreach ($categories as $category) { 
        ?>
        <a class="category"  href="javascript:void(0)" data-team="<?php echo $category->slug;?>"><?php echo $category->name;?></a>
        <?php } ?>
        </div>
    </div>
    <div class="team-wraper">
      <div class="row">
        <?php
        $args = array(
          'post_type' => 'team_members',
          'posts_per_page' => -1,
          'post_status' => 'publish',
          'order' => 'DESC',
        );
        $teammembers = new WP_Query($args);
        $i = 0;
        if ($teammembers->have_posts()) {
          while ($teammembers->have_posts()) {
            $i++;
            $teammembers->the_post();
            $teammember_image = wp_get_attachment_image_src(get_post_thumbnail_id($teammembers->ID), 'full');
        ?>
            <div class="col-lg-4">
              <div class="sngl-box">
                <div class="image-box">
                  <img src="<?php echo $teammember_image[0]; ?>">
                </div>
                <div class="content-box">
                  <div class="summary">
                    <p class="collapse" id="collapseSummary<?php echo $i; ?>">
                      <?php echo get_the_excerpt($teammembers->ID); ?>
                    </p>
                    <a class="collapsed" data-toggle="collapse" href="#collapseSummary<?php echo $i; ?>" aria-expanded="false" aria-controls="collapseSummary<?php echo $i; ?>"></a>
                  </div>
                </div>
              </div>
            </div>
        <?php }
        }

        wp_reset_postdata(); ?>
        <!-- <div class="col-lg-4">
          <div class="sngl-box">
            <div class="image-box">
              <img src="<?php echo get_template_directory_uri(); ?>/images/gould-goodwin-pic1.jpg">
            </div>
            <div class="content-box">
              <div class="summary">
                <p class="collapse" id="collapseSummary2">
                  Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc porttitor maximus laoreet. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In hac habitasse platea dictumst. Suspendisse venenatis sollicitudin erat in gravida. Sed eget nisl tristique, commodo lectus sit amet, vulputate sem. Cras porttitor lorem ipsum, sit amet iaculis massa feugiat vitae. Curabitur sapien odio, ullamcorper tincidunt interdum vitae, vestibulum eu neque. Nam leo massa, fringilla eget mauris feugiat, auctor suscipit justo.
                </p>
                <a class="collapsed" data-toggle="collapse" href="#collapseSummary2" aria-expanded="false" aria-controls="collapseSummary2"></a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="sngl-box">
            <div class="image-box">
              <img src="<?php echo get_template_directory_uri(); ?>/images/team2.jpg">
            </div>
            <div class="content-box">
              <div class="summary">
                <p class="collapse" id="collapseSummary3">
                  Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc porttitor maximus laoreet. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In hac habitasse platea dictumst. Suspendisse venenatis sollicitudin erat in gravida. Sed eget nisl tristique, commodo lectus sit amet, vulputate sem. Cras porttitor lorem ipsum, sit amet iaculis massa feugiat vitae. Curabitur sapien odio, ullamcorper tincidunt interdum vitae, vestibulum eu neque. Nam leo massa, fringilla eget mauris feugiat, auctor suscipit justo.
                </p>
                <a class="collapsed" data-toggle="collapse" href="#collapseSummary3" aria-expanded="false" aria-controls="collapseSummary3"></a>
              </div>
            </div>
          </div>
        </div> -->

        <div class="load-posts-wrapper">
          <a href="javascript:void(0)" id="load-more-btn" class="load-posts">Load Posts</a>
        </div>
**************************
