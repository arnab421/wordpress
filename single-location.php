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
