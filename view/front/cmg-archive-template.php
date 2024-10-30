<?php
/**
 * The template for displaying Content Magnet Archive page
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
global $post;
get_header();
?>
<div class="cmg_archive_wrapper">
    <main id="cmg_archive" role="main" class="container">
        <?php
        if (have_posts()) {

            while (have_posts()) {
                the_post();
                ?>
                <h1> <?php the_title(); ?> </h1>
                <div class="cmg_archive_description"> <?php the_content(); ?> </div>

                <div class="cmg_archive_action_div ">
                    <?php
                    if ( !isset($_COOKIE['cmg_magnet_email']) || empty($_COOKIE['cmg_magnet_email'])) {
                        ?>
                        <div class="i_cmg_item_inner">
                            <h3> Enter your email address to access all the resources on this page. </h3>
                            <form class="cmg_form_signup"
                                  data-magnet_id="archive">
                                <input type="email" name="cmg_email" value=""
                                       placeholder="<?php _e('Email address', CMG_NAME); ?>"
                                       class="cmg_email">
                                <button class="cmg_submit_btn cmg_btn primary" style="" type="submit" name="cmg_submit">
                                    <span><?php _e('Unlock', CMG_NAME); ?></span>
                                </button>
                                <?php /*<input type="submit" name="cmg_submit" value="<?php _e('Unlock', CMG_NAME); ?>" class="cmg_btn primary"> */ ?>
                            </form>
                            <div class="cmg_response">
                                <i class="fa fa-check-circle" aria-hidden="true"></i>
                                <div class="cmg_response_txt"></div>
                                <span class="cmg_close_response"> <i class="fa fa-times"
                                                                     aria-hidden="true"></i> </span>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="cmg_items cmg_clearfix">
                    <?php
                    $magnets = get_posts(array(
                        'post_type' => 'cmg_magnet',
                        'showposts' => -1
                    ));
                    if (!empty($magnets)) {
                        foreach ($magnets as $magnet) { //cmg_print($magnet);
                            $magnet_id = $magnet->ID;

                            $cmg_single_options = get_post_meta($magnet_id, 'cmg_single_options', true); //cmg_print($cmg_single_options);
                            $magnet_file_url = (isset($cmg_single_options['resourceURL'])) ? $cmg_single_options['resourceURL'] : '';
                            $magnet_description = (isset($cmg_single_options['subtitle'])) ? $cmg_single_options['subtitle'] : '';
                            /*if (empty($magnet_file_url)) {
                                $magnet_file_url = '#';
                                continue;
                            }*/
                            $featured_img_url = get_the_post_thumbnail_url($magnet_id, 'medium');//cmg_print($featured_img_url);
                            ?>
                            <div class="cmg_item">
                                <div class="cmg_item_inner i_cmg_item_inner">
                                    <div class="cmg_item_inner_inner cmg_row">
                                        <div class="cmg_item_head cmg-col-3" style="background-image: url(<?php echo esc_url( $featured_img_url ); ?>) ">
                                            <?php /*<img src="<?php echo esc_url( $featured_img_url ); ?>" class="cmg_item_img"> */?>
                                        </div>
                                        <div class="cmg_item_body cmg-col-9">
                                            <div class="cmg_title"><h4><?php _e( $magnet->post_title, CMG_NAME ); ?></h4></div>
                                            <div class="cmg_description"> <?php _e( $magnet_description, CMG_NAME); ?> </div>
                                            <div class="cmg_item_actions_div">
                                                <?php
                                                if ( !empty($magnet_file_url) && isset($_COOKIE['cmg_magnet_email']) && !empty($_COOKIE['cmg_magnet_email'])) {
                                                    echo '<a href="' . esc_url( $magnet_file_url ) . '" target="_blank" class="cmg_btn primary cmg_download_btn">' . __('Download', CMG_NAME) . '</a>';
                                                } else {
                                                    /*
                                                    ?>
                                                    <form class="cmg_form_signup"
                                                          data-magnet_id="<?php echo $magnet_id; ?>">
                                                        <input type="email" name="cmg_email" value=""
                                                               placeholder="<?php _e('Email address', CMG_NAME); ?>"
                                                               class="cmg_email">
                                                        <input type="submit" name="cmg_submit"
                                                               value="<?php _e('Unlock', CMG_NAME); ?>"
                                                               class="cmg_btn primary">
                                                    </form>
                                                    <?php
                                                    */
                                                }
                                                ?>
                                            </div>

                                        </div>
                                        <?php /*
                                        <div class="cmg_response">
                                            <i class="fa fa-check-circle" aria-hidden="true"></i>
                                            <div class="cmg_response_txt"></div>
                                            <span class="cmg_close_response"> <i class="fa fa-times"
                                                                                 aria-hidden="true"></i> </span>
                                        </div> */?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }

                    ?>
                </div>
                <?php
            }
        }
        ?>


    </main><!-- #site-content -->
</div>


<?php get_footer(); ?>
