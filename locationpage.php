<?php
/*
 * Template Name: location Page
 */
get_header();
// Basic scores and grade
?>
<main>
     <?php echo do_shortcode('[location_categories_map]'); ?>
</main>
<style>
    .leaflet-touch .leaflet-control-attribution, .leaflet-touch .leaflet-control-layers, .leaflet-touch .leaflet-bar {
    box-shadow: none;
    display: none !important;
}
</style>
<?php get_footer(); ?>
