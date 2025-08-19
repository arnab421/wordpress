<?php
/**
 * Custom WooCommerce Shop + AJAX Filters + Pagination
 */

// path:woocommerce/archive-product.php
defined('ABSPATH') || exit;

get_header('shop');
?>

<div class="custom-shop container">
  <aside class="filters">
    <form id="ajax-filters" autocomplete="off">
      <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce('custom_shop_nonce') ); ?>" />

      <!-- Search -->
      <div class="filter-block">
        <label>Search</label>
        <input type="search" name="s" placeholder="Search products…" />
      </div>

      <!-- Categories (hierarchical) -->
      <div class="filter-block">
        <label>Categories</label>
        <div class="checkboxes">
          <?php
          $cats = get_terms([
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'parent'     => 0,
          ]);
          function render_cat_tree($parent_id){
            $children = get_terms(['taxonomy'=>'product_cat', 'hide_empty'=>true, 'parent'=>$parent_id]);
            if ($children) {
              echo '<ul class="cat-children">';
              foreach ($children as $c) {
                echo '<li>';
                echo '<label><input type="checkbox" name="cats[]" value="'.esc_attr($c->term_id).'"> '.esc_html($c->name).'</label>';
                render_cat_tree($c->term_id);
                echo '</li>';
              }
              echo '</ul>';
            }
          }
          foreach ($cats as $cat) {
            echo '<div class="cat-root">';
            echo '<label><input type="checkbox" name="cats[]" value="'.esc_attr($cat->term_id).'"> '.esc_html($cat->name).'</label>';
            render_cat_tree($cat->term_id);
            echo '</div>';
          }
          ?>
        </div>
      </div>

      <!-- Tags -->
      <div class="filter-block">
        <label>Tags</label>
        <div class="checkboxes">
          <?php
          $tags = get_terms(['taxonomy'=>'product_tag','hide_empty'=>true,'number'=>20]);
          foreach ($tags as $t){
            echo '<label><input type="checkbox" name="tags[]" value="'.esc_attr($t->slug).'"> '.esc_html($t->name).'</label>';
          }
          ?>
        </div>
      </div>

      <!-- Dynamic Attribute Filters -->
      <?php
      $attribute_taxonomies = wc_get_attribute_taxonomies();
      if ( ! empty($attribute_taxonomies) ) :
        foreach ( $attribute_taxonomies as $tax_obj ):
          $tax = wc_attribute_taxonomy_name( $tax_obj->attribute_name ); // e.g. pa_color
          $terms = get_terms(['taxonomy'=>$tax,'hide_empty'=>true]);
          if ( empty($terms) || is_wp_error($terms) ) continue;
          ?>
          <div class="filter-block">
            <label><?php echo esc_html( $tax_obj->attribute_label ?: $tax_obj->attribute_name ); ?></label>
            <div class="checkboxes">
              <?php foreach($terms as $term): ?>
                <label>
                  <input type="checkbox" name="attr[<?php echo esc_attr($tax); ?>][]" value="<?php echo esc_attr($term->slug); ?>">
                  <?php echo esc_html($term->name); ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php
        endforeach;
      endif;
      ?>

      <!-- Price -->
      <div class="filter-block">
        <label>Price Range</label>
        <div class="price-row">
          <input type="number" name="min_price" min="0" step="1" placeholder="Min" />
          <span>–</span>
          <input type="number" name="max_price" min="0" step="1" placeholder="Max" />
        </div>
      </div>

      <!-- Stock / Sale / Rating -->
      <div class="filter-block">
        <label><input type="checkbox" name="in_stock" value="1"> In stock only</label><br>
        <label><input type="checkbox" name="on_sale" value="1"> On sale</label>
      </div>
      <div class="filter-block">
        <label>Minimum Rating</label>
        <select name="min_rating">
          <option value="">Any</option>
          <option value="1">1★+</option>
          <option value="2">2★+</option>
          <option value="3">3★+</option>
          <option value="4">4★+</option>
          <option value="5">5★</option>
        </select>
      </div>

      <!-- Sort & Per-page -->
      <div class="filter-block">
        <label>Sort by</label>
        <select name="orderby">
          <option value="">Default</option>
          <option value="date">Newest</option>
          <option value="price">Price: Low→High</option>
          <option value="price-desc">Price: High→Low</option>
          <option value="popularity">Popularity</option>
          <option value="rating">Rating</option>
          <option value="title">Title A→Z</option>
          <option value="rand">Random</option>
        </select>
      </div>
      <div class="filter-block">
        <label>Per page</label>
        <select name="ppp">
          <option value="12">12</option>
          <option value="24">24</option>
          <option value="36">36</option>
        </select>
      </div>

      <div class="filter-actions">
        <button type="submit" class="button">Apply</button>
        <button type="reset" id="reset-filters" class="button alt">Reset</button>
      </div>
    </form>
  </aside>

  <main class="results">
    <div class="results-bar">
      <div id="result-count"></div>
    </div>

    <div id="products-grid" class="grid">
      <?php
      // Server-side render for SEO & no-JS
      echo do_shortcode('[custom_shop_loop]');
      ?>
    </div>

    <div id="ajax-pagination" class="pagination"></div>

    <div id="ajax-status" class="status" style="display:none;">Loading…</div>

    <noscript>
      <?php woocommerce_pagination(); ?>
    </noscript>
  </main>
</div>

<?php get_footer('shop'); ?>
