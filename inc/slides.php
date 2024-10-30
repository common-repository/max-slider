<?php
// Registers the new post type 

function max_slides_post_type()
{
    register_post_type(
        'max_slides',
        array(
            'labels' => array(
                'name' => __('Max Slides', 'newzin_plg'),
                'singular_name' => __('Max Slides', 'newzin_plg'),
                'add_new' => __('Add New Max Slides', 'newzin_plg'),
                'add_new_item' => __('Add New Max Slide', 'newzin_plg'),
                'edit_item' => __('Edit Max Slide', 'newzin_plg'),
                'new_item' => __('Add New Max Slide', 'newzin_plg'),
                'view_item' => __('View Max Slide', 'newzin_plg'),
                'search_items' => __('Search Max Slide', 'newzin_plg'),
                'not_found' => __('No Max Slides found', 'newzin_plg'),
                'not_found_in_trash' => __('No Max Slides found in trash', 'newzin_plg')
            ),
            'public' => true,
            'supports' => array('title'),
            'capability_type' => 'post',
            'rewrite' => array("slug" => "max_slides"), // Permalinks format
            'menu_position' => 5,
            'menu_icon'           => 'dashicons-art',
            'exclude_from_search' => true
        )
    );
}
add_action('init', 'max_slides_post_type');

function max_slides_mb()
{

    /**
     * Create a custom meta boxes array that we pass to 
     * the reduxoptions Meta Box API Class.
     */
    $max_slides_mb = array(
        'id'          => 'max_slides_meta_box',
        'title'       => esc_html__('Notes:', 'newzin_plg'),
        'desc'        => '',
        'pages'       => array('max_slides'),
        'context'     => 'normal',
        'priority'    => 'high',
        'fields'      => array(
            array(
                'id'          => 'footer_setting_block',
                'label'       => '',
                'desc'        => esc_html__('You can build your custom max slides with elementor and use it in any page using the page settings.<br/>
		Make sure you have checklist the Custom Header in Elementor Settings-> Post Type', 'newzin_plg'),
                'std'         => '',
                'type'        => 'textblock-titnewzin',
                'rows'        => '',
                'post_type'   => '',
                'taxonomy'    => '',
                'min_max_step' => '',
                'class'       => '',
                'condition'   => '',
                'operator'    => 'and'
            ),

        )
    );

    /**
     * Register our meta boxes using the 
     * ot_register_meta_box() function.
     */
    if (function_exists('ot_register_meta_box')) ot_register_meta_box($max_slides_mb);
}

add_action('admin_init', 'max_slides_mb');
