<?php

if ( !class_exists('CMG_Admin') ) {
    class CMG_Admin
    {

        private static $initiated = false;
        public static $cmg_version_type = 'wp';

        public static function init()
        {
            if (!self::$initiated) {
                self::init_hooks();
                self::$cmg_version_type = CMG::$cmg_version_type;
                do_action('CMG_ADMIN_INIT');
            }
        }

        /**
         * Initializes WordPress hooks
         */
        private static function init_hooks()
        {
            self::$initiated = true;
            add_action('admin_menu', array('CMG_Admin', 'init_menus'), 10, 2);

            add_filter( 'admin_init', array('CMG_Admin', 'cmg_before_load_page'), 1 );
            add_filter( 'admin_init', array('CMG_Admin', 'cmg_the_content_filter'), 1 );

            // Add Meta Box
            add_action('add_meta_boxes', array('CMG_Admin', 'cmg_add_meta_boxes'));
            // Save Post Metabox
            add_action('save_post', array('CMG_Admin', 'save_cmg_magnet_meta_box_data'));

            //Notices
            add_action('admin_notices', array('CMG_Admin', 'cmg_notices'));

            // Reset Settings
            add_action('cmg_settings_page_init', array('CMG_Admin', 'cmg_reset_settings'));

            add_filter( 'default_content', array('CMG_Admin', 'cmg_editor_default_content'), 10, 2 );

            //add_action('admin_enqueue_scripts', array('CMG_Admin', 'cmg_global_assets'), 10);
            self::cmg_global_assets();
            add_action('enqueue_block_editor_assets', array('CMG_Admin', 'cmg_gutenberg_enqueue_block_editor_assets'), 100);
            //self::cmg_guten_block();

            add_action('wp_ajax_cmg_create_archive_page', array('CMG_Admin', 'cmg_create_archive_page'));
            add_action('wp_ajax_cmg_export_magnets', array('CMG_Admin', 'cmg_export_magnets'));
            add_action('wp_ajax_cmg_magnet_post_selected', array('CMG_Admin', 'cmg_magnet_post_selected'));

            //add_filter( 'manage_cmg_capture_posts_columns', array( 'CMG_Admin', 'set_custom_edit_cmg_capture_columns' ) );
            //add_action( 'manage_cmg_capture_posts_custom_column' , array( 'CMG_Admin', 'custom_cmg_capture_column'), 10, 2 );

            add_action('admin_head', array('CMG_Admin', 'cmg_admin_head') );
        }
        public static function init_menus()
        {
            $settings_page = add_menu_page('Content Magnets', 'Content Magnets', 'manage_options', 'content-magnets_settings', array('CMG_Admin', 'i_settings'),
                'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 32 32"><path fill="black" d="M18.2,12.4h6.6c0.5,0,0.8-0.4,0.8-0.8V6.3c0-0.5-0.4-0.8-0.8-0.8h-6.6c-7,0-11.9,4.3-11.9,10.6c0,6.2,4.9,10.5,11.9,10.5h1
	c0,0,0.1,0,0.1,0h5.4c0.5,0,0.8-0.4,0.8-0.8v-5.3c0-0.5-0.4-0.8-0.8-0.8c-0.5,0-0.8,0.4-0.8,0.8v4.4h-3.8v-3.6h1.9
	c0.5,0,0.8-0.4,0.8-0.8c0-0.5-0.4-0.8-0.8-0.8h-3.9c-4.1,0-4.9-1.4-4.9-3.6C13.3,13.8,14.2,12.4,18.2,12.4z M18.2,21.3h0.3v3.6h-0.3
	c-3,0-5.6-0.9-7.5-2.5C9,20.7,8,18.5,8,16c0-2.5,0.9-4.7,2.7-6.3c1.8-1.7,4.4-2.5,7.5-2.5H24v3.6h-3.7V8.4c0-0.5-0.4-0.8-0.8-0.8
	s-0.8,0.4-0.8,0.8v2.3h-0.4c-4.4,0-6.6,1.7-6.6,5.3S13.8,21.3,18.2,21.3z"/>
</svg>'),
                80);
            add_submenu_page('content-magnets_settings', 'Settings', 'Settings',
                'manage_options', 'content-magnets_settings');
            //add_submenu_page( 'content-magnets_settings', 'My Custom Submenu Page', 'My Custom Submenu Page', 'manage_options', 'content-magnets_cmg_magnet');

            // Register our setting.
            register_setting(
                'cmg',                         // Option Group
                'cmg_archive_option',                   // Option Name
                array('CMG_Admin', 'cmg_archive_option_sanitize')           // Sanitize Callback
            );

            /* Vars */
            $page_hook_id = self::cmg_setings_page_id();

            /* Do stuff in settings page, such as adding scripts, etc. */
            if (!empty($settings_page)) {

                /* Load the JavaScript needed for the settings screen. */
                //add_action( 'admin_enqueue_scripts', 'cmg_enqueue_scripts' );
                add_action("admin_footer-{$page_hook_id}", array('CMG_Admin', 'cmg_footer_scripts'));

                /* Set number of column available. */
                add_filter('screen_layout_columns', array('CMG_Admin', 'cmg_screen_layout_column'), 10, 2);

            }
            //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
            //add_menu_page( 'Wiser Plugin Core', 'Wiser Plugin Core', 'manage_options', 'wiser-plugin-core_settings', array( 'CMG_Admin' ,'i_settings'), 'dashicons-align-center', '80.08'  );
        }

        public static function cmg_archive_option_sanitize($input) {

            $input['cmg_archive_page'] =  sanitize_text_field($input['cmg_archive_page']);
            $input['page_title'] = sanitize_text_field($input['page_title']);
            $input['page_slug'] = sanitize_title_with_dashes($input['page_slug']);
            $input['page_description'] = sanitize_textarea_field($input['page_description']);

            if( !is_numeric($input['cmg_archive_page']) ){
                add_settings_error('cmg', 'cmg_archive_page_validation_error', __('Archive page ID is not correct!', CMG_NAME), 'error');
            }

            return $input; // return validated input
        }

        public static function cmg_setings_page_id()
        {
            return 'toplevel_page_content-magnets_settings';
        }

        public static function cmg_css_and_js($page = '')
        {

            //$page_hook_id = self::cmg_setings_page_id();
            if ($page == 'settings') {
                wp_enqueue_script('common');
                wp_enqueue_script('wp-lists');
                wp_enqueue_script('postbox');
            }

            wp_enqueue_style(
                'fontawesome',
                CMG_PLUGIN_URL . 'resources/font-awesome/css/font-awesome.min.css',
                null,
                CMGVersion
            );

            wp_enqueue_style('cmg_admin_style', CMG_PLUGIN_URL . 'resources/style/admin_style.css', array(), CMGVersion, 'all');

            wp_enqueue_script('cmg-admin-js', CMG_PLUGIN_URL . 'resources/js/admin_js.js', array('jquery'), CMGVersion, true);
            wp_localize_script('cmg-admin-js', 'cmg_infos',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'loadingmessage' => __('Saving your settings…', CMG_NAME),
                    'exportLoadingMessage' => __('Export is in progress…', CMG_NAME),
                    'cmg_version_type' => self::$cmg_version_type
                    /*'loading_img' => '<img src="'.CMG_PLUGIN_URL.'images/loading.gif" id="i_loading_img">',
                    'site_url' => site_url(),*/
                )
            );
        }


        public static function cmg_global_assets()
        {
            wp_enqueue_script(
                'cmg_global_js',
                CMG_PLUGIN_URL . 'resources/js/admin_global.js',
                array('jquery'),
                CMGVersion
            );

            wp_localize_script('cmg_global_js', 'cmg_infos',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'loadingmessage' => __('Saving your settings…', CMG_NAME),
                    'CMG_NAME' => CMG_NAME,
                    'cmg_version_type' => self::$cmg_version_type
                    /*'loading_img' => '<img src="'.CMG_PLUGIN_URL.'images/loading.gif" id="i_loading_img">',
                    'site_url' => site_url(),*/
                )
            );

            wp_enqueue_style(
                'cmg_font-awesome',
                CMG_PLUGIN_URL . 'resources/font-awesome/css/font-awesome.min.css',
                array('wp-edit-blocks'),
                CMGVersion
            );

            $magnets = get_posts(array(
                'post_type' => 'cmg_magnet',
                'showposts' => -1
            ));
            $magnets_data = array(array('label' => __('New magnet', CMG_NAME), 'value' => ''));
            if (!empty($magnets))
                foreach ($magnets as $magnet) {
                    $magnets_data[] = array('label' => $magnet->post_title, 'value' => $magnet->ID, 'text' => $magnet->post_title,);
                }

            wp_localize_script('cmg_global_js', 'cmg_global',
                array(
                    'magnets_data' => $magnets_data
                )
            );
        }

        //////// MCE / WYSIWYG
        public static function cmg_admin_head()
        {
            global $pagenow;
            echo '<style> body.post-type-cmg_capture .page-title-action, body #wp-admin-bar-new-cmg_capture{display: none;}';
            if( self::$cmg_version_type == 'wp' ){
                $count_magnets = CMG::get_magnets_count();
                if( $count_magnets > 3 ){
                    echo 'body.cmg-non-pro.post-type-cmg_magnet .page-title-action, body.cmg-non-pro #wp-admin-bar-new-cmg_magnet{display: none;}';
                }
            }
            echo '</style>';

            // Check if user have permission
            if (!current_user_can('edit_posts') || !current_user_can('edit_pages')) {
                return;
            }
            // Check if WYSIWYG is enabled
            if ('true' == get_user_option('rich_editing')) {
                add_filter('mce_external_plugins', array('CMG_Admin', 'cmg_tinymce_plugin'));
                add_filter('mce_buttons', array('CMG_Admin', 'cmg_register_mce_button'));
            }
            wp_enqueue_style('cmg_admin_global_style', CMG_PLUGIN_URL . 'resources/style/admin_global_style.css', array(), CMGVersion, 'all');
        }
        // Function for new button
        public static function cmg_tinymce_plugin( $plugin_array ) {
            $plugin_array['cmg_mce_button'] = CMG_PLUGIN_URL . 'resources/js/cmg_editor_plugin.js';
            return $plugin_array;
        }
        // Register new button in the editor
        public static function cmg_register_mce_button( $buttons ) {
            array_push( $buttons, 'cmg_mce_button' );
            return $buttons;
        }
        //do_action( 'before_wp_tiny_mce', self::$mce_settings );

        public static function cmg_notices(){
            global $pagenow;

            if ( $pagenow == 'edit.php' && $_GET['post_type'] == 'cmg_magnet') {
                if( self::$cmg_version_type == 'wp' ) {
                    $count_magnets = CMG::get_magnets_count();
                    if( $count_magnets > 3 ){
                        $txt = "Your visitors want more of your unique view! <br>";
                        $txt.= "Our free plan is limited to 3 free content magnets. <br>";
                        $txt.= "On our Pro Plan, you'll be able to add as many content magnets as you want. <br>";
                        $txt.= "Our Pro Plan gives you unlimited content magnets. <br>";

                        echo '<div class="cmg_notice notice notice-warning is-dismissible">';
                        echo '<h3>Want to give your visitors more learning opportunities?</h3>';
                        echo '<p>'.__($txt, CMG_NAME).'</p>';
                        echo '<a href="'.CMG::$cmg_global_data['buy_url'].'" target="_blank" class="button">'.__('Upgrade today!', CMG_NAME).'</a>';
                        echo '</div>';
                    }
                }
            }
        }
        /////// gutenberg
        public static function cmg_gutenberg_enqueue_block_editor_assets()
        {
            wp_enqueue_style(
                'cmg_block_guthenberg_style',
                CMG_PLUGIN_URL . 'resources/style/admin_guthenblock.css',
                array('wp-edit-blocks'),
                CMGVersion
            );

            wp_enqueue_script(
                'cmg_block_guthenberg',
                CMG_PLUGIN_URL . 'resources/js/admin_gutenblock.js',
                array('wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor'),
                CMGVersion
            );

            $magnet_id = 0;
            global $pagenow;
            if (( $pagenow == 'post.php' || $pagenow == 'post-new.php') && get_post_type() == 'cmg_magnet') {
                $magnet_id = get_the_ID();
            }
            wp_localize_script('cmg_block_guthenberg', 'cmg_gutenblock_info',
                array(
                    'cmg_in_single_magnet' => $magnet_id
                )
            );
        }

        public static function cmg_guten_block()
        {

            if (!function_exists('register_block_type')) {
                // Gutenberg is not active.
                return;
            }

            // Scripts.
            wp_register_script(
                'cmg_block_guthenberg', // Handle.
                CMG_PLUGIN_URL . 'resources/js/admin_gutenblock.js', // Block.js: We register the block here.
                array('wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-editor'), // Dependencies, defined above.
                CMGVersion, //filemtime( plugin_dir_path( __FILE__ ) . 'block.js' ),
                true // Load script in footer.
            );

            // Styles.
            wp_register_style(
                'cmg_block_guthenberg_style',
                CMG_PLUGIN_URL . 'resources/style/admin_guthenblock.css',
                array('wp-edit-blocks'),
                CMGVersion
            );

            // Here we actually register the block with WP, again using our namespacing.
            // We also specify the editor script to be used in the Gutenberg interface.
            register_block_type(
                'cmg/form-block',
                array(
                    'editor_script' => 'wp_register_script',
                    //'editor_style'  => 'organic-profile-block-editor-style',
                    'style' => 'cmg_block_guthenberg_style',
                )
            );

        } // End function organic_profile_block().

        /*cmg_editor_default_content*/
        public static function cmg_editor_default_content($content, $post)
        {
            switch( $post->post_type ) {
                case 'cmg_magnet':
                    $content = '<!-- wp:cmg/form-block --><!-- /wp:cmg/form-block -->';
                    break;
            }

            return $content;
        }
        /*
         * Improve post content if the content is broken and not right
         * */
        public static function is_gutenberg_editor() {
            if( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
                return true;
            }

            $current_screen = get_current_screen();
            if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
                return true;
            }
            return false;
        }

        public static function cmg_before_load_page()
        {
            global $pagenow;
            if( self::$cmg_version_type == 'wp' ){
                if( $pagenow == 'post-new.php' && $_GET['post_type'] == 'cmg_magnet' ){
                    $count_magnets = CMG::get_magnets_count();
                    //$magnet_id = get_the_ID();
                    if( $count_magnets > 3 ){
                        wp_redirect( admin_url('edit.php?post_type=cmg_magnet') );
                        exit;
                    }
                }

            }
        }

        public static function cmg_the_content_filter() {
            if ( is_admin() && isset($_GET['post']) ){
                $post_id = $_GET['post'];
                $post = get_post($post_id);
                if( $post->post_type == 'cmg_magnet' ){
                    $default_content = '<!-- wp:cmg/form-block --><!-- /wp:cmg/form-block -->';
                    $change_content = false;
                    $post_content = $post->post_content;
                    if (has_blocks($post_content)) {
                        $blocks = parse_blocks($post_content);
                        if( count($blocks) > 1 ){
                            $change_content = true;
                            foreach ($blocks as $block_k => $block) {
                                if ($block['blockName'] === 'cmg/form-block') {
                                    $default_content = serialize_block($block);
                                }
                            }
                        } else {
                            foreach ($blocks as $block_k => $block) {
                                if ($block['blockName'] !== 'cmg/form-block') {
                                    $change_content = true;
                                }
                            }
                        }
                    } else {
                        $change_content = true;
                    }
                    if( $change_content ){
                        $cmg_post = array(
                            'ID'           => $post_id,
                            'post_content' => $default_content,
                        );
                        wp_update_post( $cmg_post );
                    }
                }
            }
        }
        /**
         * Footer Script Needed for Meta Box:
         * - Meta Box Toggle.
         * - Spinner for Saving Option.
         * - Reset Settings Confirmation
         * @since 0.1.0
         */
        public static function cmg_footer_scripts()
        {
            $page_hook_id = self::cmg_setings_page_id();
            ?>
            <script type="text/javascript">
                //<![CDATA[
                jQuery(document).ready(function ($) {
                    // toggle
                    $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                    postboxes.add_postbox_toggles('<?php echo $page_hook_id; ?>');
                    // display spinner
                    $('#fx-smb-form').submit(function () {
                        $('#publishing-action .spinner').css('display', 'inline');
                    });
                    // confirm before reset
                    $('#delete-action .submitdelete').on('click', function () {
                        return confirm('Are you sure want to do this?');
                    });
                });
                //]]>
            </script>
            <?php
        }

        /**
         * Number of Column available in Settings Page.
         * we can only set to 1 or 2 column.
         * @since 0.1.0
         */
        public static function cmg_screen_layout_column($columns, $screen)
        {
            $page_hook_id = self::cmg_setings_page_id();
            if ($screen == $page_hook_id) {
                $columns[$page_hook_id] = 2;
            }
            return $columns;
        }

        public static function i_settings()
        {

            self::cmg_css_and_js('settings');
            //wp_enqueue_script( 'cmg-admin-js', CMG_PLUGIN_URL.'resources/js/admin_js.js' , array('jquery'), CMGVersion, true );

            require_once(CMG_PLUGIN_DIR . 'view/admin/cmg_settings.php');
        }


        /**
         * Basic Meta Box
         * @since 0.1.0
         * @link http://codex.wordpress.org/Function_Reference/add_meta_box
         */
        public static function cmg_add_meta_boxes()
        {

            $page_hook_id = self::cmg_setings_page_id();

            add_meta_box(
                'submitdiv',               /* Meta Box ID */
                'Save Options',            /* Title */
                array('CMG_Admin', 'cmg_submit_meta_box'),  /* Function Callback */
                $page_hook_id,                /* Screen: Our Settings Page */
                'side',                    /* Context */ //side, normal, hight
                'low'                     /* Priority */ //high
            );

            add_meta_box(
                'archive_setting',
                'Resource Center - Page Setting',
                array('CMG_Admin', 'cmg_archive_meta_box'),
                $page_hook_id,
                'normal',
                'high'
            );

            add_meta_box(
                'form_setting',
                'Form Settings',
                array('CMG_Admin', 'cmg_form_settings_meta_box'),
                $page_hook_id,
                'normal',
                'high'
            );

            /*add_meta_box(
                'capture_setting',
                'Capture Setting',
                array( 'CMG_Admin', 'cmg_capture_meta_box' ),
                $page_hook_id,
                'normal',
                'default'
            );

            add_meta_box(
                'ems_setting',
                '[EMS] Setting',
                array( 'CMG_Admin', 'cmg_ems_meta_box' ),
                $page_hook_id,
                'normal',
                'default'
            );*/

            //Magnet posts meta boxes
            add_meta_box(
                'cmg_single_magnet',
                'Magnet Setting',
                array('CMG_Admin', 'cmg_single_magnet_meta_box'),
                'cmg_magnet',
                'normal',
                'default'
            );

            //Magnet posts meta boxes
            add_meta_box(
                'cmg_single_capture',
                'Magnet Info',
                array('CMG_Admin', 'cmg_single_capture_meta_box'),
                'cmg_capture',
                'normal',
                'default'
            );
        }

        /**
         * Submit Meta Box Callback
         * @since 0.1.0
         */
        public static function cmg_submit_meta_box()
        {

            /* Reset URL */
            $reset_url = add_query_arg(array(
                'page' => 'cmg',
                'action' => 'reset_settings',
                '_wpnonce' => wp_create_nonce('fx-smb-reset', __FILE__),
            ),
                admin_url('admin.php')
            );

            ?>
            <div id="submitpost" class="submitbox">

                <div id="major-publishing-actions">

                    <div id="delete-action">
                        <a href="<?php echo esc_url($reset_url); ?>" class="submitdelete deletion">Reset Settings</a>
                    </div><!-- #delete-action -->

                    <div id="publishing-action">
                        <span class="spinner"></span>
                        <?php submit_button(esc_attr('Save'), 'primary', 'submit', false); ?>
                    </div>

                    <div class="clear"></div>

                </div><!-- #major-publishing-actions -->

            </div><!-- #submitpost -->

            <?php
        }


        /**
         * Delete Options
         * @since 0.1.0
         */
        public static function cmg_reset_settings()
        {

            // Check Action
            $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
            if ('reset_settings' == $action) {

                // Check User Capability
                if (current_user_can('manage_options')) {

                    // nonce
                    $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';

                    // valid
                    if (wp_verify_nonce($nonce, 'fx-smb-reset')) {

                        /**
                         * Get all registered Option Names in current Option Group
                         * ( thanks to @justintadlock )
                         * @since 0.1.1
                         * @link http://themehybrid.com/board/topics/how-to-get-all-option-name-in-option-group
                         */
                        global $new_whitelist_options;
                        $option_names = $new_whitelist_options['cmg'];

                        // Delete All Registered Option Names in the Group
                        foreach ($option_names as $option_name) {
                            delete_option($option_name);
                        }

                        // Utility hook.
                        do_action('cmg_reset');

                        // Add Update Notice
                        add_settings_error("cmg", "", "Settings reset to defaults.", 'updated');
                    } // not valid
                    else {
                        // Add Error Notice
                        add_settings_error("cmg", "", "Failed to reset settings. Please try again.", 'error');
                    }
                } // User Do Not Have Capability
                else {
                    // Add Error Notice
                    add_settings_error("cmg", "", "Failed to reset settings. You do not capability to do this action.", 'error');
                }
            }
        }

        /**
         * Archive Meta Box Callback
         * @since 0.1.0
         */
        public static function cmg_archive_meta_box()
        {
            $cmg_archive_option = get_option('cmg_archive_option', true);

            $field = array(
                'title' => __('Select Resource Center Page', CMG_NAME),
                'subtitle' => __('Select Resource Center Page', CMG_NAME),
                'heading' => '',
                'type' => 'post_selectbox',
                'id' => 'cmg_archive_option[cmg_archive_page]',
                'description' => '',
                'placeholder' => ''
            );
            ?>
            <div id="" class="cmg_metabox_content">
                <div class="cmg_option_div">
                    <?php echo CMG_CFIELD::post_selectbox($field, isset_return($cmg_archive_option, 'cmg_archive_page')); ?>
                </div>

                <h4>OR <a href="#cmg_create_new_page_div" class="i_toggle_a"> <?php _e('create a new one', CMG_NAME); ?> </a>
                </h4>

                <div id="cmg_create_new_page_div" class="i_create_new_page_div">
                    <div class="cmg_option_div clearfix">
                        <label for="cmg_page_title"><?php _e('Page Title', CMG_NAME); ?></label>
                        <input id="cmg_page_title" class="widefat" type="text" name="cmg_archive_option[page_title]"
                               value="<?php echo sanitize_text_field(isset_return($cmg_archive_option, 'page_title')); ?>">
                    </div>
                    <div class="cmg_option_div clearfix">
                        <label for="cmg_page_slug"><?php _e('Page Slug', CMG_NAME); ?></label>
                        <input id="cmg_page_slug" class="widefat" type="text" name="cmg_archive_option[page_slug]"
                               value="<?php echo sanitize_text_field(isset_return($cmg_archive_option, 'page_slug')); ?>">
                    </div>
                    <div class="cmg_option_div clearfix">
                        <label for="cmg_page_description"><?php _e('Page Description', CMG_NAME); ?></label>
                        <textarea id="cmg_page_description" class="widefat"
                                  name="cmg_archive_option[page_description]"><?php echo esc_textarea( isset_return($cmg_archive_option, 'page_description') ); ?></textarea>
                    </div>
                    <div class="cmg_option_div clearfix">
                        <a href="#" id="cmg_create_archive_page"
                           class="cmg_btn cmg_btn-primary with_mgt"><?php _e('Create Archive Page', CMG_NAME) ?></a>
                    </div>
                </div>
                <p class="howto"></p>
            </div>
            <?php
        }

        /**
         * Archive Meta Box Callback
         * @since 0.1.0
         */
        public static function cmg_form_settings_meta_box()
        {
            $magnets = get_posts(array(
                'post_type' => 'cmg_magnet',
                'showposts' => -1
            ));
            ?>
            <div id="" class="cmg_metabox_content">
                <div class="cmg_option_div clearfix">
                    <label for="cmg_export_magnets" class="cmg_export_magnets_lbl"><?php _e('Export Subscribers', CMG_NAME); ?></label>
                    <div class="cmg_option_options_div">
                        <a href="#" id="cmg_export_magnets"
                           class="cmg_btn cmg_btn-primary"><?php _e('Export', CMG_NAME) ?></a>
                    </div>

                </div>

                <p class="howto"></p>
            </div>
            <?php
        }

        /**
         * Capture Meta Box Callback
         * @since 0.1.0
         */
        public static function cmg_capture_meta_box()
        {
            //$cmg_archive_option = get_option( 'cmg_archive_option', '' );
            ?>
            Coming Soon
            <?php
        }

        /**
         * EMS Meta Box Callback
         * @since 0.1.0
         */
        public static function cmg_ems_meta_box()
        {
            //$cmg_archive_option = get_option( 'cmg_archive_option', '' );
            ?>
            Coming Soon
            <?php
        }

        /**
         * Single Magnet Meta Box Callback
         * @since 0.1.0
         */
        public static function cmg_single_magnet_meta_box($post)
        {
            self::cmg_css_and_js();
            $cmg_single_options = get_post_meta($post->ID, 'cmg_single_options', true); //cmg_print($cmg_single_options);
            // Add a nonce field so we can check for it later.
            wp_nonce_field('cmg_single_options_nonce', 'cmg_single_options_nonce');
            ?>
            <input type="hidden" name="cmg_in_single_magnet" class="cmg_in_single_magnet" value="<?php echo $post->ID; ?>">
            <style type="text/css">
                /*.editor-post-title {
                    visibility: hidden;
                    height: 70px;
                }*/
                .block_cmg_magnet_choose {
                    visibility: hidden;
                    height: 10px;
                    padding: 0 !important;
                    margin: 0;
                }
                /*.components-toolbar .block-editor-block-settings-menu,
                .block-editor-block-toolbar__slot .components-icon-button,*/
                .block-editor-default-block-appender,
                .block-editor-block-contextual-toolbar-wrapper,
                .edit-post-header-toolbar__left,
                .edit-post-layout__metaboxes {
                    display: none;
                }
            </style>
            <div class="cmg_kill_when_guten_active">
                <p>
                    <label for="cmg_title"><?php _e('Title', CMG_NAME); ?></label>
                    <input id="cmg_title" class="widefat" type="text" name="cmg_single_options[title]"
                           value="<?php echo sanitize_text_field(isset_return($cmg_single_options, 'title')); ?>">
                </p>
                <p>
                    <label for="cmg_single_subtitle"><?php _e('Magnet text (subtitle)', CMG_NAME); ?></label>
                    <textarea id="cmg_single_subtitle" class="widefat" name="cmg_single_options[subtitle]"><?php echo isset_return($cmg_single_options, 'subtitle'); ?></textarea>
                </p>
                <p>
                    <label for="cmg_file_url"><?php _e('File Url', CMG_NAME); ?></label>
                    <input id="cmg_file_url" class="widefat" type="text" name="cmg_single_options[resourceURL]"
                           value="<?php echo sanitize_text_field(isset_return($cmg_single_options, 'resourceURL')); ?>">
                </p>
                <p>
                    <input id="cmg_excludeFromArchive" class="widefat" type="checkbox"
                           name="cmg_single_options[excludeFromArchive]"
                           value="1" <?php if (isset_return($cmg_single_options, 'excludeFromArchive')) echo 'checked="checked"'; ?>>
                    <label for="cmg_excludeFromArchive"><?php _e('Exclude from Archive', CMG_NAME); ?></label>
                </p>
                <p>
                    <input id="cmg_redirectUrlEnable" class="widefat" type="checkbox"
                           name="cmg_single_options[redirectUrlEnable]"
                           value="1" <?php if (isset_return($cmg_single_options, 'redirectUrlEnable')) echo 'checked="checked"'; ?>>
                    <label for="cmg_redirectUrlEnable"><?php _e('Redirect URL', CMG_NAME); ?></label>
                </p>
                <p>
                    <label for="cmg_redirectURL"><?php _e('Redirect URL', CMG_NAME); ?></label>
                    <input id="cmg_redirectURL" class="widefat" type="text" name="cmg_single_options[redirectURL]"
                           value="<?php echo sanitize_text_field(isset_return($cmg_single_options, 'redirectURL')); ?>">
                </p>
                <p>
                    <input id="cmg_successMessageEnable" class="widefat" type="checkbox"
                           name="cmg_single_options[successMessageEnable]"
                           value="1" <?php if (isset_return($cmg_single_options, 'successMessageEnable')) echo 'checked="checked"'; ?>>
                    <label for="cmg_successMessageEnable"><?php _e('Success message', CMG_NAME); ?></label>
                </p>
                <p>
                    <label for="cmg_successMessage"><?php _e('Success message', CMG_NAME); ?></label>
                    <input id="cmg_successMessage" class="widefat" type="text" name="cmg_single_options[successMessage]"
                           value="<?php echo sanitize_text_field(isset_return($cmg_single_options, 'successMessage')); ?>">
                </p>
                <p>
                    <label for="cmg_buttonTxt"><?php _e('Button Text', CMG_NAME); ?></label>
                    <input id="cmg_buttonTxt" class="widefat" type="text" name="cmg_single_options[buttonTxt]"
                           value="<?php echo sanitize_text_field(isset_return($cmg_single_options, 'buttonTxt')); ?>">
                </p>
            </div>
            <p class="howto"></p>
            <?php
        }


        public static function cmg_single_capture_meta_box($post)
        {
            $magnet_email = get_post_meta($post->ID, 'magnet_email', true);
            $magnet_post_id = get_post_meta($post->ID, 'magnet_post_id', true);
            $magnet_user_ip = get_post_meta($post->ID, 'magnet_user_ip', true);
            $magnet_date_time = get_post_meta($post->ID, 'magnet_date_time', true);
            $magnet_post = get_posts(array('include' => $magnet_post_id, 'post_type' => 'cmg_magnet'));
            $magnet_post_txt = $magnet_post_id;
            if (!empty($magnet_post)) {
                $magnet_post = $magnet_post[0];
                $magnet_post_txt = '<a href="' . get_edit_post_link($magnet_post->ID) . '" target="_blank">' . $magnet_post->post_title . '</a>';
            }
            ?>

            <div>
                <p><b><?php _e('Email', CMG_NAME); ?>:</b> <span> <?php echo sanitize_email($magnet_email); ?></span>
                </p>
                <p><b><?php _e('Magnet Post', CMG_NAME); ?>:</b> <span> <?php echo $magnet_post_txt; ?></span></p>
                <p><b><?php _e('User IP', CMG_NAME); ?>:</b>
                    <span> <?php echo sanitize_text_field($magnet_user_ip); ?></span></p>
                <p><b><?php _e('DateTime', CMG_NAME); ?>:</b>
                    <span> <?php echo sanitize_text_field($magnet_date_time); ?></span></p>
            </div>
            <p class="howto"></p>
            <?php
        }


        public static function save_cmg_magnet_meta_box_data($post_id)
        {

            // Check if our nonce is set.
            if (!isset( $_POST['cmg_single_options_nonce'])) {
                return;
            }

            // Verify that the nonce is valid.
            if (!wp_verify_nonce($_POST['cmg_single_options_nonce'], 'cmg_single_options_nonce')) {
                return;
            }

            // If this is an autosave, our form has not been submitted, so we don't want to do anything.
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            // Check the user's permissions.
            // no need for now

            // OK, it's safe for us to save the data now.

            // Make sure that it is set.
            if (!isset($_POST['cmg_single_options'])) {
                return;
            }

            //cmg_print($_POST['cmg_single_options']); exit;
            // Sanitize user input.
            //$cmg_single_options = array_map( array('CMG', 'cmg_sanitize_data'),  $_POST['cmg_single_options'] );
            $cmg_single_options = array_filter($_POST['cmg_single_options'], array('CMG', 'cmg_sanitize_data'), ARRAY_FILTER_USE_BOTH );

            // Update the meta field in the database.
            update_post_meta($post_id, 'cmg_single_options', $cmg_single_options);
        }

        public static function set_custom_edit_cmg_capture_columns($columns)
        {

            $columns['full_name'] = __('Full name', CMG_NAME);
            //$columns['publisher'] = __( 'Publisher', CMG_NAME );

            return $columns;
        }

        public static function custom_cmg_capture_column($column, $post_id)
        {
            switch ($column) {

                case 'full_name' :
                    $terms = get_the_term_list($post_id, 'book_author', '', ',', '');
                    if (is_string($terms))
                        echo $terms;
                    else
                        _e('Unable to get author(s)', CMG_NAME);
                    break;

                case 'publisher' :
                    echo get_post_meta($post_id, 'publisher', true);
                    break;

            }
        }


        //ETC
        static function array2csv( array &$array, $file_path ) {
            if (count($array) == 0) {
                return null;
            }
            ob_start();
            $df = fopen($file_path, 'w');
            //fputcsv($df, array_keys(reset($array)));
            foreach ($array as $row) {
                fputcsv($df, $row);
            }
            fclose($df);
            return ob_get_clean();
        }

        /*
         * AJAX
         */

        public static function cmg_create_archive_page()
        {

            if (!(isset($_REQUEST['action']) && 'cmg_create_archive_page' == sanitize_text_field( $_POST['action'] )))
                return;

            $return = array(
                'status' => '0',
                'html' => ''
            );
            $post_type = 'page';
            $validation = true;

            $post_title = sanitize_text_field( $_POST['cmg_page_title'] );
            $post_slug = sanitize_title_with_dashes( $_POST['cmg_page_slug'] );
            $post_content = sanitize_textarea_field( $_POST['cmg_page_description'] );

            //Validation
            if (!$post_title) {
                $validation = false;
                $return['html'] = __('Page title is empty.', CMG_NAME);
            }

            if ($validation) {
                $args = array(
                    'post_type' => $post_type,
                    'post_title' => $post_title,
                    'post_name' => $post_slug,
                    'post_status' => 'publish',
                    'post_content' => $post_content,
                    'comment_status' => 'closed'
                );
                $post_id = wp_insert_post($args);

                if ($post_id) {

                    $cmg_archive_option = get_option('cmg_archive_option', true);
                    $cmg_archive_option['cmg_archive_page'] = $post_id;
                    $cmg_archive_option['page_title'] = $post_title;
                    $cmg_archive_option['page_slug'] = $post_slug;
                    $cmg_archive_option['page_description'] = $post_content;
                    update_option('cmg_archive_option', $cmg_archive_option);

                    $return = array(
                        'status' => '1',
                        'html' => __('Content Magnet Archive page created successfully!', CMG_NAME),
                        'page_id' => $post_id
                    );
                } else {
                    $return = array(
                        'status' => '0',
                        'html' => __('Can\'t create the page for Content Magnet Archive!', CMG_NAME)
                    );
                }

            } else {
                $return['status'] = '0';
            }

            echo json_encode($return);
            exit;
        }

        public static function cmg_magnet_post_selected()
        {

            if (!(isset($_REQUEST['action']) && 'cmg_magnet_post_selected' == sanitize_text_field( $_POST['action'] )))
                return;

            $return = array(
                'status' => '0',
                'html' => ''
            );
            $validation = true;
            $post_id = sanitize_text_field( $_POST['cmg_post_id'] );

            //Validation
            if (!is_user_logged_in()) {
                $validation = false;
                $return['html'] = __('User is not logged in! Are you a hacker?', CMG_NAME);
            }

            if ($validation) {
                if ( !empty($post_id) ) {
                    $cmg_single_options = get_post_meta($post_id, 'cmg_single_options', true);
                    $magnet_post = get_posts(array('include' => $post_id, 'post_type' => 'cmg_magnet'));
                    $magnet_info = $cmg_single_options;
                    $return = array(
                        'status' => '1',
                        'html' => 'Magnet found!',
                        'magnet_info' => $magnet_info
                    );
                } else {
                    $return = array(
                        'status' => '0',
                        'html' => __('There is no any Subscribers!', CMG_NAME)
                    );
                }

            } else {
                $return['status'] = '0';
            }

            echo json_encode($return);
            exit;
        }

        public static function cmg_export_magnets()
        {

            if (!(isset($_REQUEST['action']) && 'cmg_export_magnets' == sanitize_text_field( $_POST['action'] )))
                return;

            $return = array(
                'status' => '0',
                'html' => ''
            );
            $post_type = 'cmg_capture';
            $validation = true;

            $export_from_magnets = $_POST['cmg_export_from_magnets'];

            //Validation
            if (!is_user_logged_in()) {
                $validation = false;
                $return['html'] = __('User is not logged in! Are you a hacker?', CMG_NAME);
            }

            if ($validation) {
                $args = array(
                    'post_type' => $post_type,
                    'posts_per_page' => '-1',
                    'fields' => 'ids'
                );
                /*if( $export_from_magnets && is_numeric($export_from_magnets) ){
                    $args['meta_query'] = array(
                        array(
                            'key' => 'magnet_post_id',
                            'value'   => $export_from_magnets,
                            'compare' => '=',
                        )
                    );
                }*/ //Pro only

                $post_ids = new WP_Query($args);
                $magnet_subscribers = array(
                    array(
                        'Email Address',
                        'User IP',
                        'Date/Time',
                        'Magnet Post Info (ID | Title | link)'
                    )
                );
                if ( !empty($post_ids->posts) ) {
                    $post_ids = $post_ids->posts;
                    $magnet_subscriber = array();

                    foreach ( $post_ids as $post_id ){
                        $magnet_email = get_post_meta($post_id, 'magnet_email', true);
                        $magnet_post_id = get_post_meta($post_id, 'magnet_post_id', true);
                        $magnet_user_ip = get_post_meta($post_id, 'magnet_user_ip', true);
                        $magnet_date_time = get_post_meta($post_id, 'magnet_date_time', true);
                        $magnet_post = get_posts(array('include' => $magnet_post_id, 'post_type' => 'cmg_magnet'));
                        $magnet_post_txt = $magnet_post_id;
                        if (!empty($magnet_post)) {
                            $magnet_post = $magnet_post[0];
                            $magnet_post_txt = $magnet_post_id.' | '.$magnet_post->post_title.' | '.get_edit_post_link($magnet_post->ID);
                        }
                        $magnet_subscriber = array(
                            $magnet_email,
                            $magnet_user_ip,
                            $magnet_date_time,
                            $magnet_post_txt
                        );

                        array_push( $magnet_subscribers, $magnet_subscriber );
                    }
                    //cmg_print($magnet_subscribers);

                    $upload_dir = wp_upload_dir(); //basedir //baseurl
                    $file_name = 'Content-Magnet-'.'-Export-'.date("Y-m-d") . '-'.time() . ".csv";
                    $folder_name = '/cmg_export_files';
                    $file_dirname = $upload_dir['basedir'].$folder_name;
                    if( ! file_exists( $file_dirname ) )
                        wp_mkdir_p( $file_dirname );
                    $file_path = $file_dirname.'/'.$file_name;
                    //echo $upload_dir['baseurl'].$folder_name.'/'.$file_name;
                    $file_url = urlencode($upload_dir['baseurl'].$folder_name.'/'.$file_name );

                    self::array2csv( $magnet_subscribers, $file_path );
                    $return = array(
                        'status' => true,
                        'html' => 'Subscribers exported successfully!',
                        'url' => $file_url
                    );
                } else {
                    $return = array(
                        'status' => '0',
                        'html' => __('There is no any Subscribers!', CMG_NAME)
                    );
                }

            } else {
                $return['status'] = '0';
            }

            echo json_encode($return);
            exit;
        }

    }
}




