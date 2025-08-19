(function($){
  const $form = $('#ajax-filters');
  const $grid = $('#products-grid');
  const $pagi = $('#ajax-pagination');
  const $status = $('#ajax-status');
  const $count = $('#result-count');

  // Debounce helper
  function debounce(fn, ms){ let t; return function(){ clearTimeout(t); t=setTimeout(()=>fn.apply(this, arguments), ms); }; }

  // Serialize form to plain object
  function formDataObj() {
    const data = $form.serializeArray();
    const obj = {};
    data.forEach(({name, value}) => {
      if (name.endsWith('[]')) {
        const key = name.slice(0,-2);
        obj[key] = obj[key] || [];
        obj[key].push(value);
      } else if (name.match(/^attr\[/)) {
        // attr[pa_color][] style comes already as arrays
        if (obj[name]) {
          if (Array.isArray(obj[name])) obj[name].push(value);
          else obj[name] = [obj[name], value];
        } else {
          obj[name] = value;
        }
      } else {
        obj[name] = value;
      }
    });
    return obj;
  }

  // Build payload for AJAX
  function buildPayload(page=1){
    const fd = $form.serializeArray();
    const payload = { action: 'custom_shop_filter', nonce: $form.find('[name="nonce"]').val(), page };
    fd.forEach(({name, value}) => {
      // keep array params
      if (name.endsWith('[]')) {
        const key = name.slice(0,-2);
        if (!payload[key]) payload[key] = [];
        payload[key].push(value);
      } else {
        payload[name] = value;
      }
    });
    return payload;
  }

  // Read/Write URL state
  function toQuery() {
    const p = new URLSearchParams();
    const fd = $form.serializeArray();
    fd.forEach(({name, value}) => { if (value) p.append(name, value); });
    return p.toString();
  }
  function applyFromURL() {
    const q = new URLSearchParams(window.location.search);
    if (!q.toString()) return;
    // set values
    $form.find(':input').each(function(){
      const name = this.name;
      if (!name) return;
      if (name.endsWith('[]')) {
        const key = name;
        const vals = q.getAll(key);
        if (this.type === 'checkbox') {
          this.checked = vals.includes(this.value);
        } else if (this.tagName === 'SELECT') {
          $(this).val(vals);
        }
      } else {
        const val = q.get(name);
        if (typeof val !== 'undefined' && val !== null) {
          if (this.type === 'checkbox') {
            this.checked = (val === '1' || val === 'on' || val === this.value);
          } else {
            $(this).val(val);
          }
        }
      }
    });
  }

  function bindPagination() {
    $pagi.off('click', 'a').on('click', 'a', function(e){
      e.preventDefault();
      const page = parseInt($(this).data('page'),10) || 1;
      load(page, false);
      window.scrollTo({ top: $('.custom-shop').offset().top - 20, behavior: 'smooth' });
    });
  }

  function setLoading(on){
    if (on) { $status.text(CUSTOM_SHOP_VARS.i18n_loading).show(); }
    else { $status.hide(); }
  }

  function load(page=1, pushState=true){
    const payload = buildPayload(page);
    setLoading(true);
    $.post(CUSTOM_SHOP_VARS.ajax_url, payload, function(res){
      setLoading(false);
      if (!res || !res.success) {
        $grid.html('<p>Something went wrong.</p>');
        $pagi.empty();
        $count.text('');
        return;
      }
      $grid.html(res.data.html);
      $pagi.html(res.data.pagination || '');
      $count.text(res.data.resultCount || '');
      bindPagination();

      if (pushState) {
        const qs = toQuery();
        const url = CUSTOM_SHOP_VARS.base_url + (qs ? ('?'+qs) : '');
        window.history.pushState({ page }, '', url);
      }
    });
  }

  // Events
  $form.on('submit', function(e){ e.preventDefault(); load(1); });
  $form.on('change', 'select,input[type=checkbox],input[type=number]', function(){ load(1); });
  $form.on('input', 'input[type=search]', debounce(function(){ load(1); }, 500));
  $('#reset-filters').on('click', function(){ setTimeout(()=>load(1), 0); });

  // Back/forward support
  window.addEventListener('popstate', function(){ applyFromURL(); load(1, false); });

  // Initialize from URL (if any), else leave SSR result
  applyFromURL();
  // Optional: load immediately to ensure parity with AJAX results
  // load(1, false);

  // For SSR pagination fallback links (if any) – rebind
  bindPagination();
})(jQuery);
