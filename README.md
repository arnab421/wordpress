# wordpress
wordpress template

functions.php code 
******************************
function load_posts_callback()
{
	 ob_start();
	// Retrieve the category from the AJAX request
	// Retrieve the category name from the AJAX request
	$currentPage=$_REQUEST['currentPage']+1; // we need next page to be loaded
	$category_name = $_POST['category_name'];
  
	  if ($category_name == 'all') {
		$args = array(
			'post_type' => 'team_members', // Adjust post type if needed
			'posts_per_page' => 2, // Posts per page setting
			'paged' => $currentPage,
		    'post_status' => 'publish',
		    'order' => 'DESC',
		);
	 }
	 else {
		$args = array(
			'post_type' => 'team_members',
			'posts_per_page' => 2, 
			'paged' => $currentPage,
			'tax_query' => array(
				array(
					'taxonomy' => 'team_type',
					'field'    => 'slug',
					'terms'    => $category_name, 
				),
			),
		);
	}
	// Construct the query args


	// Run the query
	$query = new WP_Query($args);
	$post = [];
	$i=0;
	// Check if there are posts
	if ($query->have_posts()) {
		// Start the loop
		while ($query->have_posts()) {
			$query->the_post();
			$postdata_image = wp_get_attachment_image_src(get_post_thumbnail_id($query->ID), 'full');
			$i++;
			$post[] = [
				"title" => get_the_title(),
				"image" => $postdata_image[0],
				"excerpt" => get_the_excerpt($query->ID)
			]; ?>
			<div class="col-lg-4">
              <div class="sngl-box">
                <div class="image-box">
                  <img src="<?php echo $postdata_image[0]; ?>">
                </div>
                <div class="content-box">
                  <div class="summary">
                    <p class="collapse" id="collapseSummary<?php echo $i; ?>">
                      <?php echo get_the_excerpt($query->ID); ?>
                    </p>
                    <a class="collapsed" data-toggle="collapse" href="#collapseSummary<?php echo $i; ?>" aria-expanded="false" aria-controls="collapseSummary<?php echo $i; ?>"></a>
                  </div>
                </div>
              </div>
            </div>
	   <?php
		}
		// Restore original post data
		wp_reset_postdata();
	} else {
		// If no more posts are found, return a message
		echo 'no_more_posts';
	}
	$output = ob_get_contents();
	ob_end_clean();
	if ($post) {
		$return = array(
			'message'  => 'posts',
			'status' => 1,
			'result' => $post,
			'resultout' => $output,
			'maxPost' => $query->max_num_pages,
			'currentPage' => $currentPage,
			'req' => $_REQUEST['currentPage']
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
*******************************************************************************
js file 
*************************************
jQuery(document).ready(function($) {
    // Event listener for the "Load More" button click
    jQuery('#load-more-btn').on('click', function() {
        // Check if a request is not already in progress
            // Set loading flag to true
            let currentPage=jQuery(this).attr('data-currentpage');
            // let category_name = jQuery(.category);
            var category_name = jQuery('.category.active').data('team');
            console.log(category_name);
            // AJAX request to load more posts
            jQuery.ajax({
                url: ajax.ajaxurl,
                data: {
                    action: 'load_posts_callback',
                    category_name: category_name,
                    currentPage:currentPage,
                },
                dataType: 'json',
                type:'post',
                success: function(response) {
                if(response){
                        var i=0;
                    jQuery('.load-posts').attr('data-currentpage',response.currentPage);
                    jQuery.each(response.result, function (key, val) {
                        i++;
                        jQuery('.team-box').append(`<div class="col-lg-4">
                        <div class="sngl-box">
                          <div class="image-box">
                            <img src="${val.image}">
                          </div>
                          <div class="content-box">
                            <div class="summary">
                              <p class="collapse" id="collapseSummary${i}">
                                ${val.excerpt}
                              </p>
                              <a class="collapsed" data-toggle="collapse" href="#collapseSummary${i}" aria-expanded="false" aria-controls="collapseSummary${i}"></a>
                            </div>
                          </div>
                        </div>
                      </div>`); 
                    });
                    if(response.currentPage == response.maxPost){
                        jQuery('.load-posts').hide();
                    }
                    
                  }
                  else {
					button.remove(); // if no data, remove the button as well
				  }

                }
                  
                
            });
       
    });

    // ctaegory click 
    jQuery('.category').on('click', function() {
        jQuery('.category').removeClass('active');
        jQuery(this).addClass('active');    
        
        let currentPage=jQuery(this).attr('data-currentpage');
        category_name = jQuery(this).data('team');
        // console.log(category_name);
        jQuery('.team-box').empty();
        jQuery('#load-more-btn').show();
        
        jQuery.ajax({
            url: ajax.ajaxurl,
            data: {
                action: 'load_posts_callback',
                category_name: category_name,
                currentPage:currentPage,
            },
            dataType: 'json',
            type:'post',
            success: function(response) {

                if (response) { 
                   jQuery('.team-box').html(response.resultout); 
                    if(response.currentPage == response.maxPost){
                      jQuery('.load-posts').hide();
                   }
                } else {
                    button.remove();
                }
                
            },
            
        });
      
    });
    // category click
});



*************************************
template 
**********************
<?php

        $custom_post_type = 'team_members';

        // Get the categories associated with the custom post type
        $categories = get_terms(array(
            'taxonomy' => 'team_type', // Replace 'your_taxonomy_name' with the name of your taxonomy
            'hide_empty' => false, // Set to true if you want to hide empty categories
       )); ?>
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
      <div class="row team-box">
        <?php
        $paged = '1';
        $args = array(
          'post_type' => 'team_members',
          'posts_per_page' => 2,
          'paged' => 1,
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
      </div>
      <?php


			// don't display the button if there are not enough posts
			 if ($teammembers->max_num_pages > 1){?>
      <div class="load-posts-wrapper">
          <a href="javascript:void(0)" data-currentpage="1" id="load-more-btn" class="load-posts">Load Posts</a>
      </div>
      <?php } ?>
***********************
