<?php
/**
 * ===== Enqueue assets for Shop & Single =====
 */
add_action('wp_enqueue_scripts', function () {
  // Shop & product category/tag archives
  if ( is_shop() || is_product_taxonomy() ) {
    // Base Woo style handle to inline on
    wp_enqueue_style('woocommerce-general');

    wp_enqueue_script(
      'custom-shop-ajax',
      get_stylesheet_directory_uri() . '/assets/js/custom-shop.js',
      ['jquery'],
      '1.4',
      true
    );

    wp_localize_script('custom-shop-ajax', 'CUSTOM_SHOP_VARS', [
      'ajax_url'     => admin_url('admin-ajax.php'),
      'nonce'        => wp_create_nonce('custom_shop_nonce'),
      'base_url'     => esc_url_raw( get_permalink( wc_get_page_id('shop') ) ),
      'i18n_loading' => __('Loading…','woocommerce'),
    ]);

    // Minimal CSS (adjust or move to a .css file)
    $css = '
    .custom-shop{display:grid;grid-template-columns:280px 1fr;gap:24px}
    .custom-shop .filters{border:1px solid #eee;padding:16px;border-radius:12px}
    .custom-shop .filter-block{margin-bottom:16px}
    .custom-shop .checkboxes{display:flex;flex-direction:column;gap:8px;max-height:240px;overflow:auto}
    .custom-shop .price-row{display:flex;align-items:center;gap:8px}
    .custom-shop .results .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:18px}
    .custom-shop .product-card{border:1px solid #eee;border-radius:12px;padding:12px;display:flex;flex-direction:column;gap:8px}
    .custom-shop .product-card .thumb img{width:100%;height:auto;display:block}
    .custom-shop .product-card .meta{display:flex;align-items:center;justify-content:space-between;gap:8px}
    .custom-shop .pagination{display:flex;gap:8px;justify-content:center;margin:18px 0}
    .custom-shop .pagination a{padding:6px 10px;border:1px solid #ddd;border-radius:8px;text-decoration:none}
    .custom-shop .pagination a.active{background:#333;color:#fff;border-color:#333}
    .custom-shop .status{margin:16px 0;text-align:center}
    @media (max-width: 900px){ .custom-shop{grid-template-columns: 1fr} }
    ';
    wp_add_inline_style('woocommerce-general', $css);
  }

  // Single product
  if ( is_product() ) {
    wp_enqueue_script('wc-add-to-cart-variation'); // native variation logic
    wp_enqueue_script(
      'custom-single-product',
      get_stylesheet_directory_uri() . '/assets/js/single-product.js',
      ['jquery','wc-add-to-cart-variation'],
      '1.1',
      true
    );
    wp_localize_script('custom-single-product', 'CUSTOM_SINGLE_VARS', [
      'ajax_url' => admin_url('admin-ajax.php'),
      'nonce'    => wp_create_nonce('custom_single_nonce'),
      'wc_ajax'  => function_exists('wc_get_endpoint_url') ? wc_get_endpoint_url('%%endpoint%%') : '',
    ]);
  }
});

/**
 * ===== Helper: sanitize array of ints/slugs =====
 */
function cs_array_map_int($arr){ return array_map('intval', array_filter((array)$arr)); }
function cs_array_map_slug($arr){ return array_map('sanitize_title', array_filter((array)$arr)); }

/**
 * ===== Build WP_Query args from request =====
 * Supports: search, cats[], tags[], attr[pa_x][] arrays, price, stock, sale, rating, orderby, ppp, paged
 */
function custom_shop_build_query_args($req = []) {
  $paged = isset($req['page']) ? max(1, (int)$req['page']) : max(1, get_query_var('paged'));
  $ppp   = isset($req['ppp'])  ? max(1, (int)$req['ppp'])  : 12;

  $args = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    'posts_per_page' => $ppp,
    'paged'          => $paged,
    's'              => isset($req['s']) ? sanitize_text_field($req['s']) : '',
    'tax_query'      => ['relation' => 'AND'],
    'meta_query'     => ['relation' => 'AND'],
  ];

  // Categories
  if (!empty($req['cats'])) {
    $args['tax_query'][] = [
      'taxonomy' => 'product_cat',
      'field'    => 'term_id',
      'terms'    => cs_array_map_int($req['cats']),
    ];
  }

  // Tags
  if (!empty($req['tags'])) {
    $args['tax_query'][] = [
      'taxonomy' => 'product_tag',
      'field'    => 'slug',
      'terms'    => cs_array_map_slug($req['tags']),
    ];
  }

  // Attributes: attr[pa_color][]=red, etc.
  if (!empty($req['attr']) && is_array($req['attr'])) {
    foreach ($req['attr'] as $tax => $slugs) {
      $slugs = cs_array_map_slug($slugs);
      if (!$slugs) continue;
      $args['tax_query'][] = [
        'taxonomy' => sanitize_title($tax),
        'field'    => 'slug',
        'terms'    => $slugs,
        'operator' => 'IN',
      ];
    }
  }

  // Price
  $has_min = isset($req['min_price']) && $req['min_price'] !== '';
  $has_max = isset($req['max_price']) && $req['max_price'] !== '';
  if ($has_min || $has_max) {
    $min = $has_min ? (float)$req['min_price'] : 0;
    $max = $has_max ? (float)$req['max_price'] : 999999999;
    $args['meta_query'][] = [
      'key'     => '_price',
      'value'   => [$min, $max],
      'compare' => 'BETWEEN',
      'type'    => 'DECIMAL(10,2)'
    ];
  }

  // In stock
  if (!empty($req['in_stock'])) {
    // Exclude outofstock via product_visibility taxonomy
    $args['tax_query'][] = [
      'taxonomy' => 'product_visibility',
      'field'    => 'name',
      'terms'    => ['outofstock'],
      'operator' => 'NOT IN',
    ];
  }

  // On sale
  if (!empty($req['on_sale'])) {
    $on_sale_ids = wc_get_product_ids_on_sale();
    $on_sale_ids = array_unique(array_map('intval', $on_sale_ids));
    $on_sale_ids = $on_sale_ids ?: [0]; // prevent empty which means "all"
    $args['post__in'] = $on_sale_ids;
  }

  // Min rating
  if (!empty($req['min_rating'])) {
    $args['meta_query'][] = [
      'key'     => '_wc_average_rating',
      'value'   => (float)$req['min_rating'],
      'compare' => '>=',
      'type'    => 'DECIMAL(10,2)'
    ];
  }

  // Orderby
  $orderby = isset($req['orderby']) ? sanitize_text_field($req['orderby']) : '';
  switch ($orderby) {
    case 'price':
      $args['meta_key'] = '_price';
      $args['orderby']  = 'meta_value_num';
      $args['order']    = 'ASC';
      break;
    case 'price-desc':
      $args['meta_key'] = '_price';
      $args['orderby']  = 'meta_value_num';
      $args['order']    = 'DESC';
      break;
    case 'date':
      $args['orderby'] = 'date';
      $args['order']   = 'DESC';
      break;
    case 'rating':
      $args['meta_key'] = '_wc_average_rating';
      $args['orderby']  = 'meta_value_num';
      $args['order']    = 'DESC';
      break;
    case 'popularity':
      $args['meta_key'] = 'total_sales';
      $args['orderby']  = 'meta_value_num';
      $args['order']    = 'DESC';
      break;
    case 'title':
      $args['orderby'] = 'title';
      $args['order']   = 'ASC';
      break;
    case 'rand':
      $args['orderby'] = 'rand';
      break;
    default:
      // Default Woo behavior
      $args['orderby'] = ['menu_order' => 'ASC', 'title' => 'ASC'];
  }

  return $args;
}

/**
 * ===== Renderer: product grid HTML (simple + variable) =====
 */
function custom_shop_render_products_html($query_args) {
  $q = new WP_Query($query_args);
  ob_start();

  if ($q->have_posts()) {
    echo '<div class="products grid">';
    while ($q->have_posts()) {
      $q->the_post();
      $product = wc_get_product(get_the_ID());
      if (!$product) continue;
      $rating_html = wc_get_rating_html( $product->get_average_rating() );
      ?>
      <div class="product-card">
        <a class="thumb" href="<?php the_permalink(); ?>">
          <?php echo $product->get_image('woocommerce_thumbnail'); ?>
        </a>
        <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

        <div class="meta">
          <span class="price"><?php echo $product->get_price_html(); ?></span>
          <span class="rating"><?php echo $rating_html ?: ''; ?></span>
        </div>

        <div class="actions">
          <?php
          // Industry-standard: use Woo template which outputs
          // "Add to cart" for simple / "Select options" for variable
          woocommerce_template_loop_add_to_cart();
          ?>
        </div>
      </div>
      <?php
    }
    echo '</div>';
  } else {
    echo '<p>' . esc_html__('No products found.', 'woocommerce') . '</p>';
  }
  wp_reset_postdata();

  $html = ob_get_clean();
  return [
    'html'       => $html,
    'found'      => (int) $q->found_posts,
    'max_pages'  => (int) $q->max_num_pages,
  ];
}

/**
 * ===== Shortcode for initial SSR in archive template =====
 */
add_shortcode('custom_shop_loop', function () {
  $args = custom_shop_build_query_args([]);
  $res  = custom_shop_render_products_html($args);
  return $res['html'];
});

/**
 * ===== AJAX: filter + paginate =====
 */
add_action('wp_ajax_custom_shop_filter', 'custom_shop_ajax_handler');
add_action('wp_ajax_nopriv_custom_shop_filter', 'custom_shop_ajax_handler');

function custom_shop_ajax_handler() {
  check_ajax_referer('custom_shop_nonce', 'nonce');

  // Collect and sanitize request
  $req = [
    's'          => $_POST['s']          ?? '',
    'cats'       => $_POST['cats']       ?? [],
    'tags'       => $_POST['tags']       ?? [],
    'attr'       => $_POST['attr']       ?? [],
    'min_price'  => $_POST['min_price']  ?? '',
    'max_price'  => $_POST['max_price']  ?? '',
    'in_stock'   => !empty($_POST['in_stock']) ? 1 : 0,
    'on_sale'    => !empty($_POST['on_sale'])  ? 1 : 0,
    'min_rating' => $_POST['min_rating'] ?? '',
    'orderby'    => $_POST['orderby']    ?? '',
    'ppp'        => $_POST['ppp']        ?? 12,
    'page'       => $_POST['page']       ?? 1,
  ];

  // sanitize nested arrays
  $req['s'] = sanitize_text_field($req['s']);
  $req['cats'] = cs_array_map_int($req['cats']);
  $req['tags'] = cs_array_map_slug($req['tags']);
  if (!empty($req['attr']) && is_array($req['attr'])) {
    foreach ($req['attr'] as $tax => $slugs) {
      $req['attr'][$tax] = cs_array_map_slug($slugs);
    }
  }

  $args = custom_shop_build_query_args($req);
  $res  = custom_shop_render_products_html($args);

  // Build pagination HTML
  $pagination = '';
  if ($res['max_pages'] > 1) {
    $current = max(1, (int)$req['page']);
    $pagination .= '<div class="pages" role="navigation" aria-label="Pagination">';
    for ($i = 1; $i <= $res['max_pages']; $i++) {
      $active = $i === $current ? ' class="active"' : '';
      $pagination .= '<a href="#" data-page="'.$i.'"'.$active.'>'.$i.'</a>';
    }
    $pagination .= '</div>';
  }

  // Result count text
  $ppp   = max(1, (int)$req['ppp']);
  $from  = ($req['page'] - 1) * $ppp + 1;
  $to    = min($from + $ppp - 1, $res['found']);
  $count = $res['found'] ? sprintf(__('Showing %1$d–%2$d of %3$d results', 'woocommerce'), $from, $to, $res['found']) : __('No results', 'woocommerce');

  wp_send_json_success([
    'html'        => $res['html'],
    'pagination'  => $pagination,
    'resultCount' => $count,
    'max_pages'   => $res['max_pages'],
    'found'       => $res['found'],
  ]);
}
