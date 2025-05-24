<?php
function bb_list_public_groups_by_type_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(
        array(
            'max'        => 10,               // Number of groups to show
            'group_type' => '',               // Slug of the group type (e.g., health-topic)
        ),
        $atts
    );

    // Check required BuddyBoss function exists
    if (!function_exists('groups_get_groups')) {
        return '<p>Error: BuddyBoss/BuddyPress is not active.</p>';
    }

    $args = array(
        'type'        => 'alphabetical',
        'per_page'    => intval($atts['max']),
        'show_hidden' => false, // Only show public groups
    );

    // Add group_type filter if specified
    if (!empty($atts['group_type'])) {
        $args['group_type'] = sanitize_text_field($atts['group_type']);
    }

    $groups = groups_get_groups($args);

    if (empty($groups['groups'])) {
        return '<p>No public groups found.</p>';
    }

    $output = '<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; padding: 1.5rem;">';

    foreach ($groups['groups'] as $group) {
        $group_avatar = bp_core_fetch_avatar(array(
            'item_id' => $group->id,
            'object'  => 'group',
            'type'    => 'thumb',
            'html'    => true,
        ));

        $cover_image = bp_attachments_get_attachment('url', array(
            'object_dir' => 'groups',
            'item_id'    => $group->id,
        ));
        $cover_image = !empty($cover_image) ? esc_url($cover_image) : 'https://via.placeholder.com/300x150';

        $group_url = bp_get_group_permalink($group);
        $last_active = bp_core_time_since($group->last_activity);
        $description = wp_trim_words($group->description, 30, '...');

        $output .= '<div class="card" style="border: 1px solid #ddd; border-radius: 0.5rem; overflow: hidden; background-color: #fff;">';
        $output .= '<a href="' . esc_url($group_url) . '" style="display: block; position: relative; height: 150px; background-image: url(' . $cover_image . '); background-size: cover; background-position: center;"></a>';
        $output .= '<div class="card-content" style="padding: 1rem;">';
        $output .= '<div class="flex" style="display: flex; gap: 1rem; margin-bottom: 1rem;">';
        $output .= '<div style="min-width:90px;"><a href="' . esc_url($group_url) . '" class="home-group-avatar" style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden;">' . $group_avatar . '</a></div>';
        $output .= '<div>';
        $output .= '<h3 style="margin: 0; font-size: 1.25rem;"><a href="' . esc_url($group_url) . '" style="text-decoration: none; color: inherit;">' . esc_html($group->name) . '</a></h3>';
        $output .= '<p class="text-muted" style="margin: 0; color: #666; font-size: 0.875rem;">' . esc_html($description) . ' <a href="' . esc_url($group_url) . '" style="color: #0073aa; text-decoration: none;">Read more</a></p>';
        $output .= '</div>';
        $output .= '</div>'; // flex
        $output .= '</div>'; // card-content
        $output .= '<div class="card-footer" style="background-color: #f9f9f9; padding: 0.5rem 1rem; font-size: 0.875rem; color: #666;">';
        $output .= '<p style="margin: 0;">Last activity: ' . esc_html($last_active) . '</p>';
        $output .= '</div>'; // footer
        $output .= '</div>'; // card
    }

    $output .= '</div>'; // grid

    return $output;
}
add_shortcode('bb_public_groups', 'bb_list_public_groups_by_type_shortcode');
