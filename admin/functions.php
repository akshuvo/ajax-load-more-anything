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

    ppr( $args['selector'] );

    // Array key
    $key =  isset( $args['key'] ) ? $args['key'] : "";

    $wrapper_key = $args['key']+1;

    $load_more_button_wrapper = __( 'Load More Button Selector', 'aldtd' );
    $load_more_button_wrapper_desc = __( 'Load more button will be insert end of this selector', 'aldtd' );

    $load_more_item_selector = __( 'Load More Items Selector', 'aldtd' );
    $load_more_item_selector_desc = __( 'Selector for load more items. Example: <code>.parent_selector .items</code>', 'aldtd' );

    $visiable_items_text = __( 'Visiable Items', 'aldtd' );
    $visiable_items_desc = __( 'How many item will show initially', 'aldtd' );

    $load_items_text = __( 'Load Items', 'aldtd' );
    $load_items_desc = __( 'How Many Item Will Load When Click Load More Button?', 'aldtd' );

    $button_label_text = __( 'Load More Button Label', 'aldtd' );
    $button_label_desc = __( 'Enter the name of Load More Button <br> Use <code>+[count]</code> for countable button like +15 more', 'aldtd' );

    ob_start();
    ?>
    <!-- Wrapper One Start -->
    <div id="postimagediv" class="postbox tf_gen_sel_field">
        <a class="header ald-toggle-head" data-toggle="collapse">
            <span id="poststuff">
                <h2 class="hndle"><?php echo sprintf( esc_html__( 'Wrapper - %s', 'aldtd' ), $wrapper_key ); ?></h2>
            </span>
        </a>
        <div class="collapse ald-toggle-wrap">
            <div class="inside">

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-btn_selector-<?php _e( $key ); ?>"><?php _e( $load_more_button_wrapper ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-btn_selector-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php _e( $key ); ?>][btn_selector]" value="<?php echo esc_attr( $btn_selector ); ?>" />
                            <p><?php _e( $load_more_button_wrapper_desc ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-load_selector-<?php _e( $key ); ?>"><?php _e( $load_more_item_selector ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-load_selector-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php _e( $key ); ?>][load_selector]" value="<?php echo esc_attr( $load_selector ); ?>" />
                            <p><?php _e( $load_more_item_selector_desc ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-visible_items-<?php _e( $key ); ?>"><?php _e( $visiable_items_text ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-visible_items-<?php _e( $key ); ?>" class="regular-text" type="number" name="ald_options[general_loadmore][<?php _e( $key ); ?>][visible_items]" value="<?php echo esc_attr( $visible_items ); ?>" />
                            <p><?php _e( $visiable_items_desc ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-load_items-<?php _e( $key ); ?>"><?php _e( $load_items_text ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-load_items-<?php _e( $key ); ?>" class="regular-text" type="number" name="ald_options[general_loadmore][<?php _e( $key ); ?>][load_items]" value="<?php echo esc_attr( $load_items ); ?>" />
                            <p><?php _e( $load_items_desc ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-button_label-<?php _e( $key ); ?>"><?php _e( $button_label ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-button_label-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php _e( $key ); ?>][button_label]" value="<?php echo esc_attr( $button_label ); ?>" />
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