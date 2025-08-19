<?php
// Put below inside your child theme functions.php

// -------------------- Enqueue scripts & styles --------------------
add_action('wp_enqueue_scripts', 'cs_enqueue_single_product_assets');
function cs_enqueue_single_product_assets() {
    if ( ! is_product() ) return;

    // Slick Carousel (or use Swiper if you prefer)
    wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', [], '1.8.1');
    wp_enqueue_style('slick-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', ['slick-css'], '1.8.1');
    wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', ['jquery'], '1.8.1', true);

    // Ensure Woo variation script is present for variable products (updates price & image)
    wp_enqueue_script('wc-add-to-cart-variation');

    // Custom single product JS
    wp_enqueue_script(
        'custom-single-product',
        get_stylesheet_directory_uri() . '/assets/js/custom-single-product.js',
        ['jquery','slick-js','wc-add-to-cart-variation'],
        '1.0.0',
        true
    );

    // Localize needed vars
    global $post;
    wp_localize_script('custom-single-product', 'CUSTOM_SINGLE_VARS', [
        'ajax_url'    => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('custom_single_nonce'),
        'product_id'  => $post ? $post->ID : 0,
        // For fragment compatibility (wc_fragment_refresh triggers)
    ]);

    // Minimal inline CSS or move to file
    $css = '
    .custom-single-product{display:grid;grid-template-columns:1fr 420px;gap:28px;align-items:start}
    .product-gallery .main-slider .slide img{width:100%;height:auto;display:block}
    .thumb-slider .thumb img{width:72px;height:auto;display:block;cursor:pointer}
    .product-summary{padding:8px}
    .custom-mini-cart{border:1px solid #eee;padding:12px;border-radius:8px}
    .reviews-list{list-style:none;padding:0;margin:0}
    .related-carousel .related-item{padding:10px}
    @media(max-width:900px){ .custom-single-product{grid-template-columns:1fr} .thumb-slider{display:flex;gap:8px;overflow:auto} }
    ';
    wp_add_inline_style('woocommerce-general', $css);
}

// -------------------- Helper sanitizers --------------------
function cs_sanitize_array_of_slugs($arr){
    if ( empty($arr) ) return [];
    return array_map('sanitize_title', (array)$arr);
}

// -------------------- AJAX: Add to cart (simple & variable) --------------------
add_action('wp_ajax_cs_ajax_add_to_cart', 'cs_ajax_add_to_cart');
add_action('wp_ajax_nopriv_cs_ajax_add_to_cart', 'cs_ajax_add_to_cart');
function cs_ajax_add_to_cart() {
    check_ajax_referer('custom_single_nonce', 'nonce');

    // Accept either serialized form (data) or direct keys
    if ( ! empty( $_POST['data'] ) ) {
        parse_str( wp_unslash( $_POST['data'] ), $form );
    } else {
        $form = $_POST;
    }

    $product_id   = isset( $form['add-to-cart'] ) ? absint( $form['add-to-cart'] ) : ( isset( $form['product_id'] ) ? absint( $form['product_id'] ) : 0 );
    $quantity     = isset( $form['quantity'] ) ? wc_stock_amount( $form['quantity'] ) : 1;
    $variation_id = isset( $form['variation_id'] ) ? absint( $form['variation_id'] ) : 0;
    $variation    = [];

    foreach ( $form as $key => $val ) {
        if ( strpos( $key, 'attribute_' ) === 0 ) {
            $variation[ $key ] = wc_clean( $val );
        }
    }

    if ( ! $product_id ) {
        wp_send_json_error(['message' => 'Invalid product.']);
    }

    $added = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

    if ( $added ) {
        // Make WC return fragments so front-end wc_fragment_refresh works
        WC_AJAX::get_refreshed_fragments();
    } else {
        wp_send_json_error(['message' => 'Could not add to cart.']);
    }
}

// -------------------- AJAX: Load mini cart (render simple list) --------------------
add_action('wp_ajax_cs_load_mini_cart', 'cs_load_mini_cart');
add_action('wp_ajax_nopriv_cs_load_mini_cart', 'cs_load_mini_cart');
function cs_load_mini_cart() {
    // Use standard mini cart template if available
    ob_start();
    woocommerce_mini_cart();
    $html = ob_get_clean();
    echo $html;
    wp_die();
}

// -------------------- AJAX: Update cart item qty --------------------
add_action('wp_ajax_cs_update_cart_item', 'cs_update_cart_item');
add_action('wp_ajax_nopriv_cs_update_cart_item', 'cs_update_cart_item');
function cs_update_cart_item() {
    check_ajax_referer('custom_single_nonce', 'nonce');
    $key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ?? '' ) );
    $qty = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
    if ( $key ) {
        WC()->cart->set_quantity( $key, $qty, true );
        WC_AJAX::get_refreshed_fragments();
    }
    wp_die();
}

// -------------------- AJAX: Remove cart item --------------------
add_action('wp_ajax_cs_remove_cart_item', 'cs_remove_cart_item');
add_action('wp_ajax_nopriv_cs_remove_cart_item', 'cs_remove_cart_item');
function cs_remove_cart_item() {
    check_ajax_referer('custom_single_nonce', 'nonce');
    $key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ?? '' ) );
    if ( $key ) {
        WC()->cart->remove_cart_item( $key );
        WC_AJAX::get_refreshed_fragments();
    }
    wp_die();
}

// -------------------- AJAX: Load more reviews --------------------
add_action('wp_ajax_cs_load_more_reviews', 'cs_load_more_reviews');
add_action('wp_ajax_nopriv_cs_load_more_reviews', 'cs_load_more_reviews');
function cs_load_more_reviews() {
    check_ajax_referer('custom_single_nonce', 'nonce');

    $product_id = absint( $_POST['product_id'] ?? 0 );
    $page       = max(1, absint( $_POST['page'] ?? 1 ));
    $per_page   = max(1, absint( $_POST['per_page'] ?? 5 ));

    if ( ! $product_id ) {
        wp_send_json_error(['message' => 'Invalid product.']);
    }

    $args = [
        'post_id' => $product_id,
        'status'  => 'approve',
        'type'    => 'review',
        'number'  => $per_page,
        'offset'  => ($page - 1) * $per_page,
        'orderby' => 'comment_date_gmt',
        'order'   => 'DESC',
    ];
    $comments = get_comments( $args );
    $total = get_comments( ['post_id' => $product_id, 'status' => 'approve', 'type' => 'review', 'count' => true] );

    ob_start();
    if ( $comments ) {
        foreach ( $comments as $c ) {
            // Reuse WC's review template for consistent markup (falls back gracefully)
            $GLOBALS['comment'] = $c;
            wc_get_template( 'single-product/review.php', ['comment' => $c] );
        }
    }
    $html = ob_get_clean();
    $has_more = ($page * $per_page) < $total;

    wp_send_json_success(['html' => $html, 'has_more' => $has_more, 'total' => (int)$total]);
}

// -------------------- AJAX: Related products carousel --------------------
add_action('wp_ajax_cs_related_products', 'cs_related_products');
add_action('wp_ajax_nopriv_cs_related_products', 'cs_related_products');
function cs_related_products() {
    check_ajax_referer('custom_single_nonce', 'nonce');

    $product_id = absint( $_POST['product_id'] ?? 0 );
    $limit = max(1, absint( $_POST['limit'] ?? 8 ));

    if ( ! $product_id ) wp_send_json_error(['message'=>'Invalid product']);

    $related_ids = wc_get_related_products( $product_id, $limit );
    if ( empty( $related_ids ) ) {
        wp_send_json_success(['html' => '<p>' . esc_html__('No related products found', 'woocommerce') . '</p>']);
    }

    ob_start();
    echo '<div class="related-track">';
    foreach ( $related_ids as $rid ) {
        $rp = wc_get_product( $rid );
        if ( ! $rp ) continue;
        echo '<div class="related-item">';
        echo '<a class="thumb" href="' . esc_url( get_permalink( $rid ) ) . '">' . $rp->get_image() . '</a>';
        echo '<h4 class="title"><a href="'.esc_url(get_permalink($rid)).'">' . esc_html( $rp->get_name() ) . '</a></h4>';
        echo '<div class="price">' . $rp->get_price_html() . '</div>';
        if ( $rp->is_type('simple') && $rp->is_in_stock() ) {
            echo '<form class="related-add-to-cart" method="post">';
            echo '<input type="hidden" name="product_id" value="' . esc_attr( $rid ) . '">';
            echo '<input type="hidden" name="quantity" value="1">';
            echo '<button type="submit" class="button">Add to cart</button>';
            echo '</form>';
        } else {
            echo '<a class="button" href="' . esc_url( get_permalink( $rid ) ) . '">' . esc_html__('View', 'woocommerce') . '</a>';
        }
        echo '</div>';
    }
    echo '</div>';
    $html = ob_get_clean();

    wp_send_json_success(['html' => $html]);
}
