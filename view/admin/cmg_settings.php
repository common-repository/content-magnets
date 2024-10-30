<?php

global $hook_suffix;
//cmg_print($hook_suffix);

/* utility hook */
do_action( 'cmg_settings_page_init' );

/* enable add_meta_boxes function in this page. */
do_action( 'add_meta_boxes', $hook_suffix, null );
?>

<div class="wrap">

    <h2>Content Magnets Settings </h2>

    <div class="cmg_settings_response cmg_response">
        <i class="fa fa-check-circle" aria-hidden="true"></i>
        <div class="cmg_response_txt"> <?php settings_errors(); ?> </div>
        <span class="cmg_close_response"> <i class="fa fa-times" aria-hidden="true"></i> </span>
    </div>

    <div class="fx-settings-meta-box-wrap">

        <form id="fx-smb-form" method="post" action="options.php">

            <?php settings_fields( 'cmg' ); // options group  ?>
            <?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
            <?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

                    <div id="postbox-container-1" class="postbox-container">

                        <?php do_meta_boxes( $hook_suffix, 'side', null ); ?>
                        <!-- #side-sortables -->

                    </div><!-- #postbox-container-1 -->

                    <div id="postbox-container-2" class="postbox-container">

                        <?php do_meta_boxes( $hook_suffix, 'normal', null ); ?>
                        <!-- #normal-sortables -->

                        <?php do_meta_boxes( $hook_suffix, 'advanced', null ); ?>
                        <!-- #advanced-sortables -->

                    </div><!-- #postbox-container-2 -->

                </div><!-- #post-body -->

                <br class="clear">

            </div><!-- #poststuff -->

        </form>

    </div><!-- .fx-settings-meta-box-wrap -->

</div><!-- .wrap -->
