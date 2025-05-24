<?php

add_action('admin_menu', function () {
    add_menu_page(
        'Health Topics',                                 // Page title
        'Health Topics',                                 // Menu title
        'manage_options',                                // Capability
        'edit-tags.php?taxonomy=health_topic',           // Menu slug (links to taxonomy)
        '',                                              // No callback needed — just redirect
        'dashicons-heart',                               // Icon (can use BuddyBoss SVG if you prefer)
        25                                               // Position in the menu
    );
});


// Register the taxonomy so it's manageable in the admin
function register_health_topic_taxonomy() {
    register_taxonomy(
    'health_topic',
    ['group_fake_type', 'post'], // <-- Add 'post' here
    array(
        'labels' => array(
            'name' => 'Health Topics',
            'singular_name' => 'Health Topic',
            'search_items' => 'Search Health Topics',
            'all_items' => 'All Health Topics',
            'edit_item' => 'Edit Health Topic',
            'update_item' => 'Update Health Topic',
            'add_new_item' => 'Add New Health Topic',
            'new_item_name' => 'New Health Topic Name',
            'menu_name' => 'Health Topics',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => false,
        'show_in_rest'      => true,
        'rewrite'           => array('slug' => 'health-topic'),
        'public'            => true, // <-- Helps ensure it's queryable
    )
);

}
add_action( 'init', 'register_health_topic_taxonomy' );

// Show meta box on BuddyBoss Group admin screen
function add_health_topic_metabox_to_group_admin() {
    add_meta_box(
        'health_topicdiv',
        __('Health Topics', 'textdomain'),
        'render_health_topic_metabox',
        get_current_screen()->id,
        'side',
        'default'
    );
}
add_action( 'bp_groups_admin_meta_boxes', 'add_health_topic_metabox_to_group_admin' );

// Render the checkbox UI for terms
function render_health_topic_metabox( $item ) {
    $group_id = $item->id;
    $selected_terms = (array) groups_get_groupmeta( $group_id, 'health_topic_terms', true );
    $terms = get_terms( array(
        'taxonomy'   => 'health_topic',
        'hide_empty' => false,
    ) );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        echo '<p>No health topics found.</p>';
        return;
    }

    echo '<ul>';
    foreach ( $terms as $term ) {
        $checked = in_array( $term->term_id, $selected_terms ) ? 'checked' : '';
        printf(
            '<li><label><input type="checkbox" name="health_topic_terms[]" value="%d" %s> %s</label></li>',
            esc_attr( $term->term_id ),
            $checked,
            esc_html( $term->name )
        );
    }
    echo '</ul>';
}

// Save selected terms into group meta (NOT taxonomy)
function save_health_topic_terms_to_groupmeta( $group_id ) {
    if ( ! current_user_can( 'bp_moderate' ) ) {
        return;
    }

    if ( isset( $_POST['health_topic_terms'] ) && is_array( $_POST['health_topic_terms'] ) ) {
        $term_ids = array_map( 'intval', $_POST['health_topic_terms'] );
        groups_update_groupmeta( $group_id, 'health_topic_terms', $term_ids );
    } else {
        groups_delete_groupmeta( $group_id, 'health_topic_terms' );
    }
}
add_action( 'bp_group_admin_edit_after', 'save_health_topic_terms_to_groupmeta' );


// Display terms from group meta
function display_health_topics_for_group( $group_id ) {
    $term_ids = (array) groups_get_groupmeta( $group_id, 'health_topic_terms', true );
    if ( empty( $term_ids ) ) {
        return 'No Health Topics assigned.';
    }

    $terms = get_terms( array(
        'taxonomy' => 'health_topic',
        'include'  => $term_ids,
        'hide_empty' => false,
    ) );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return 'No valid Health Topics found.';
    }

    $output = '<ul class="group-health-topics">';
    foreach ( $terms as $term ) {
        $output .= '<li>' . esc_html( $term->name ) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}

function shortcode_group_health_topics( $atts ) {
    $atts = shortcode_atts( array(
        'group_id' => 0,
    ), $atts );

    if ( ! function_exists( 'groups_get_groupmeta' ) ) {
        return '<p>BuddyBoss not active.</p>';
    }

    $group_id = absint( $atts['group_id'] );

    if ( ! $group_id && function_exists( 'bp_is_group' ) && bp_is_group() ) {
        $group = groups_get_current_group();
        $group_id = isset( $group->id ) ? $group->id : 0;
    }

    if ( ! $group_id ) {
        return '<p>Group ID not found.</p>';
    }

    return display_health_topics_for_group( $group_id );
}

add_shortcode( 'group_health_topics', 'shortcode_group_health_topics' );

add_action( 'health_topic_edit_form_fields', function( $term ) {
    $image = get_term_meta( $term->term_id, 'health_topic_image', true );
    $resources = get_term_meta( $term->term_id, 'health_topic_resources', true );
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="health_topic_image"><?php _e( 'Image', 'textdomain' ); ?></label></th>
        <td>
            <input type="text" id="health_topic_image" name="health_topic_image" value="<?php echo esc_attr( $image ); ?>" class="regular-text" />
            <button class="upload_image_button button"><?php _e( 'Upload/Add image', 'textdomain' ); ?></button>
            <p class="description"><?php _e( 'Select an image for this health topic.', 'textdomain' ); ?></p>
        </td>
    </tr>

    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="health_topic_resources"><?php _e( 'Resources', 'textdomain' ); ?></label></th>
        <td>
            <?php
            wp_editor(
                htmlspecialchars_decode( $resources ),
                'health_topic_resources',
                array(
                    'textarea_name' => 'health_topic_resources',
                    'textarea_rows' => 5,
                    'media_buttons' => false,
                    'teeny' => true,
                )
            );
            ?>
            <p class="description"><?php _e( 'Add related resources or links for this health topic.', 'textdomain' ); ?></p>
        </td>
    </tr>
    <?php
});

// Add image field to Add screen
add_action( 'health_topic_add_form_fields', function() {
    ?>
    <div class="form-field term-group">
        <label for="health_topic_image"><?php _e( 'Image', 'textdomain' ); ?></label>
        <input type="text" id="health_topic_image" name="health_topic_image" value="" class="regular-text" />
        <button class="upload_image_button button"><?php _e( 'Upload/Add image', 'textdomain' ); ?></button>
        <p class="description"><?php _e( 'Select an image for this health topic.', 'textdomain' ); ?></p>
    </div>
    <?php
} );

// Add image field to Edit screen
add_action( 'health_topic_edit_form_fields', function( $term ) {
    $image = get_term_meta( $term->term_id, 'health_topic_image', true );
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="health_topic_image"><?php _e( 'Image', 'textdomain' ); ?></label></th>
        <td>
            <input type="text" id="health_topic_image" name="health_topic_image" value="<?php echo esc_attr( $image ); ?>" class="regular-text" />
            <button class="upload_image_button button"><?php _e( 'Upload/Add image', 'textdomain' ); ?></button>
            <p class="description"><?php _e( 'Select an image for this health topic.', 'textdomain' ); ?></p>
        </td>
    </tr>
    <?php
} );

// Save image meta
add_action( 'created_health_topic', 'save_health_topic_image_meta' );
add_action( 'edited_health_topic', 'save_health_topic_image_meta' );
function save_health_topic_image_meta( $term_id ) {
    if ( isset( $_POST['health_topic_image'] ) ) {
        update_term_meta( $term_id, 'health_topic_image', esc_url_raw( $_POST['health_topic_image'] ) );
    }
	if ( isset( $_POST['health_topic_resources'] ) ) {
        update_term_meta( $term_id, 'health_topic_resources', wp_kses_post( $_POST['health_topic_resources'] ) );
    }
}

// Enqueue media uploader + inline script
add_action( 'admin_enqueue_scripts', function( $hook ) {
    if ( $hook !== 'edit-tags.php' && $hook !== 'term.php' ) {
        return;
    }

    if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'health_topic' ) {
        wp_enqueue_media();

        wp_register_script( 'health-topic-media', '' ); // dummy handle for inline use
        wp_enqueue_script( 'health-topic-media' );

        wp_add_inline_script( 'health-topic-media', <<<JS
        jQuery(document).ready(function ($) {
            function setupUploader(button, input) {
                button.on('click', function (e) {
                    e.preventDefault();
                    const customUploader = wp.media({
                        title: 'Choose Image',
                        button: { text: 'Use this image' },
                        multiple: false
                    });
                    customUploader.on('select', function () {
                        const attachment = customUploader.state().get('selection').first().toJSON();
                        input.val(attachment.url);
                    });
                    customUploader.open();
                });
            }

            setupUploader($('.upload_image_button'), $('#health_topic_image'));
        });
        JS );
    }
});
