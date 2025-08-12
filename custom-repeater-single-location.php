<?php get_header(); ?>

<div class="single-location">
    <?php
    $title = get_the_title();
    $lat = get_field('lat');
    $lng = get_field('lang'); // assuming "lang" is actually "lng"
    $img = get_field('location_image');
    $phone = get_field('location_phone');
    $permalink = get_permalink();

    if (!$lat || !$lng) {
        echo "<p>Location coordinates not available.</p>";
        get_footer();
        return;
    }
    ?>

    <h1><?php echo esc_html($title); ?></h1>

    <div id="single-location-map" style="height: 500px; margin-bottom: 20px;"></div>

    <div class="location-card">
        <?php if ($img): ?>
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" class="location-image" />
        <?php endif; ?>
        <h3><?php echo esc_html($title); ?></h3>
        <?php if ($phone): ?>
            <p><strong>Phone:</strong> <?php echo esc_html($phone); ?></p>
        <?php endif; ?>
        <p><strong>Latitude:</strong> <?php echo esc_html($lat); ?></p>
        <p><strong>Longitude:</strong> <?php echo esc_html($lng); ?></p>
    </div>
</div>


<?php
$cards = get_post_meta(get_the_ID(), 'location_cards', true);

if (!empty($cards) && is_array($cards)) :
?>
<div class="main-tabs-wrapper">

    <!-- Main Tab Buttons -->
    <ul class="main-tab-buttons">
        <?php foreach ($cards as $card_index => $card) : ?>
            <li class="main-tab-button <?php echo $card_index === 0 ? 'active' : ''; ?>"
                data-target="main-tab-<?php echo $card_index; ?>">
                <?php echo esc_html($card['title']); ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Main Tab Contents -->
    <div class="main-tab-contents">
        <?php foreach ($cards as $card_index => $card) : ?>
            <div id="main-tab-<?php echo $card_index; ?>"
                 class="main-tab-content <?php echo $card_index === 0 ? 'active' : ''; ?>">

                <?php if (!empty($card['tabs']) && is_array($card['tabs'])) : ?>
                    <?php foreach ($card['tabs'] as $tab) : ?>
                        <div class="nested-tab-block">
                            <?php if (!empty($tab['title'])) : ?>
                                <h3><?php echo esc_html($tab['title']); ?></h3>
                            <?php endif; ?>

                            <?php if (!empty($tab['image'])) : ?>
                                <div class="nested-tab-image">
                                    <img src="<?php echo esc_url($tab['image']); ?>" alt="">
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($tab['content'])) : ?>
                                <div class="nested-tab-text">
                                    <?php echo wpautop(wp_kses_post($tab['content'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>

</div>

<style>
.main-tabs-wrapper { margin: 20px 0; }
.main-tab-buttons { display: flex; gap: 10px; list-style: none; padding: 0; margin: 0 0 20px; }
.main-tab-button { padding: 8px 14px; background: #ddd; cursor: pointer; border-radius: 5px; }
.main-tab-button.active { background: #0073aa; color: #fff; }
.main-tab-content { display: none; }
.main-tab-content.active { display: block; }
.nested-tab-block { margin-bottom: 25px; }
.nested-tab-image img { max-width: 300px; height: auto; border-radius: 8px; display: block; margin-bottom: 8px; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".main-tab-button").forEach(function (btn) {
        btn.addEventListener("click", function () {
            let target = this.getAttribute("data-target");

            document.querySelectorAll(".main-tab-button").forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            document.querySelectorAll(".main-tab-content").forEach(c => c.classList.remove("active"));
            document.getElementById(target).classList.add("active");
        });
    });
});
</script>
<?php endif; ?>




<!-- Leaflet JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const map = L.map('single-location-map').setView([<?php echo $lat; ?>, <?php echo $lng; ?>], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const tooltipContent = `
        <strong><?php echo esc_js($title); ?></strong><br>
        <?php if ($img): ?>
            <img src="<?php echo esc_url($img); ?>" style="width: 100px; height: auto; margin-top: 5px;"><br>
        <?php endif; ?>
        <?php if ($phone): ?>
            <span style="font-size:12px;"><?php echo esc_js($phone); ?></span><br>
        <?php endif; ?>
        <a href="<?php echo esc_url($permalink); ?>" style="font-size:12px;">View Details</a>
    `;

    const marker = L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>]).addTo(map);
    marker.bindTooltip(tooltipContent, {
        direction: 'top',
        permanent: true,
        sticky: true,
        className: 'custom-tooltip'
    }).openTooltip();
});
</script>

<style>

.location-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}
.location-card-item {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 15px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
.card-title {
    font-size: 1.4rem;
    margin-bottom: 10px;
}
.card-tabs {
    border-top: 1px solid #eee;
    padding-top: 10px;
}
.card-tab-item {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px dashed #ccc;
}
.tab-title {
    font-weight: bold;
    margin-bottom: 5px;
}
.tab-image img {
    max-width: 100%;
    height: auto;
    margin-bottom: 8px;
    border-radius: 4px;
}
.tab-content {
    font-size: 0.95rem;
}



.location-card {
    border: 1px solid #ddd;
    padding: 15px;
    max-width: 400px;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    background: #f9f9f9;
    margin-top: 20px;
}

.location-card img.location-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
    margin-bottom: 10px;
    border-radius: 5px;
}
</style>

<?php get_footer(); ?>
