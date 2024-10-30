<?php

if ( !class_exists('CMG') ) {
    class CMG
    {

        private static $initiated = false;
        public static $cmg_version_type = 'wp';
        public static $cmg_global_data = [];

        public static function init()
        {

            if (!self::$initiated) {
                self::init_hooks();
                do_action('CMG_INIT');
            }

        }

        /**
         * Initializes WordPress hooks
         */
        private static function init_hooks()
        {
            self::$initiated = true;

            self::$cmg_global_data['buy_url'] = 'https://contentmagnets.com/';

            add_action('template_redirect', array('CMG', 'check_page_template'));

            add_action('wp_enqueue_scripts', array('CMG', 'load_resources'));

            add_action('rest_api_init', function () {
                $latest_posts_controller = new CMG_REST_Controller();
                $latest_posts_controller->register_routes();
            });

            add_action('wp_ajax_cmg_magnet_me', array('CMG', 'cmg_magnet_me'));
            add_action('wp_ajax_nopriv_cmg_magnet_me', array('CMG', 'cmg_magnet_me'));

            add_action('pre_get_posts', array('CMG', 'hide_cmg_capture_bad_status_posts'), 10, 1);
            add_filter('views_edit-cmg_capture', array('CMG', 'remove_statuses_cmg_capture_post'), 10, 1);
            add_filter('manage_cmg_capture_posts_columns', array('CMG', 'manage_cmg_capture_posts_columns'), 10, 1);

            //REST API
            add_action('rest_insert_post', array('CMG', 'cmg_save_post'), 10, 3);
            add_action('rest_insert_page', array('CMG', 'cmg_save_post'), 10, 3);
            add_action('rest_insert_cmg_magnet', array('CMG', 'cmg_save_post'), 10, 3);
        }

        public static function plugin_activation()
        {

        }

        public static function check_page_template()
        {
            if (is_page()) {
                $cmg_archive_option = get_option('cmg_archive_option');
                $cmg_archive_page = isset_return($cmg_archive_option, 'cmg_archive_page');
                if ($cmg_archive_page && is_page() && get_the_ID() == $cmg_archive_page) {
                    require_once(CMG_PLUGIN_DIR . 'view/front/cmg-archive-template.php');
                    exit;
                }
            }
        }

        public static function load_resources()
        {
            //CMG_PLUGIN_URL.'resources/style/admin_style.css
            wp_enqueue_script('cmg-front-js', CMG_PLUGIN_URL . 'resources/js/front_js.js', array('jquery'), CMGVersion, true);

            wp_enqueue_style(
                'fontawesome',
                CMG_PLUGIN_URL . 'resources/font-awesome/css/font-awesome.min.css',
                null,
                CMGVersion
            );
            wp_enqueue_style('cmg_style', CMG_PLUGIN_URL . 'resources/style/front_style.css', array(), CMGVersion, 'all');
            wp_localize_script('cmg-front-js', 'cmg_infos',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'home_url' => home_url(),
                    'cmg_version_type' => self::$cmg_version_type,
                    'co_branded' => self::cmg_co_branded(),
                    'cmg_website_url' => self::cmg_website_url()
                ) //data-cfasync="false"
            );
        }

        public static function remove_statuses_cmg_capture_post($views)
        {
            $new_view = array(
                'all' => $views['all'],
                'trash' => $views['trash'],
            );
            return $new_view;
        }

        public static function hide_cmg_capture_bad_status_posts($query) {
            // We have to check if we are in admin and check if current query is the main query and check if you are looking for product post type
            /*if(is_admin() && $query->is_main_query() && $query->get('post_type') == "cmg_capture") {
                $query->set('post_status',array('publish', 'trash'));
            }*/
        }

        public static function manage_cmg_capture_posts_columns($columns) {
            unset($columns['tags'], $columns['date'], $columns['author'], $columns['categories'], $columns['comments']);
            return $columns;
        }

        public static function cmg_save_post($post, $request, $creating)
        {
            $post_content = $request['content'];

            if (has_blocks($post_content)) {
                $blocks = parse_blocks($post_content);
                foreach ($blocks as $block) {
                    if ($block['blockName'] === 'cmg/form-block') {
                        //cmg_print($post); cmg_print( $block );

                        $post_type = 'cmg_magnet';
                        $magnet_id = ($block['attrs']['cmg_id']) ? $block['attrs']['cmg_id'] : $block['attrs']['magnetPost'];
                        if (!$magnet_id)
                            return;
                        $magnet_title = trim(sanitize_text_field( $block['attrs']['title'] ) );
                        $magnet_content = sanitize_textarea_field( $block['attrs']['subtitle'] );
                        $mediaID = sanitize_textarea_field( $block['attrs']['mediaID'] );

                        $post_data = array(
                            'post_status' => 'publish',
                            //'post_title' => $magnet_title,
                            //'post_content' => $magnet_content,
                            'post_type' => $post_type,
                        );
                        $post_data['ID'] = $magnet_id;

                        wp_update_post($post_data);

                        //update_post_meta($magnet_id, 'cmg_single_content', $magnet_content);

                        //set_post_thumbnail
                        set_post_thumbnail($magnet_id, $mediaID);

                        //Update meta fields
                        $cmg_single_options = get_post_meta($magnet_id, 'cmg_single_options', true);
                        if (!empty($cmg_single_options)) {
                            $cmg_single_options_new = array_merge($cmg_single_options, $block['attrs']);
                        } else {
                            $cmg_single_options_new = $block['attrs'];
                        }
                        update_post_meta($magnet_id, 'cmg_excludeFromArchive', $cmg_single_options_new['excludeFromArchive']);
                        //cmg_print($cmg_single_options_new);

                        update_post_meta($magnet_id, 'cmg_single_options', $cmg_single_options_new);
                        do_action( 'cmg_save_post', $magnet_id, $block ); // Hook CMG save post
                    }
                }

            }
        }

        public static function update_magnet_post($post_data, $post_fields)
        {

            wp_update_post($post_data);
        }



        /**
         * Sanitize Basic Settings
         * This function is defined in register_setting().
         * @since 0.1.0
         */
        public static function cmg_sanitize_data($data, $key='') //subtitle
        {
            if( $key == 'subtitle' ){
                //$data = sanitize_textarea_field($data);
            } else {
                if( is_array($data) ){
                    $data = array_map( 'sanitize_text_field',  $data );
                } else {
                    $data = sanitize_text_field($data);
                }
            }

            return $data;
        }

        /**
         * Removes all connection options
         * @static
         */
        public static function plugin_deactivation()
        {

        }

        public static function cmg_website_url()
        {
            return 'https://contentmagnets.com/';
        }

        public static function get_magnets_count()
        {
            if( !isset($cmg_global_data['count_magnets']) ){
                $count_magnets = wp_count_posts( 'cmg_magnet' );
                $cmg_global_data['count_magnets'] = $count_magnets->publish;
            }
            return $cmg_global_data['count_magnets'];
        }

        public static function cmg_co_branded()
        {
            $website_url = self::cmg_website_url();
            return '<div class="cmg_co_branded"> Powered by <a href="'.$website_url.'" target="_blank">Content Magnets</a> </div>';
        }

        public static function cmg_getUserIP()
        {
            // Get real visitor IP behind CloudFlare network
            if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            }
            $client = @$_SERVER['HTTP_CLIENT_IP'];
            $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
            $remote = $_SERVER['REMOTE_ADDR'];

            if (filter_var($client, FILTER_VALIDATE_IP)) {
                $ip = $client;
            } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
                $ip = $forward;
            } else {
                $ip = $remote;
            }

            return $ip;
        }

        /*
         * AJAX
         */

        public static function cmg_magnet_me()
        {

            if (!(isset($_REQUEST['action']) && 'cmg_magnet_me' == sanitize_text_field( $_POST['action'] ) ))
                return;

            $return = array(
                'status' => '0',
                'html' => ''
            );
            $html = '';
            $validation = true;


            $user_ip = self::cmg_getUserIP();

            $post_type = 'cmg_capture';

            //Check for wp simple version
            $count_magnet_captures = wp_count_posts( $post_type );
            if( $count_magnet_captures->publish > 50 ){ //Only if NON-Pro
                $website_url = self::cmg_website_url();
                $html = '<div class="cmg_upgrade_msg"><p>The free version collected emails are limited - Max.50</p>';
                $html .= '<p>Please <a href="'.$website_url.'" target="_blank">Upgrade to Pro</a></p></div>';

                $return['html'] = $html;
                echo json_encode($return);
                exit;
            }

            $magnet_email = sanitize_email( $_POST['magnet_email'] );
            $post_title = $magnet_email;
            $magnet_post_id = sanitize_text_field( $_POST['magnet_post_id'] );
            $date_time = date("Y-m-d H:i:s");
            //Validate email address
            if (!is_email($magnet_email)) {
                $validation = false;
                $return['html'] = __('Email address is invalid.', CMG_NAME);
            }

            if ($validation) {
                //Check if email exist in our DB
                $post_id = '';
                $args = array(
                    'post_type' => $post_type,
                    'meta_query' => array(
                        array(
                            'key' => 'magnet_email',
                            'value' => $magnet_email,
                            'compare' => '=',
                        )
                    )
                );
                $exist_magnet = get_posts($args);
                if (!empty($exist_magnet)) {
                    $exist_magnet_id = $exist_magnet[0]->ID;
                } else {
                    if( $magnet_post_id == 'archive' ){
                        $magnet_from = 'Resource Center';
                    } else {
                        $magnet_from = $magnet_post_id;
                    }
                    $post_content = '<p> <b>User Email: <b>' . $magnet_email . ' </p>';
                    $post_content .= '<p> <b>Magnet From Form: <b>' . $magnet_from . ' </p>';
                    $post_content .= '<p> <b>User IP: <b>' . $user_ip . ' </p>';
                    $post_content .= '<p> <b>Date/Time: <b>' . $date_time . ' </p>';

                    $args = array(
                        'post_type' => $post_type,
                        'post_title' => $post_title,
                        'post_status' => 'publish',
                        //'post_content' => $post_content,
                        'comment_status' => 'closed'
                    );
                    $post_id = wp_insert_post($args);

                    if ($post_id) {
                        // insert post meta
                        add_post_meta($post_id, 'magnet_email', $magnet_email);
                        add_post_meta($post_id, 'magnet_post_id', $magnet_post_id);
                        add_post_meta($post_id, 'magnet_user_ip', $user_ip);
                        add_post_meta($post_id, 'magnet_date_time', $date_time);
                    }
                }

                if( $magnet_post_id == 'archive' ){
                    $return = array(
                        'status' => '1',
                        'html' => 'Success',
                        'download_resource_html' => '',
                        'successMessageEnable' => 1,
                        'redirectURL' => '',
                        'redirectUrlEnable' => 0,
                        'resourceURL' => ''
                    );
                } else {
                    $cmg_single_options = get_post_meta($magnet_post_id, 'cmg_single_options', true);
                    $resourceURL = (isset_return($cmg_single_options, 'resourceURL')) ? $cmg_single_options['resourceURL'] : '';
                    $download_resource_html = '<div class="cmg_download_resource_div"><a href="'.$resourceURL.'" target="_blank">'.__('Download Resource', CMG_NAME).'</a> </div>';
                    $return = array(
                        'status' => '1',
                        'html' => (isset_return($cmg_single_options, 'successMessage')) ? $cmg_single_options['successMessage'] : 'Success',
                        'download_resource_html' => $download_resource_html,
                        'successMessageEnable' => (isset_return($cmg_single_options, 'successMessageEnable')) ? 1 : 0,
                        'redirectURL' => (isset_return($cmg_single_options, 'redirectURL')) ? $cmg_single_options['redirectURL'] : '',
                        'redirectUrlEnable' => (isset_return($cmg_single_options, 'redirectUrlEnable') ) ? 1 : 0,
                        'resourceURL' => $resourceURL
                    );
                }

                $magnet_info = array(
                    'magnet_email' => $magnet_email,
                    'post_title' => $post_title,
                    'post_id' => $post_id
                );
                do_action( 'cmg_magnet_created', $magnet_info, $magnet_post_id ); //Hook Magnet Created
            } else {
                $return['status'] = '0';
            }

            echo json_encode($return);
            exit;
        }

    }
}
