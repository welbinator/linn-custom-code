<?php
function display_upcoming_events($atts) {
    $atts = shortcode_atts(
        array(
            'count' => 6, // Default number of events to display
        ),
        $atts,
        'upcoming_events'
    );

    $event_count = intval($atts['count']); // Number of events to display
    $event_count = $event_count > 0 ? $event_count : 6; // Default to 6 if invalid
    
    // Query upcoming events
    $args = array(
        'post_type'      => 'tribe_events',
        'posts_per_page' => $event_count,
        'meta_query'     => array(
            array(
                'key'     => '_EventStartDate',
                'value'   => current_time('Y-m-d H:i:s'),
                'compare' => '>=',
                'type'    => 'DATETIME',
            ),
        ),
        'orderby'        => 'meta_value',
        'meta_key'       => '_EventStartDate',
        'order'          => 'ASC',
    );

    $query = new WP_Query($args);

    if (!$query->have_posts()) {
        return 'No upcoming events found.';
    }

    ob_start();

    // Inline CSS for the event grid and card layout
    echo '<style>
        .events-container {
			margin-top: 50px;
		}
		.event-grid {
			display: grid;
			gap: 20px;
			grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
			max-width: 1215px;
		}
        .event-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .event-card-content {
            padding: 16px;
        }
        .event-title {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .event-title a {
            text-decoration: none;
            color: inherit;
        }
        .event-details {
            margin-top: 8px;
        }
        .event-card-footer {
            padding: 16px;
            border-top: 1px solid #ddd;
        }
        .event-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #005489;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .event-button:hover {
            background-color: #003b61;
        }
    </style>';

    echo '<div class="event-grid">';

    while ($query->have_posts()) {
        $query->the_post();
        $event_id = get_the_ID();
        ?>
        <div class="event-card">
            <a href="<?php echo esc_url(get_permalink($event_id)); ?>">
                <?php if (has_post_thumbnail($event_id)): ?>
                    <img src="<?php echo get_the_post_thumbnail_url($event_id, 'medium'); ?>" alt="Event image" class="event-image">
                <?php else: ?>
                    <img src="/path/to/placeholder-image.jpg" alt="Placeholder image" class="event-image">
                <?php endif; ?>
            </a>
            <div class="event-card-content">
                <h2 class="event-title">
                    <a href="<?php echo esc_url(get_permalink($event_id)); ?>"><?php echo esc_html(get_the_title($event_id)); ?></a>
                </h2>
                <div class="event-details">
                    <?php 
                    $event_date = tribe_get_start_date($event_id, true, 'F j, Y g:i a');
                    if ($event_date): ?>
                        <p><?php echo esc_html($event_date); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="event-card-footer">
                <a href="<?php echo esc_url(get_permalink($event_id)); ?>" class="event-button">Read More</a>
            </div>
        </div>
        <?php
    }

    echo '</div>';

    wp_reset_postdata();

    return ob_get_clean();
}

add_shortcode('upcoming_events', 'display_upcoming_events');
