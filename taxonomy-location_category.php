<?php get_header(); ?>
<div class="location-archive">
    <h1><?php single_term_title(); ?></h1>
    <div id="locations-map" style="height: 500px; margin-bottom: 20px;"></div>
    <?php if (have_posts()): ?>
        <div class="location-cards">
            <?php while (have_posts()):
                the_post();
                $lat = get_field('lat');
                $lng = get_field('lang');
                $img = get_field('location_image');
                $phone = get_field('location_phone');
                $title = get_the_title();
                $permalink = get_permalink();

                if (!$lat || !$lng) continue;
                ?>
                <div class="location-card"
                    data-lat="<?php echo esc_attr($lat); ?>"
                    data-lng="<?php echo esc_attr($lng); ?>"
                    data-name="<?php echo esc_attr($title); ?>"
                    data-image="<?php echo esc_url($img); ?>"
                    data-phone="<?php echo esc_attr($phone); ?>"
                    data-url="<?php echo esc_url($permalink); ?>"
                >
                    <?php if ($img): ?>
                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" class="location-image" />
                    <?php endif; ?>
                    <h3><?php echo esc_html($title); ?></h3>
                    <?php if ($phone): ?>
                        <p><?php echo esc_html($phone); ?></p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No locations found in this category.</p>
    <?php endif; ?>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<!-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('locations-map').setView([37.0902, -95.7129], 1);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const markers = [];

        document.querySelectorAll('.location-card').forEach(card => {
            const lat = parseFloat(card.dataset.lat);
            const lng = parseFloat(card.dataset.lng);
            const name = card.dataset.name;
            const image = card.dataset.image;
            const phone = card.dataset.phone;
            const url = card.dataset.url;

            let tooltipContent = `<strong>${name}</strong>`;
            if (image) {
                tooltipContent += `<br><img src="${image}" alt="${name}" style="width:100px;height:auto;">`;
            }
            if (phone) {
                tooltipContent += `<br><span style="font-size:12px;">${phone}</span>`;
            }
            tooltipContent += `<br><a href="${url}" style="font-size:12px;">View Details</a>`;

            const marker = L.marker([lat, lng]).addTo(map).bindTooltip(tooltipContent, {
                direction: 'top',
                permanent: false,
                sticky: true,
                opacity: 1,
                className: 'custom-tooltip'
            });

            markers.push({ marker, card });

            
            card.addEventListener('mouseenter', () => {
                map.setView([lat, lng], 8);
                marker.openTooltip();
            });
            marker.on('click', () => {
                marker.openTooltip();
            });
            card.addEventListener('mouseleave', () => {
                marker.closeTooltip();
            });
        });
    });
</script> -->

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('locations-map').setView([37.0902, -95.7129], 4);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const markers = [];

        document.querySelectorAll('.location-card').forEach(card => {
            let lat = parseFloat(card.dataset.lat);
            let lng = parseFloat(card.dataset.lng);
            const name = card.dataset.name;
            const image = card.dataset.image;
            const phone = card.dataset.phone;
            const url = card.dataset.url;

            // ADD A SMALL RANDOM OFFSET
            const randomOffset = () => (Math.random() - 0.5) * 0.02; // ~0.01 deg variation
            lat += randomOffset();
            lng += randomOffset();

            let tooltipContent = `<strong>${name}</strong>`;
            if (image) {
                tooltipContent += `<br><img src="${image}" alt="${name}" style="width:100px;height:auto;">`;
            }
            if (phone) {
                tooltipContent += `<br><span style="font-size:12px;">${phone}</span>`;
            }
            tooltipContent += `<br><a href="${url}" style="font-size:12px;">View Details</a>`;

            const marker = L.marker([lat, lng]).addTo(map).bindTooltip(tooltipContent, {
                direction: 'top',
                permanent: false,
                sticky: true,
                opacity: 1,
                className: 'custom-tooltip'
            });

            markers.push({ marker, card });

            card.addEventListener('mouseenter', () => {
                map.setView([lat, lng], 8);
                marker.openTooltip();
            });
            marker.on('click', () => {
                marker.openTooltip();
            });
            card.addEventListener('mouseleave', () => {
                marker.closeTooltip();
            });
        });
    });
</script>
<style>
.location-cards {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.location-card {
    border: 1px solid #ddd;
    padding: 10px;
    width: 250px;
    border-radius: 6px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    background: #f9f9f9;
    cursor: pointer;
    transition: 0.3s ease;
}

.location-card:hover {
    background: #e2f0ff;
    transform: scale(1.02);
}

.location-card img.location-image {
    width: 100%;
    height: 150px;
    object-fit: cover;
    margin-bottom: 5px;
    border-radius: 4px;
}

.location-card h3 {
    margin: 5px 0;
    font-size: 1rem;
}
</style>

<?php get_footer(); ?>
