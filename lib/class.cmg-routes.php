<?php

if ( !class_exists('CMG_REST_Controller') ) {
    class CMG_REST_Controller extends WP_REST_Controller {
        public function register_routes() {
            $namespace = 'cmg/v1';
            //$path = 'change_create_magnet/(?P<magnet_id>\d+)';
            $path = 'change_create_magnet/(?P<magnet_id>\d+)';

            register_rest_route( $namespace, '/' . $path, [
                array(
                    'methods'             => 'POST',
                    'callback'            => array( $this, 'change_create_magnet' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' )
                ),
            ]);
        }

        public function get_items_permissions_check($request) {
            return current_user_can('edit_posts');
        }

        public function get_items($request) {

            $args = array(
                'category' => sanitize_text_field( $request['category_id'] )
            );

            $posts = get_posts($args);


            if (empty($posts)) {

                return new WP_Error( 'empty_category', __('There is no post in this category.', CMG_NAME), array( 'status' => 404 ) );
            }
            return new WP_REST_Response($posts, 200);
        }

        public function change_create_magnet( $request ) {

            $magnet_id = sanitize_text_field( $request['magnet_id'] );
            $magnet_title = sanitize_title( $request['title'] );
            $magnet_content = ''; //sanitize_textarea_field( $request['content'] );
            $posts = array();
            $post_type = 'cmg_magnet';

            if( empty( $magnet_title ) ){
                return new WP_Error( 'magnet_title_missing', __('There is no title for this magnet', CMG_NAME), array('status' => 404) );
            }

            $return = array(
                'ok' => 'ok',
                'status' => '0',
                'html' => '',
                'id' => ''
            );

            if( $magnet_id ){
                $args = array(
                    'post_status' => 'any',
                    'post_type' => $post_type,
                    'post__in' => array($magnet_id)
                );
                $posts = get_posts($args);
            }

            $post_data = array(
                'post_status' => 'publish',
                'post_title' => $magnet_title,
                //'post_content' => $magnet_content,
                'post_type' => $post_type,
            );
            //$magnet_id = $post_id = 31;
            if ( !empty($posts) ) {
                $post_data['ID'] = $magnet_id;
                wp_update_post( $post_data );
                $return['post_id'] = $magnet_id;

                $return['html'] = __('Magnet updated successfully!', CMG_NAME);
            } else {
                $post_data['post_content'] = $magnet_content;
                $post_id = wp_insert_post( $post_data );
                $return['post_id'] = $post_id;

                $return['html'] = __('Magnet created successfully!', CMG_NAME );
            }

            $return['status'] = 1;

            $response = new WP_REST_Response($return);
            $response->set_status(200);

            return $response;
        }
    }
}
