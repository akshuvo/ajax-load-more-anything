<?php
/**
 * Old plugin Options
 * @return array
 */
function ald_old_options(){
    $old_options = array();

    // Get Old Options
    if ( get_option('ald_wrapper_class') ) {
        $old_options['ald_wrapper_class'] = array(
            'btn_selector' => get_option('ald_wrapper_class'),
            'load_selector' => get_option('ald_load_class'),
            'visible_items' => get_option('ald_item_show'),
            'load_items' => get_option('ald_item_load'),
            'button_label' => get_option('ald_load_label'),
            'display_type' => 'normal',
        );
    }
    if ( get_option('ald_wrapper_classa') ) {
        $old_options['ald_wrapper_classa'] = array(
            'btn_selector' => get_option('ald_wrapper_classa'),
            'load_selector' => get_option('ald_load_classa'),
            'visible_items' => get_option('ald_item_showa'),
            'load_items' => get_option('ald_item_loada'),
            'button_label' => get_option('ald_load_labela'),
            'display_type' => 'normal',
        );
    }
    if ( get_option('ald_wrapper_classb') ) {
        $old_options['ald_wrapper_classb'] = array(
            'btn_selector' => get_option('ald_wrapper_classb'),
            'load_selector' => get_option('ald_load_classb'),
            'visible_items' => get_option('ald_item_showb'),
            'load_items' => get_option('ald_item_loadb'),
            'button_label' => get_option('ald_load_labelb'),
            'display_type' => 'normal',
        );
    }
    if ( get_option('ald_wrapper_classc') ) {
        $old_options['ald_wrapper_classc'] = array(
            'btn_selector' => get_option('ald_wrapper_classc'),
            'load_selector' => get_option('ald_load_classc'),
            'visible_items' => get_option('ald_item_showc'),
            'load_items' => get_option('ald_item_loadc'),
            'button_label' => get_option('ald_load_labelc'),
            'display_type' => 'normal',
        );
    }
    if ( get_option('ald_wrapper_classd') ) {
        $old_options['ald_wrapper_classd'] = array(
            'btn_selector' => get_option('ald_wrapper_classd'),
            'load_selector' => get_option('ald_load_classd'),
            'visible_items' => get_option('ald_item_showd'),
            'load_items' => get_option('ald_item_loadd'),
            'button_label' => get_option('ald_load_labeld'),
            'display_type' => 'normal',
        );
    }
    if ( get_option('ald_wrapper_classe') ) {
        $old_options['ald_wrapper_classe'] = array(
            'btn_selector' => get_option('ald_wrapper_classe'),
            'load_selector' => get_option('ald_load_classe'),
            'visible_items' => get_option('ald_item_showe'),
            'load_items' => get_option('ald_item_loade'),
            'button_label' => get_option('ald_load_labele'),
            'display_type' => 'flex',
        );
    }

    // Get custom css from old options
    if ( get_option('asr_ald_css_class') ) {
        $old_options['custom_css'] = get_option('asr_ald_css_class');
    }

    return $old_options;
}

// All old plugin options keys
function ald_old_options_keys(){
    $keys = array(

        'ald_wrapper_class',
        'ald_load_class',
        'ald_item_show',
        'ald_item_load',
        'ald_load_label',

        'ald_wrapper_classa',
        'ald_load_classa',
        'ald_item_showa',
        'ald_item_loada',
        'ald_load_labela',

        'ald_wrapper_classb',
        'ald_load_classb',
        'ald_item_showb',
        'ald_item_loadb',
        'ald_load_labelb',

        'ald_wrapper_classc',
        'ald_load_classc',
        'ald_item_showc',
        'ald_item_loadc',
        'ald_load_labelc',

        'ald_wrapper_classd',
        'ald_load_classd',
        'ald_item_showd',
        'ald_item_loadd',
        'ald_load_labeld',

        'ald_wrapper_classe',
        'ald_load_classe',
        'ald_item_showe',
        'ald_item_loade',
        'ald_load_labele',

        'asr_ald_css_class',
    );

    return $keys;
}

// Save & delete old plugin data
function update_option_ald_options_hook( $old_value, $value, $option ){

    // Save old plugin options in one array
    if ( ald_old_options() && get_option( 'ald_old_options' ) === false ) {
        update_option( 'ald_old_options', ald_old_options() );
    }

    // Delete old plugin options
    if ( ald_old_options() ) {
        foreach ( ald_old_options_keys() as $key => $value ) {
            if( get_option( $value ) ){
                delete_option( $value );
            }
        }
    }
    // All done, Have fun!

}
add_action( "update_option_ald_options", "update_option_ald_options_hook", 10, 3 );

// Save option ajax
add_action( 'wp_ajax_ald_save_settings', 'ald_save_option_ajax_function' );
function ald_save_option_ajax_function(){
    $options = isset( $_POST['ald_options'] ) ? $_POST['ald_options'] : array();

    if ( !is_admin() ) {
        return;
    }

    //Update entire array
    update_option('ald_options', $options);

    $my_multi_options = get_option('ald_options');

    print_r( $my_multi_options );

    die();
}

// Add general wrapper action
add_action( 'wp_ajax_ald_add_general_loadmore', 'ald_add_general_loadmore_action' );
function ald_add_general_loadmore_action(){

    $key = sanitize_text_field( $_POST['key'] );

    ob_start();

    echo ald_add_general_loadmore_wrap( array(
        'key' => $key,
    ) );

    $output = ob_get_clean();

    echo $output;

    die();
}

// Single general loadmore data
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

    $wrapper_key = '<span class="gen_wrap_sl">'.($args['key']+1).'</span>';

    if ( !isset( $args['selector']['wrapper_title'] ) ) {
        $wrapper_title = __('Wrapper Title', 'aldtd');
    }

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
    <div id="postimagediv" class="postbox tf_gen_sel_field"> <!-- Wrapper Start -->
        <a class="header ald-toggle-head" data-toggle="collapse">
            <span id="poststuff">
                <h2 class="hndle">
                    <input type="text" class="ald_ajax_wrap_title" name="ald_options[general_loadmore][<?php _e( $key ); ?>][wrapper_title]" value="<?php esc_attr_e( $wrapper_title ); ?>" title="<?php esc_attr_e( 'Change title to anything you like. Ex: For Homepage', 'aldtd' ); ?>">
                    <span class="delete_field">&times;</span>
                </h2>
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
                                <label for="general_loadmore-button_label-<?php _e( $key ); ?>"><?php _e( $button_label_text ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-button_label-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php _e( $key ); ?>][button_label]" value="<?php echo esc_attr( $button_label ); ?>" />
                            <p><?php _e( $button_label_desc ) ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-display_type-<?php _e( $key ); ?>"><?php _e( 'Select display type', 'aldtd' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <select id="general_loadmore-display_type-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php _e( $key ); ?>][display_type]">
                                <option value="normal" <?php selected( $display_type, 'normal' ); ?>><?php _e( 'Normal', 'aldtd' ); ?></option>
                                <option value="flex" <?php selected( $display_type, 'flex' ); ?>><?php _e( 'Flex', 'aldtd' ); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <!-- Wrapper end -->
    </div>

    <?php
    $output = ob_get_clean();

    return $output;
}

// Add ajax wrapper action
add_action( 'wp_ajax_ald_add_ajax_loadmore', 'ald_add_ajax_loadmore_action' );
function ald_add_ajax_loadmore_action(){

    $key = sanitize_text_field( $_POST['key'] );

    ob_start();

    echo ald_add_ajax_loadmore_wrap( array(
        'key' => $key,
    ) );

    $output = ob_get_clean();

    echo $output;

    die();
}

// Single ajax loadmore data
function ald_add_ajax_loadmore_wrap( $args ){
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

    $wrapper_key = '<span class="ajax_wrap_sl">'.($args['key']+1).'</span>';

    if ( !isset( $args['selector']['wrapper_title'] ) ) {
        $wrapper_title = __('Wrapper Title', 'aldtd');
    }

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

    <div id="postimagediv" class="postbox tf_ajax_sel_field"> <!-- Wrapper Start -->
        <a class="header ald-toggle-head" data-toggle="collapse">
            <span id="poststuff">
                <h2 class="hndle">
                    <input type="text" class="ald_ajax_wrap_title" name="ald_options[ajax_loadmore][<?php _e( $key ); ?>][wrapper_title]" value="<?php esc_attr_e( $wrapper_title ); ?>" title="<?php esc_attr_e( 'Change title to anything you like. Ex: Homepage Posts', 'aldtd' ); ?>">
                    <span class="delete_field">&times;</span>
                </h2>
            </span>
        </a>
        <div class="collapse ald-toggle-wrap">
            <div class="inside">

                <table class="form-table">

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-event_type-<?php _e( $key ); ?>"><?php _e( 'Event Type:', 'aldtd' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <select id="ajax_loadmore-event_type-<?php _e( $key ); ?>" class="regular-text ajax_loadmore-event_type" type="text" name="ald_options[ajax_loadmore][<?php _e( $key ); ?>][event_type]">
                                <option value="selectors_click" <?php selected( $event_type, 'selectors_click' ); ?>><?php _e( 'Selector(s) Click', 'aldtd' ); ?></option>
                                <option value="scroll_to_load" <?php selected( $event_type, 'scroll_to_load' ); ?>><?php _e( 'Scroll to Load (Infinite Scroll)', 'aldtd' ); ?></option>
                                <option value="custom_button" <?php selected( $event_type, 'custom_button' ); ?>><?php _e( 'Add Custom Button', 'aldtd' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top" data-id="custom_button_append">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-custom_button_append-<?php _e( $key ); ?>"><?php esc_html_e( 'Button Insert Selector', 'default' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-custom_button_append-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php _e( $key ); ?>][custom_button_append]" value="<?php echo esc_attr( $custom_button_append ); ?>" />
                            <p><?php esc_html_e( 'Button will be insert after this selector.', 'default' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top" data-id="button_trigger_selector">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-button_trigger_selector-<?php _e( $key ); ?>"><?php esc_html_e( 'Button click trigger Selector', 'default' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-button_trigger_selector-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php _e( $key ); ?>][button_trigger_selector]" value="<?php echo esc_attr( $button_trigger_selector ); ?>" />
                            <p><?php esc_html_e( 'This selector will be trigger when the button clicked.', 'default' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top" data-id="click_selector">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-click_selector-<?php _e( $key ); ?>"><?php esc_html_e( 'Enter Selector', 'default' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-click_selector-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php _e( $key ); ?>][click_selector]" value="<?php echo esc_attr( $click_selector ); ?>" />
                            <p><?php esc_html_e( 'Selector should be correct, otherwise ajax will fail to load contents', 'default' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top" data-id="hide_selector_wrapper">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-hide_selector_wrapper-<?php _e( $key ); ?>"><?php _e( 'Hide Selector(s) wrapper', 'aldtd' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <select id="ajax_loadmore-hide_selector_wrapper-<?php _e( $key ); ?>" class="regular-text ajax_loadmore-hide_selector_wrapper" type="text" name="ald_options[ajax_loadmore][<?php _e( $key ); ?>][hide_selector_wrapper]">
                                <option value="no" <?php selected( $hide_selector_wrapper, 'no' ); ?>><?php _e( 'No', 'aldtd' ); ?></option>
                                <option value="yes" <?php selected( $hide_selector_wrapper, 'yes' ); ?>><?php _e( 'Yes', 'aldtd' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top" data-id="wrapper_to_hide">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-wrapper_to_hide-<?php _e( $key ); ?>"><?php esc_html_e( 'Wrapper Selector to hide', 'default' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-wrapper_to_hide-<?php _e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php _e( $key ); ?>][wrapper_to_hide]" value="<?php echo esc_attr( $wrapper_to_hide ); ?>" />
                            <p><?php esc_html_e( 'Enter the selector of the wrapper which you want to hide from visitors', 'default' ); ?></p>
                        </td>
                    </tr>



                </table>
            </div>
        </div>
    <!-- Wrapper end below -->
    </div>


    <?php
    $output = ob_get_clean();

    return $output;
}