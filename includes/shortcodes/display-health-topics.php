<?php
function shortcode_health_topics_grid( $atts ) {
    $atts = shortcode_atts([
        'max' => 0, // 0 = no limit
    ], $atts );

    $terms_args = [
        'taxonomy'   => 'health_topic',
        'hide_empty' => false,
    ];

    // Apply the limit if greater than 0
    if ( intval( $atts['max'] ) > 0 ) {
        $terms_args['number'] = intval( $atts['max'] );
    }

    $terms = get_terms( $terms_args );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return '<p>No health topics found.</p>';
    }

    ob_start();
    echo '<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; padding-block: 1.5rem;">';

    foreach ( $terms as $term ) {
        $term_link = get_term_link( $term );
        $image_url = get_term_meta( $term->term_id, 'health_topic_image', true );

        if ( ! $image_url ) {
            $image_url = 'https://linn.crweb.design/wp-content/plugins/buddyboss-platform/bp-core/images/cover-image.png';
        }

        $description = wp_trim_words( $term->description, 40, '...' );

        echo '<div class="card" style="border: 1px solid #ddd; border-radius: 0.5rem; overflow: hidden; background-color: #fff;">';
        echo '<a href="' . esc_url( $term_link ) . '" style="display: block; position: relative; height: 150px; background-image: url(' . esc_url( $image_url ) . '); background-size: cover; background-position: center;"></a>';
        echo '<div class="card-content" style="padding: 1rem;">';
        echo '<div class="flex" style="display: flex; gap: 1rem; margin-bottom: 1rem;">';
        echo '<div>';
        echo '<h3 style="margin: 0; font-size: 1.25rem;"><a href="' . esc_url( $term_link ) . '" style="text-decoration: none; color: inherit;">' . esc_html( $term->name ) . '</a></h3>';
        echo '<p class="text-muted" style="margin: 0; color: #666; font-size: 0.875rem;">' . esc_html( $description ) . ' <a href="' . esc_url( $term_link ) . '" style="color: #0073aa; text-decoration: none;">Read more</a></p>';
        echo '</div></div></div></div>';
    }

    echo '</div>';
    return ob_get_clean();
}
add_shortcode( 'health_topics_grid', 'shortcode_health_topics_grid' );
