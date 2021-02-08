<?php

add_action( 'wp_ajax_tf_add_new_room', 'tf_add_room_data_action' );
function tf_add_room_data_action(){

    $key = sanitize_text_field( $_POST['key'] );

    ob_start();

    echo ald_add_general_loadmore_wrap( array(
        'key' => $key,
    ) );

    $output = ob_get_clean();

    echo $output;

    die();
}

// Single room data
function ald_add_general_loadmore_wrap( $args ){
    $defaults = array (
        'key' => '',
        'selector' => '',
    );

    // Parse incoming $args into an array and merge it with $defaults
    $args = wp_parse_args( $args, $defaults );

    // Let's extract the array
    extract( $args['selector'] );

    // Array key
    $key =  isset( $args['key'] ) ? $args['key'] : "";


    ob_start();
    ?>
    <!-- Wrapper One Start -->
    <div id="postimagediv" class="postbox tf_gen_sel_field">
        <a class="header ald-toggle-head" data-toggle="collapse">
            <span id="poststuff">
                <h2 class="hndle"><?php esc_html_e( 'Wrapper - 1', 'aldtd' ); ?></h2>
            </span>
        </a>
        <div class="collapse ald-toggle-wrap">
            <div class="inside">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( $load_more_button_wrapper ); ?></th>
                        <td>
                            <input class="regular-text" type="text" name="ald_wrapper_class" value="<?php echo esc_attr( get_option('ald_wrapper_class') ); ?>" />
                            <p><?php _e( $load_more_button_wrapper_desc ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( $load_more_item_selector ); ?></th>
                        <td>
                            <input class="regular-text" type="text" name="ald_load_class" value="<?php echo esc_attr( get_option('ald_load_class') ); ?>" />
                            <p><?php _e( $load_more_item_selector_desc ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( $visiable_items ); ?></th>
                        <td>
                            <input class="regular-text" type="number" name="ald_item_show" value="<?php echo esc_attr( get_option('ald_item_show') ); ?>" />
                            <p><?php _e( $visiable_items_desc ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( $load_items ); ?></th>
                        <td>
                            <input class="regular-text" type="number" name="ald_item_load" value="<?php echo esc_attr( get_option('ald_item_load') ); ?>" />
                            <p><?php _e( $load_items_desc ); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( $button_label ); ?></th>
                        <td>
                            <input class="regular-text" type="text" name="ald_load_label" value="<?php echo esc_attr( get_option('ald_load_label') ); ?>" />
                            <p><?php _e( $button_label_desc ) ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <!-- Wrapper One end -->

    <?php
    $output = ob_get_clean();

    return $output;

}