<?php
/**
 * Advanced custom single product template
 * Place: your-child-theme/woocommerce/single-product.php
 */
defined( 'ABSPATH' ) || exit;
get_header( 'shop' );

while ( have_posts() ) : the_post();
    global $product;
    if ( ! $product ) continue;
?>

<div id="custom-single-product" class="custom-single-product container">

  <div class="product-top">
    <!-- Gallery -->
    <div class="product-gallery">
      <div class="main-slider">
        <?php
        // Featured image first
        $featured_id = $product->get_image_id();
        if ( $featured_id ) {
            echo '<div class="slide">' . wp_get_attachment_image( $featured_id, 'large' ) . '</div>';
        }
        // Additional gallery images
        $attachment_ids = $product->get_gallery_image_ids();
        if ( $attachment_ids ) {
            foreach ( $attachment_ids as $aid ) {
                echo '<div class="slide">' . wp_get_attachment_image( $aid, 'large' ) . '</div>';
            }
        }
        ?>
      </div>

      <div class="thumb-slider">
        <?php
        if ( $featured_id ) {
            echo '<div class="thumb">' . wp_get_attachment_image( $featured_id, 'thumbnail' ) . '</div>';
        }
        if ( $attachment_ids ) {
            foreach ( $attachment_ids as $aid ) {
                echo '<div class="thumb">' . wp_get_attachment_image( $aid, 'thumbnail' ) . '</div>';
            }
        }
        ?>
      </div>
    </div>

    <!-- Summary -->
    <div class="product-summary">
      <h1 class="product-title"><?php the_title(); ?></h1>

      <div class="price-stock">
        <div class="product-price"><?php echo $product->get_price_html(); ?></div>
        <div class="stock-status"><?php echo wc_get_stock_html( $product ); ?></div>
      </div>

      <div class="short-desc">
        <?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ); ?>
      </div>

      <!-- Add to cart area:
           - For variable products we rely on Woo's variations form (so wc-add-to-cart-variation works)
           - For simple, we render quantity + button and handle AJAX
      -->
      <div class="product-cart-area">
        <?php if ( $product->is_type( 'variable' ) ) : ?>
            <?php
            // This outputs <form class="variations_form cart" ...> with attribute selects & variation_id
            woocommerce_variable_add_to_cart();
            ?>
        <?php else : ?>
            <form class="simple_add_to_cart_form cart" method="post">
                <?php woocommerce_quantity_input( ['min_value' => 1, 'input_value' => 1] ); ?>
                <input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
                <button type="submit" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
            </form>
        <?php endif; ?>
      </div>

      <div class="product-meta">
        <div class="sku"><?php echo wc_product_sku_enabled() ? 'SKU: ' . $product->get_sku() : ''; ?></div>
        <div class="categories"><?php echo wc_get_product_category_list( $product->get_id() ); ?></div>
      </div>
    </div>
  </div>

  <!-- Tabs / description -->
  <div class="product-tabs">
    <div class="description">
      <h2>Description</h2>
      <div><?php the_content(); ?></div>
    </div>
  </div>

  <!-- Reviews (AJAX load more) -->
  <section id="custom-reviews" class="custom-reviews">
    <h2><?php esc_html_e( 'Customer reviews', 'woocommerce' ); ?></h2>
    <ul id="reviews-list" class="reviews-list" aria-live="polite"></ul>
    <div class="reviews-actions">
      <button id="load-more-reviews"
              class="button"
              data-page="1"
              data-per-page="5"
              data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
        <?php esc_html_e( 'Load more reviews', 'woocommerce' ); ?>
      </button>
    </div>

    <div class="review-form-wrap">
      <?php comments_template(); // keep normal review form ?>
    </div>
  </section>

  <!-- Related (AJAX carousel) -->
  <section id="custom-related" class="custom-related">
    <h2><?php esc_html_e( 'Related products', 'woocommerce' ); ?></h2>
    <div class="related-carousel" aria-live="polite"></div>
  </section>

  <!-- Mini cart (AJAX; updated by fragments or explicit loader) -->
  <aside id="custom-mini-cart" class="custom-mini-cart">
    <h3><?php esc_html_e( 'Your cart', 'woocommerce' ); ?></h3>
    <div class="mini-cart-contents"></div>
  </aside>

<?php
endwhile;
get_footer( 'shop' );
