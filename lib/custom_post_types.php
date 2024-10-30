<?php
// Register Custom Post Type
if ( !function_exists('cmg_custom_post_types') ){
    function cmg_custom_post_types() {

        $labels = array(
            'name'                  => _x( 'Magnets', 'Post Type General Name', CMG_NAME ),
            'singular_name'         => _x( 'Magnet', 'Post Type Singular Name', CMG_NAME ),
            'menu_name'             => __( 'Magnets', CMG_NAME ),
            'name_admin_bar'        => __( 'Magnet', CMG_NAME ),
            'archives'              => __( 'Magnet Archives', CMG_NAME ),
            'attributes'            => __( 'Magnet Attributes', CMG_NAME ),
            'parent_item_colon'     => __( 'Parent Magnet:', CMG_NAME ),
            'all_items'             => __( 'Magnets', CMG_NAME ),
            'add_new_item'          => __( 'Add New Magnet', CMG_NAME ),
            'add_new'               => __( 'Add New', CMG_NAME ),
            'new_item'              => __( 'New Magnet', CMG_NAME ),
            'edit_item'             => __( 'Edit Magnet', CMG_NAME ),
            'update_item'           => __( 'Update Magnet', CMG_NAME ),
            'view_item'             => __( 'View Magnet', CMG_NAME ),
            'view_items'            => __( 'View Magnets', CMG_NAME ),
            'search_items'          => __( 'Search Magnet', CMG_NAME ),
            'not_found'             => __( 'Not found', CMG_NAME ),
            'not_found_in_trash'    => __( 'Not found in Trash', CMG_NAME ),
            'featured_image'        => __( 'Featured Image', CMG_NAME ),
            'set_featured_image'    => __( 'Set featured image', CMG_NAME ),
            'remove_featured_image' => __( 'Remove featured image', CMG_NAME ),
            'use_featured_image'    => __( 'Use as featured image', CMG_NAME ),
            'insert_into_item'      => __( 'Insert into Magnet', CMG_NAME ),
            'uploaded_to_this_item' => __( 'Uploaded to this Magnet', CMG_NAME ),
            'items_list'            => __( 'Magnets list', CMG_NAME ),
            'items_list_navigation' => __( 'Magnets list navigation', CMG_NAME ),
            'filter_items_list'     => __( 'Filter Magnets list', CMG_NAME ),
        );
        $args = array(
            'label'                 => __( 'Magnet', CMG_NAME ),
            'description'           => __( 'The lead Magnets you have created.', CMG_NAME ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'thumbnail', 'editor' ),
            'taxonomies'            => array(),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'content-magnets_settings',
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-share-alt',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => false,
            'capability_type'       => 'page',
            'show_in_rest' => true
        );
        if( isset( $_GET['cmg_preview'] ) ){
            array_push($args['supports'], 'editor');
            $args['show_in_rest'] = true; //,
        }


        register_post_type( 'cmg_magnet', $args );

        $labels = array(
            'name'                  => _x( 'Subscribers', 'Post Type General Name', CMG_NAME ),
            'singular_name'         => _x( 'Subscriber', 'Post Type Singular Name', CMG_NAME ),
            'menu_name'             => __( 'Subscribers', CMG_NAME ),
            'archives'              => __( 'Item Archives', CMG_NAME ),
            'attributes'            => __( 'Item Attributes', CMG_NAME ),
            'all_items'             => __( 'Subscribers', CMG_NAME ),
            'add_new_item'          => __( 'Add New Item', CMG_NAME ),
            'add_new'               => __( 'Add New', CMG_NAME ),
            'new_item'              => __( 'New Item', CMG_NAME ),
            'edit_item'             => __( 'Edit Item', CMG_NAME ),
            'update_item'           => __( 'Update Item', CMG_NAME ),
            'view_item'             => __( 'View Item', CMG_NAME ),
            'view_items'            => __( 'View Items', CMG_NAME ),
            'search_items'          => __( 'Search Item', CMG_NAME ),
            'not_found'             => __( 'Not found', CMG_NAME ),
            'not_found_in_trash'    => __( 'Not found in Trash', CMG_NAME ),
            'featured_image'        => __( 'Featured Image', CMG_NAME ),
            'set_featured_image'    => __( 'Set featured image', CMG_NAME ),
            'remove_featured_image' => __( 'Remove featured image', CMG_NAME ),
            'use_featured_image'    => __( 'Use as featured image', CMG_NAME ),
            'insert_into_item'      => __( 'Insert into item', CMG_NAME ),
            'uploaded_to_this_item' => __( 'Uploaded to this item', CMG_NAME ),
            'items_list'            => __( 'Items list', CMG_NAME ),
            'items_list_navigation' => __( 'Items list navigation', CMG_NAME ),
            'filter_items_list'     => __( 'Filter items list', CMG_NAME ),
        );

        $args = array(
            'label'                 => __( 'Subscriber', CMG_NAME ),
            'description'           => __( 'The lead Subscribers you have created.', CMG_NAME ),
            'labels'                => $labels,
            'supports'              => array( 'title' ), /*, 'editor'*/
            'taxonomies'            => array( ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => 'content-magnets_settings',
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-tickets',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'page',
            //'show_in_rest'          => true,
        );


        register_post_type( 'cmg_capture', $args );
    }
}
add_action( 'init', 'cmg_custom_post_types', 0 );
