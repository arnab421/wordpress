/* global CUSTOM_SINGLE_VARS */
jQuery(function($){
  // ----- Gallery (Slick)
  if ($('.main-slider').length && $('.thumb-slider').length) {
    $('.main-slider').slick({
      slidesToShow: 1,
      arrows: true,
      asNavFor: '.thumb-slider'
    });
    $('.thumb-slider').slick({
      slidesToShow: 4,
      asNavFor: '.main-slider',
      focusOnSelect: true,
      responsive: [
        { breakpoint: 900, settings: { slidesToShow: 4 } },
        { breakpoint: 480, settings: { slidesToShow: 3 } }
      ]
    });
  }

  // ----- Helper: refresh mini cart via fragment (WC standard) OR explicit loader
  function refreshFragments() {
    // standard event triggers refresh; but we also have a fallback loader
    $(document.body).trigger('wc_fragment_refresh');
    // fallback: load our mini cart HTML
    $.post(CUSTOM_SINGLE_VARS.ajax_url, { action: 'cs_load_mini_cart' }, function(html){
      $('#custom-mini-cart .mini-cart-contents').html(html);
    });
  }

  // initial mini cart load
  refreshFragments();

  // ----- AJAX Add to Cart (simple & variable)
  // variable: the variations_form produced by Woo contains a submit button with class single_add_to_cart_button
  $(document).on('submit', '.variations_form.cart, .simple_add_to_cart_form.cart', function(e){
    e.preventDefault();
    var $form = $(this);

    // serialize either full serialized data or single fields
    var data;
    if ($form.is('form')) {
      data = $form.serialize();
    } else {
      // fallback: collect inputs
      data = $form.find(':input').serialize();
    }

    // append action + nonce
    data += '&action=cs_ajax_add_to_cart&nonce=' + encodeURIComponent(CUSTOM_SINGLE_VARS.nonce);

    $.ajax({
      url: CUSTOM_SINGLE_VARS.ajax_url,
      method: 'POST',
      data: data,
      dataType: 'json',
      beforeSend: function(){
        $form.find('button[type=submit]').prop('disabled', true).addClass('loading');
      },
      success: function(res){
        if ( res && res.fragments ) {
          // If WC_AJAX::get_refreshed_fragments() returned, replace fragments
          $.each(res.fragments, function(key, val){
            try { $(key).replaceWith(val); } catch(e){}
          });
        }
        refreshFragments();
      },
      error: function(){
        alert('Could not add to cart. Please try again.');
      },
      complete: function(){
        $form.find('button[type=submit]').prop('disabled', false).removeClass('loading');
      }
    });
  });

  // ----- Related products carousel (AJAX)
  function loadRelated() {
    $.post(CUSTOM_SINGLE_VARS.ajax_url, {
      action: 'cs_related_products',
      nonce: CUSTOM_SINGLE_VARS.nonce,
      product_id: CUSTOM_SINGLE_VARS.product_id,
      limit: 8
    }, function(res){
      if ( res && res.success ) {
        var $wrap = $('.related-carousel');
        $wrap.html(res.data.html);
        // init slick on container
        $wrap.find('.related-track').slick({
          slidesToShow: 4,
          slidesToScroll: 1,
          arrows: true,
          responsive: [
            { breakpoint: 1024, settings: { slidesToShow: 3 } },
            { breakpoint: 768, settings: { slidesToShow: 2 } },
            { breakpoint: 480, settings: { slidesToShow: 1 } }
          ]
        });
      }
    }, 'json');
  }
  loadRelated();

  // related add-to-cart (delegated)
  $(document).on('submit', '.related-add-to-cart', function(e){
    e.preventDefault();
    var d = $(this).serialize() + '&action=cs_ajax_add_to_cart&nonce=' + encodeURIComponent(CUSTOM_SINGLE_VARS.nonce);
    $.post(CUSTOM_SINGLE_VARS.ajax_url, d, function(res){
      refreshFragments();
    }, 'json');
  });

  // ----- Mini-cart interactions: update qty + remove (delegated)
  $(document).on('change', '#custom-mini-cart .quantity input.qty', function(){
    var $input = $(this);
    var key = $input.data('cart_item_key') || $input.attr('data-key');
    var qty = $input.val();
    if (!key) return;
    $.post(CUSTOM_SINGLE_VARS.ajax_url, {
      action: 'cs_update_cart_item',
      nonce: CUSTOM_SINGLE_VARS.nonce,
      cart_item_key: key,
      quantity: qty
    }, function(){
      refreshFragments();
    });
  });

  $(document).on('click', '#custom-mini-cart .mini_cart_item a.remove, #custom-mini-cart .mini-cart-remove', function(e){
    e.preventDefault();
    var key = $(this).data('cart_item_key') || $(this).attr('data-key');
    if (!key) return;
    $.post(CUSTOM_SINGLE_VARS.ajax_url, {
      action: 'cs_remove_cart_item',
      nonce: CUSTOM_SINGLE_VARS.nonce,
      cart_item_key: key
    }, function(){
      refreshFragments();
    });
  });

  // ----- Load more reviews
  $('#load-more-reviews').on('click', function(){
    var $btn = $(this);
    var page = parseInt( $btn.data('page') || 1, 10 );
    var per = parseInt( $btn.data('per-page') || 5, 10 );
    var pid = $btn.data('product-id');

    $btn.prop('disabled', true).text('Loading...');

    $.post(CUSTOM_SINGLE_VARS.ajax_url, {
      action: 'cs_load_more_reviews',
      nonce: CUSTOM_SINGLE_VARS.nonce,
      product_id: pid,
      page: page,
      per_page: per
    }, function(res){
      if ( res && res.success ) {
        $('#reviews-list').append(res.data.html);
        $btn.data('page', page + 1);
        if (!res.data.has_more) $btn.hide();
      }
      $btn.prop('disabled', false).text('Load more reviews');
    }, 'json');
  });

  // Refresh fragments -> also reload mini cart contents
  $(document.body).on('wc_fragments_refreshed', function(){
    $.post(CUSTOM_SINGLE_VARS.ajax_url, { action: 'cs_load_mini_cart' }, function(html){
      $('#custom-mini-cart .mini-cart-contents').html(html);
    });
  });

});
