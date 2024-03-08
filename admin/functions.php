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

    // Check nonce
    if ( !isset( $_POST['ald_nonce'] ) || !wp_verify_nonce( $_POST['ald_nonce'], 'alma_settings_nonce' ) ) {
        wp_die('Permission Denied');
    }

    // Check admin
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die('Permission Denied');
    }

    // Get options
    $options = isset( $_POST['ald_options'] ) ? $_POST['ald_options'] : array();
    $options = apply_filters( 'ald_before_options_save', $options );

    // Update entire array
    update_option('ald_options', $options);

    // Send success message
    wp_send_json_success();

    die();
}

// Add general wrapper action
add_action( 'wp_ajax_ald_add_general_loadmore', 'ald_add_general_loadmore_action' );
function ald_add_general_loadmore_action(){

    //$key = sanitize_text_field( $_POST['key'] );
    $key = isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : '';
    $thiskey = isset( $_POST['thiskey'] ) ? intval( $_POST['thiskey'] ) : 0;

    $output = ald_add_general_loadmore_wrap( array(
        'key' => $key,
        'thiskey' => $thiskey,
    ) );

    echo $output; // phpcs:ignore 
    die();
}

// List of Display Types
function ald_display_types(){
    $types = [
        'default' => [
            'label' => 'Default',
            'value' => 'default',
            'pro' => false,
        ],
        'flex' => [
            'label' => 'Flex',
            'value' => 'flex',
            'pro' => false,
        ],
        'inline' => [
            'label' => 'Inline',
            'value' => 'inline',
            'pro' => true,
        ],
        'block' => [
            'label' => 'Block',
            'value' => 'block',
            'pro' => true,
        ],
        'contents' => [
            'label' => 'Contents',
            'value' => 'contents',
            'pro' => true,
        ],
        'grid' => [
            'label' => 'Grid',
            'value' => 'grid',
            'pro' => true,
        ],
        'inline-block' => [
            'label' => 'Inline Block',
            'value' => 'inline-block',
            'pro' => true,
        ],
        'inline-flex' => [
            'label' => 'Inline Flex',
            'value' => 'inline-flex',
            'pro' => true,
        ],
        'inline-grid' => [
            'label' => 'Inline Grid',
            'value' => 'inline-grid',
            'pro' => true,
        ],
        'inline-table' => [
            'label' => 'Inline Table',
            'value' => 'inline-table',
            'pro' => true,
        ],
        'list-item' => [
            'label' => 'List Item',
            'value' => 'list-item',
            'pro' => true,
        ],
        'run-in' => [
            'label' => 'Run In',
            'value' => 'run-in',
            'pro' => true,
        ],
        'table' => [
            'label' => 'Table',
            'value' => 'table',
            'pro' => true,
        ],
        'table-caption' => [
            'label' => 'Table Caption',
            'value' => 'table-caption',
            'pro' => true,
        ],
        'table-column-group' => [
            'label' => 'Table Column Group',
            'value' => 'table-column-group',
            'pro' => true,
        ],
        'table-header-group' => [
            'label' => 'Table Header Group',
            'value' => 'table-header-group',
            'pro' => true,
        ],
        'table-footer-group' => [
            'label' => 'Table Footer Group',
            'value' => 'table-footer-group',
            'pro' => true,
        ],
        'table-row-group' => [
            'label' => 'Table Row Group',
            'value' => 'table-row-group',
            'pro' => true,
        ],
        'table-cell' => [
            'label' => 'Table Cell',
            'value' => 'table-cell',
            'pro' => true,
        ],
        'table-column' => [
            'label' => 'Table Column',
            'value' => 'table-column',
            'pro' => true,
        ],
        'table-row' => [
            'label' => 'Table Row',
            'value' => 'table-row',
            'pro' => true,
        ],
        'initial' => [
            'label' => 'Initial',
            'value' => 'initial',
            'pro' => true,
        ],
        'inherit' => [
            'label' => 'Inherit',
            'value' => 'inherit',
            'pro' => true,
        ],
    ];

    return $types;
}

// Single general loadmore data
function ald_add_general_loadmore_wrap( $args ){
    // Load Blank variable
    $btn_selector = $load_selector = $visible_items = $load_items = $button_label = $display_type = '';

    // Parse incoming $args into an array and merge it with $defaults
    $args = wp_parse_args( $args, [
        'thiskey' => '',
        'key' => '',
        'selector' => array(),
    ] );

    // Let's extract the array
    extract( $args['selector'] );

    // Array key
    $key =  isset( $args['key'] ) ? wp_generate_password(5, false) . sanitize_text_field( $args['key'] ) : wp_generate_password(5, false);

    if ( !isset( $args['selector']['wrapper_title'] ) ) {
        $wrapper_title = __('Wrapper Title #' . intval($args['thiskey']+1) , 'ajax-load-more-anything');
    }

    $load_more_button_wrapper = __( 'Load More Button Selector', 'ajax-load-more-anything' );
    $load_more_button_wrapper_desc = __( 'Load more button will be insert end of this selector', 'ajax-load-more-anything' );

    $load_more_item_selector = __( 'Load More Items Selector', 'ajax-load-more-anything' );
    $load_more_item_selector_desc = __( 'Selector for load more items. Example: <code>.parent_selector .items</code>', 'ajax-load-more-anything' );

    $visiable_items_text = __( 'Visible Items', 'ajax-load-more-anything' );
    $visiable_items_desc = __( 'How many item will show initially', 'ajax-load-more-anything' );

    $load_items_text = __( 'Load Items', 'ajax-load-more-anything' );
    $load_items_desc = __( 'How Many Item Will Load When Click Load More Button?', 'ajax-load-more-anything' );

    $button_label_text = __( 'Load More Button Label', 'ajax-load-more-anything' );
    $button_label_desc = __( 'Enter the name of Load More Button <br> Use <code>+[count]</code> for countable button like +15 more', 'ajax-load-more-anything' );

    ob_start();
    do_action( 'ald_general_loadmore_before_wrap', $args );
    ?>
    <div id="postimagediv" class="postbox tf_gen_sel_field"> <!-- Wrapper Start -->
        <a class="header ald-toggle-head" data-toggle="collapse">
            <span id="poststuff">
                <h2 class="hndle">
                    <input type="text" class="ald_ajax_wrap_title" name="ald_options[general_loadmore][<?php esc_attr_e( $key ); ?>][wrapper_title]" value="<?php esc_attr_e( $wrapper_title ); ?>" title="<?php esc_attr_e( 'Change title to anything you like. Ex: For Homepage', 'ajax-load-more-anything' ); ?>">
                    <span class="dashicons indicator_field"></span>
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
                                <label for="general_loadmore-btn_selector-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( $load_more_button_wrapper ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-btn_selector-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php esc_attr_e( $key ); ?>][btn_selector]" value="<?php echo esc_attr( $btn_selector ); ?>" placeholder="<?php echo esc_attr( '#selector' ); ?>" />
                            <p><?php esc_html_e( $load_more_button_wrapper_desc ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-load_selector-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( $load_more_item_selector ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-load_selector-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php esc_attr_e( $key ); ?>][load_selector]" value="<?php echo esc_attr( $load_selector ); ?>" placeholder="<?php echo esc_attr( '#selector .repeated_selector' ); ?>" />
                            <p><?php esc_html_e( $load_more_item_selector_desc ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-visible_items-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( $visiable_items_text ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-visible_items-<?php esc_attr_e( $key ); ?>" class="regular-text" type="number" name="ald_options[general_loadmore][<?php esc_attr_e( $key ); ?>][visible_items]" value="<?php echo esc_attr( $visible_items ); ?>" placeholder="<?php echo esc_attr( '6' ); ?>"/>
                            <p><?php esc_html_e( $visiable_items_desc ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-load_items-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( $load_items_text ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-load_items-<?php esc_attr_e( $key ); ?>" class="regular-text" type="number" name="ald_options[general_loadmore][<?php esc_attr_e( $key ); ?>][load_items]" value="<?php echo esc_attr( $load_items ); ?>" placeholder="<?php echo esc_attr( '3' ); ?>" />
                            <p><?php esc_html_e( $load_items_desc ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-button_label-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( $button_label_text ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="general_loadmore-button_label-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php esc_attr_e( $key ); ?>][button_label]" value="<?php echo esc_attr( $button_label ); ?>" placeholder="<?php echo esc_attr( 'Load +[count] more' ); ?>" />
                            <p><?php esc_html_e( $button_label_desc ) ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="general_loadmore-display_type-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Select display type', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <select id="general_loadmore-display_type-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[general_loadmore][<?php esc_attr_e( $key ); ?>][display_type]">
                                
                                <?php foreach( ald_display_types() as $option_value => $option ) : 
                                    $is_pro = isset( $option['pro'] ) ? $option['pro'] : false;
                                    $pro_text = ($is_pro && !defined('ALD_PRO_PLUGIN_URL')) ? ' (Available in Pro) ' : '';
                                    ?>
                                    <option value="<?php esc_attr_e( $option_value ); ?>" <?php selected( $display_type, $option_value ); ?> <?php disabled( ($is_pro && !defined('ALD_PRO_PLUGIN_URL')), true ); ?>>
                                        <?php esc_html_e( $option['label'] . $pro_text ); ?>
                                    </option>
                                <?php endforeach; ?>
                                    
                            </select>
                            
                            <p><?php esc_html_e( 'Select display property for load more items.', 'ajax-load-more-anything' ); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <!-- Wrapper end -->
    </div>
    <?php
    $output = ob_get_clean();
    return do_action( 'ald_general_loadmore_after_wrap', $output, $args );
}

// Add ajax wrapper action
add_action( 'wp_ajax_ald_add_ajax_loadmore', 'ald_add_ajax_loadmore_action' );
function ald_add_ajax_loadmore_action(){

    $key = isset( $_POST['key'] ) ? sanitize_text_field( $_POST['key'] ) : '';
    $thiskey = isset( $_POST['thiskey'] ) ? intval( $_POST['thiskey'] ) : 0;

    $output = ald_add_ajax_loadmore_wrap( array(
        'key' => $key,
        'thiskey' => $key,
    ) );

    echo $output; // phpcs:ignore

    die();
}

// Single ajax loadmore data
function ald_add_ajax_loadmore_wrap( $args ){
    // Load Blank variable
    $event_type = $custom_button_append = $button_trigger_selector = $button_label = $click_selector = $hide_selector_wrapper = $wrapper_to_hide = $update_browser_url = $update_page_title = '';

    // Parse incoming $args into an array and merge it with $defaults
    $args = wp_parse_args( $args, [
        'thiskey' => '',
        'key' => '',
        'selector' => array(),
    ] );

    // Let's extract the array
    extract( $args['selector'] );

    // Array key
    $key =  isset( $args['key'] ) ? $args['key'] : "";

    if ( !isset( $args['selector']['wrapper_title'] ) ) {
        $wrapper_title = __('Wrapper Title #' . intval( $args['thiskey'] + 1 ) , 'ajax-load-more-anything');
    }

    // data_implement_selectors
    if ( !isset( $args['selector']['data_implement_selectors'] ) ) {
        $data_implement_selectors = array();
    }

    $load_more_button_wrapper = __( 'Load More Button Selector', 'ajax-load-more-anything' );
    $load_more_button_wrapper_desc = __( 'Load more button will be insert end of this selector', 'ajax-load-more-anything' );

    $load_more_item_selector = __( 'Load More Items Selector', 'ajax-load-more-anything' );
    $load_more_item_selector_desc = __( 'Selector for load more items. Example: <code>.parent_selector .items</code>', 'ajax-load-more-anything' );

    $visiable_items_text = __( 'Visible Items', 'ajax-load-more-anything' );
    $visiable_items_desc = __( 'How many item will show initially', 'ajax-load-more-anything' );

    $load_items_text = __( 'Load Items', 'ajax-load-more-anything' );
    $load_items_desc = __( 'How Many Item Will Load When Click Load More Button?', 'ajax-load-more-anything' );

    $button_label_text = __( 'Load More Button Label', 'ajax-load-more-anything' );
    $button_label_desc = __( 'Enter the name of Load More Button <br> Use <code>+[count]</code> for countable button like +15 more', 'ajax-load-more-anything' );

    ob_start();
    do_action( 'ald_ajax_loadmore_before_wrap', $args );
    ?>

    <div id="postimagediv" class="postbox tf_ajax_sel_field"> <!-- Wrapper Start -->
        <a class="header ald-toggle-head" data-toggle="collapse">
            <span id="poststuff">
                <h2 class="hndle">
                    <input type="text" class="ald_ajax_wrap_title" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][wrapper_title]" value="<?php esc_attr_e( $wrapper_title ); ?>" title="<?php esc_attr_e( 'Change title to anything you like. Ex: Homepage Posts', 'ajax-load-more-anything' ); ?>">
                    <span class="dashicons indicator_field"></span>
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
                                <label for="ajax_loadmore-event_type-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Event Type:', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <select id="ajax_loadmore-event_type-<?php esc_attr_e( $key ); ?>" data-pro-val="custom_button" class="option-select-lmapro-modal-trigger regular-text ajax_loadmore-event_type" type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][event_type]">
                                <option value="selectors_click" <?php selected( $event_type, 'selectors_click' ); ?>><?php esc_html_e( 'Selector(s) Click', 'ajax-load-more-anything' ); ?></option>
                                <option value="scroll_to_load" <?php selected( $event_type, 'scroll_to_load' ); ?>><?php esc_html_e( 'Scroll to Load (Infinite Scroll)', 'ajax-load-more-anything' ); ?></option>
                                <option value="custom_button" <?php selected( $event_type, 'custom_button' ); ?>><?php esc_html_e( 'Add Custom Button', 'ajax-load-more-anything' ); ?><?php echo defined('ALD_PRO_PLUGIN_URL') ? '' : ' (Available in Pro) '; ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top" data-id="custom_button_append">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-custom_button_append-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Button Insert Selector', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-custom_button_append-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][custom_button_append]" value="<?php echo esc_attr( $custom_button_append ); ?>" placeholder="<?php echo esc_attr( '.selector, #selector' ); ?>" />
                            <p><?php esc_html_e( 'Button will be insert after this selector.', 'ajax-load-more-anything' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top" data-id="button_trigger_selector">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-button_trigger_selector-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Button click trigger Selector', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-button_trigger_selector-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][button_trigger_selector]" value="<?php echo esc_attr( $button_trigger_selector ); ?>" placeholder="<?php echo esc_attr( '.nav-links a.next-link' ); ?>" />
                            <p><?php esc_html_e( 'This selector will be trigger when the button clicked.', 'ajax-load-more-anything' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top" data-id="button_trigger_selector">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-button_label-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Button Label', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-button_label-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][button_label]" value="<?php echo ( $button_label ) ? esc_attr( $button_label ) : esc_attr( 'Load More', 'ajax-load-more-anything' ); ?>" placeholder="<?php echo esc_attr( 'Load More' ); ?>" />
                        </td>
                    </tr>

                    <tr valign="top" data-id="click_selector">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-click_selector-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Enter Selector', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-click_selector-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][click_selector]" value="<?php echo esc_attr( $click_selector ); ?>" placeholder="<?php echo esc_attr( '.nav-links a.link' ); ?>"/>
                            <p><?php esc_html_e( 'Selector should be correct, otherwise ajax will fail to load contents', 'ajax-load-more-anything' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top" data-id="hide_selector_wrapper">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-hide_selector_wrapper-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Hide Selector(s) wrapper', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <select id="ajax_loadmore-hide_selector_wrapper-<?php esc_attr_e( $key ); ?>" class="regular-text ajax_loadmore-hide_selector_wrapper" type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][hide_selector_wrapper]">
                                <option value="no" <?php selected( $hide_selector_wrapper, 'no' ); ?>><?php esc_html_e( 'No', 'ajax-load-more-anything' ); ?></option>
                                <option value="yes" <?php selected( $hide_selector_wrapper, 'yes' ); ?>><?php esc_html_e( 'Yes', 'ajax-load-more-anything' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr valign="top" data-id="wrapper_to_hide">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-wrapper_to_hide-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Wrapper Selector to hide', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <input id="ajax_loadmore-wrapper_to_hide-<?php esc_attr_e( $key ); ?>" class="regular-text" type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][wrapper_to_hide]" value="<?php echo esc_attr( $wrapper_to_hide ); ?>" placeholder="<?php echo esc_attr( '.navigation-area' ); ?>" />
                            <p><?php esc_html_e( 'Enter the selector of the wrapper which you want to hide from visitors', 'ajax-load-more-anything' ); ?></p>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <div class="tf-label">
                                <label for="ajax_loadmore-data_implement_selectors-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Data Implement Selectors', 'ajax-load-more-anything' ); ?></label>
                            </div>
                        </th>
                        <td>
                            <?php
                            $dis_key = uniqid();
                            ?>
                            <div class="data_implement_selectors_wrap">
                                <table class="uptade-browser-title-url-field">
                                    <tr>
                                        <th>
                                            <label for="ajax_loadmore-update_browser_url-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Update Browser URL?', 'ajax-load-more-anything' ); ?></label>
                                        </th>
                                        <th>
                                            <label for="ajax_loadmore-update_page_title-<?php esc_attr_e( $key ); ?>"><?php esc_html_e( 'Update Page Title?', 'ajax-load-more-anything' ); ?></label>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>
                                            <select id="ajax_loadmore-update_browser_url-<?php esc_attr_e( $key ); ?>" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][update_browser_url]">
                                                <option value="no" <?php selected( $update_browser_url, 'no' ); ?>><?php esc_html_e( 'No', 'ajax-load-more-anything' ); ?></option>
                                                <option value="yes" <?php echo defined('ALD_PRO_PLUGIN_URL') ? '' : ' disabled '; selected( $update_browser_url, 'yes' ); ?>><?php esc_html_e( 'Yes', 'ajax-load-more-anything' ); ?><?php echo defined('ALD_PRO_PLUGIN_URL') ? '' : ' (Available in Pro) '; ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <select id="ajax_loadmore-update_page_title-<?php esc_attr_e( $key ); ?>" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][update_page_title]">
                                                <option value="no" <?php selected( $update_page_title, 'no' ); ?>><?php esc_html_e( 'No', 'ajax-load-more-anything' ); ?></option>
                                                <option value="yes" <?php echo defined('ALD_PRO_PLUGIN_URL') ? '' : ' disabled '; selected( $update_page_title, 'yes' ); ?>><?php esc_html_e( 'Yes', 'ajax-load-more-anything' ); ?><?php echo defined('ALD_PRO_PLUGIN_URL') ? '' : ' (Available in Pro) '; ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                                <table>
                                    <tr>
                                        <th><?php esc_html_e( 'Data Selector', 'ajax-load-more-anything' ); ?></th>
                                        <th><?php esc_html_e( 'Implement Type', 'ajax-load-more-anything' ); ?></th>
                                    </tr>

                                    <?php if( $data_implement_selectors ) : ?>
                                        <?php foreach( $data_implement_selectors as $dis_key => $value ) :

                                            $data_selector = isset( $value['data_selector'] ) ? $value['data_selector'] : "";
                                            $implement_type = isset( $value['implement_type'] ) ? $value['implement_type'] : "";

                                            if ( !$data_selector ) {
                                                continue;
                                            }
                                            ?>
                                            <tr class="data_implement_selectors_row">
                                                <td>
                                                    <input type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][data_implement_selectors][<?php esc_attr_e( $dis_key ); ?>][data_selector]" value="<?php echo esc_attr( $data_selector ); ?>" placeholder="<?php echo esc_attr( '.posts-wrapper' ); ?>" />
                                                </td>
                                                <td>
                                                    <select type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][data_implement_selectors][<?php esc_attr_e( $dis_key ); ?>][implement_type]">
                                                        <option value="replace_data" <?php selected( $implement_type, 'replace_data' ); ?>><?php esc_html_e( 'Replace Data', 'ajax-load-more-anything' ); ?></option>
                                                        <option value="insert_after" <?php selected( $implement_type, 'insert_after' ); ?>><?php esc_html_e( 'Insert After', 'ajax-load-more-anything' ); ?></option>
                                                        <option value="insert_before" <?php selected( $implement_type, 'insert_before' ); ?>><?php esc_html_e( 'Insert Before', 'ajax-load-more-anything' ); ?></option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="disr_action">
                                                        <a class="delete_disr">&minus;</a>
                                                        <a class="add_disr">&plus;</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <tr class="data_implement_selectors_row">
                                            <td>
                                                <input type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][data_implement_selectors][0][data_selector]" placeholder="<?php esc_attr_e( 'Enter data selector here', 'ajax-load-more-anything' ); ?>" />
                                            </td>
                                            <td>
                                                <select type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][data_implement_selectors][0][implement_type]">
                                                    <option value="replace_data"><?php esc_html_e( 'Replace Data', 'ajax-load-more-anything' ); ?></option>
                                                    <option value="insert_before"><?php esc_html_e( 'Insert Before', 'ajax-load-more-anything' ); ?></option>
                                                    <option value="insert_after"><?php esc_html_e( 'Insert After', 'ajax-load-more-anything' ); ?></option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="disr_action">
                                                    <a class="delete_disr">&minus;</a>
                                                    <a class="add_disr">&plus;</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <!-- Blank Row  -->
                                    <tr class="data_implement_selectors_row disr_empty-row screen-reader-text">
                                        <td>
                                            <input type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][data_implement_selectors][${j}][data_selector]" placeholder="<?php esc_attr_e( 'Enter data selector here', 'ajax-load-more-anything' ); ?>" />
                                        </td>
                                        <td>
                                            <select type="text" name="ald_options[ajax_loadmore][<?php esc_attr_e( $key ); ?>][data_implement_selectors][${j}][implement_type]">
                                                <option value="replace_data"><?php esc_html_e( 'Replace Data', 'ajax-load-more-anything' ); ?></option>
                                                <option value="insert_before"><?php esc_html_e( 'Insert Before', 'ajax-load-more-anything' ); ?></option>
                                                <option value="insert_after"><?php esc_html_e( 'Insert After', 'ajax-load-more-anything' ); ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="disr_action">
                                                <a class="delete_disr">&minus;</a>
                                                <a class="add_disr">&plus;</a>
                                            </div>
                                        </td>
                                    </tr>


                                </table>
                            </div>
                        </td>
                    </tr>



                </table>
            </div>
        </div>
    <!-- Wrapper end below -->
    </div>
    <?php
    $output = ob_get_clean();

    return do_action( 'ald_ajax_loadmore_after_wrap', $output, $args );
}

/*
* Submit Button
*/
function ald_ajax_save_btn(){
    ?>
    <div class="am_form_buttons">
        <button type="submit" id="ald_submit_settings" class="am_submit_button">
            <div class="am_spinner"></div>
            <?php esc_html_e( 'Save Changes', 'ajax-load-more-anything' ); ?>
        </button>
    </div>
    <?php
}

/**
 * Plugin Name Data
 */
function ald_plugin_name_data(){
    ?>
    <h2>
        <?php esc_html_e( 'Load More Anyting', 'ajax-load-more-anything' ); ?>
        <?php if ( defined('ALD_PRO_PLUGIN_VERSION') ) : ?>
            <sup>pro</sup>
        <?php endif; ?>
    </h2>
    <?php
}
add_action( 'ald_left_panel', 'ald_plugin_name_data', 10 );

/**
 * Plugin Pro Modal
 */
function ald_plugin_pro_modal(){
    if ( defined('ALD_PRO_PLUGIN_VERSION') ) {
        return;
    }
    ?>
    <div id="ald_go-pro" class="am_go-pro-modal-outer" style="display: none;">
        <div class="am_shadow am-modal-close"></div>
        <div class="am_go-pro-modal-inner">
            <div class="am_go-pro-modal">
                <div class="am-modal-close">&times;</div>
                <div class="am_go-pro-modal-content">
                    <div class="very-top">
                        <h2 style=" font-size: 2em; ">It's time to go pro</h2>
                        <p><a class="button button-primary" target="_blank" href="<?php echo esc_url( ALD_GOPRO_URL ); ?>">(<del>$49</del> <strong>$29</strong>) <?php esc_html_e( 'Upgrade to Pro', 'ajax-load-more-anything' ); ?> <span class="dashicons dashicons-external"></span></a><br><small>Limited time offer!</small></p>
                    </div>
                    <div class="in-middle">
                        <p>Checkout more features on <strong>Load More Anything Pro</strong></p>
                        <table class="table ald-table">
                            <thead>
                                <tr>
                                    <th>Load More Anything</th>
                                    <th>Free</th>
                                    <th>Pro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>General Wrapper</td>
                                    <td><span class="dashicons dashicons-yes"></span></td>
                                    <td><span class="dashicons dashicons-yes"></span></td>
                                </tr>
                                <tr>
                                    <td>Ajax Based Wrapper</td>
                                    <td><span class="dashicons dashicons-yes"></span></td>
                                    <td><span class="dashicons dashicons-yes"></span></td>
                                </tr>
                                <tr>
                                    <td>General Wrapper Limit</td>
                                    <td>5</td>
                                    <td>Unlimited</td>
                                </tr>
                                <tr>
                                    <td>Ajax Based Wrapper Limit</td>
                                    <td>1</td>
                                    <td>Unlimited</td>
                                </tr>

                                <tr>
                                    <td>Dynamically Update Browser URL</td>
                                    <td><span class="dashicons dashicons-no-alt"></span></td>
                                    <td><span class="dashicons dashicons-yes"></span></i></td>
                                  </tr>
                                  <tr>
                                    <td>Dynamically Update Page Title</td>
                                    <td><span class="dashicons dashicons-no-alt"></span></td>
                                    <td><span class="dashicons dashicons-yes"></span></i></td>
                                  </tr>
                                  
                                  <tr>
                                    <td>Data Selector</td>
                                    <td>Unlimited</td>
                                    <td>Unlimited</td>
                                  </tr>
                                  
                                  <tr>
                                    <td>Data Implement Type</td>
                                    <td>
                                        <span class="dashicons dashicons-yes"></span></i> Replace Data<br>
                                        <span class="dashicons dashicons-yes"></span></i> Insert After<br>
                                        <span class="dashicons dashicons-yes"></span></i> Insert Before<br>
                                    </td>
                                    <td>
                                        <span class="dashicons dashicons-yes"></span></i> Replace Data<br>
                                        <span class="dashicons dashicons-yes"></span></i> Insert After<br>
                                        <span class="dashicons dashicons-yes"></span></i> Insert Before<br>
                                    </td>
                                  </tr>

                                <tr>
                                    <td>Selector Type</td>
                                    <td>Any</td>
                                    <td>Any</td>
                                </tr>
                                <tr>
                                    <td>Custom CSS</td>
                                    <td><span class="dashicons dashicons-yes"></span></td>
                                    <td><span class="dashicons dashicons-yes"></span></td>
                                </tr>
                                <tr>
                                    <td>Custom JavaScript</td>
                                    <td><span class="dashicons dashicons-no-alt"></span></td>
                                    <td><span class="dashicons dashicons-yes"></span></td>
                                </tr>
                                <tr>
                                    <td>Ajax Preloader</td>
                                    <td><span class="dashicons dashicons-yes"></span></i></td>
                                    <td><span class="dashicons dashicons-yes"></span></i></td>
                                </tr>
                                <tr>
                                    <td>Support Limit</td>
                                    <td>Limited</td>
                                    <td>Unlimited</td>
                                </tr>
                                <tr>
                                    <td>Priority Support</td>
                                    <td><span class="dashicons dashicons-no-alt"></span></td>
                                    <td><span class="dashicons dashicons-yes"></span></td>
                                </tr>
                                <tr>
                                    <td>Unlimited Usage:</td>
                                    <td>
                                        <span class="dashicons dashicons-no-alt"></span> Comments <br>
                                        <span class="dashicons dashicons-no-alt"></span> Custom Post Type <br>
                                        <span class="dashicons dashicons-no-alt"></span> WooCommerce<br>
                                        <span class="dashicons dashicons-no-alt"></span> Products<br>
                                        <span class="dashicons dashicons-no-alt"></span> Easy Digital Downloads<br>
                                        <span class="dashicons dashicons-no-alt"></span> Posts<br>
                                        <span class="dashicons dashicons-no-alt"></span> Pages<br>
                                        <span class="dashicons dashicons-no-alt"></span> Archives<br>
                                        <span class="dashicons dashicons-no-alt"></span> Search Results<br>
                                        <span class="dashicons dashicons-no-alt"></span> Mostly Anywhere<br>              
                                    </td>
                                    <td>
                                        <span class="dashicons dashicons-yes"></span> Comments <br>
                                        <span class="dashicons dashicons-yes"></span> Custom Post Type <br>
                                        <span class="dashicons dashicons-yes"></span> WooCommerce<br>
                                        <span class="dashicons dashicons-yes"></span> Products<br>
                                        <span class="dashicons dashicons-yes"></span> Easy Digital Downloads<br>
                                        <span class="dashicons dashicons-yes"></span> Posts<br>
                                        <span class="dashicons dashicons-yes"></span> Pages<br>
                                        <span class="dashicons dashicons-yes"></span> Archives<br>
                                        <span class="dashicons dashicons-yes"></span> Search Results<br>
                                        <span class="dashicons dashicons-yes"></span> Mostly Anywhere<br>  
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td colspan="2" style=" text-align: center; ">
                                        <p><a class="button button-primary" target="_blank" href="<?php echo esc_url( ALD_GOPRO_URL ); ?>">(<del>$49</del> <strong>$29</strong>) <?php esc_html_e( 'Upgrade to Pro', 'ajax-load-more-anything' ); ?> <span class="dashicons dashicons-external"></span></a><br><small>100% secure transaction</small></p>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <br>
                    
                </div>
            </div>
        </div>
    </div>
    <style>
    .am_shadow.am-modal-close{
        cursor: auto;
    }
    </style>
    <?php
}
add_action( 'admin_footer', 'ald_plugin_pro_modal', 10 );

/**
 * Plugin Pro Modal
 */
function ald_plugin_options_custom_js(){
    if ( defined('ALD_PRO_PLUGIN_VERSION') ) {
        return;
    }
    ?>
    <div class="tf-field-wrap">
        <h4>
            <?php esc_html_e( 'Custom JavaScript', 'ajax-load-more-anything' ); ?>
            <div class="desc"><?php esc_html_e( 'You can trigger custom functions from here.', 'ajax-load-more-anything' ); ?></div>
        </h4>
        <div class="pro-lock" data-modal-show="ald_go-pro" onclick="triggerGoPro()">
            <textarea class="wfull" rows="5" id="ald_options_custom_js"></textarea>
        </div>
    </div>
    <style>
    .pro-lock {
        position: relative;
        cursor: pointer;
    }
    .pro-lock:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 100%;
        background: #ffffff8c;
        z-index: 9;
    }
    .pro-lock:after {
        content: 'Enable JavaScript';
        position: absolute;
        z-index: 10;
        top: 50%;
        transform: translateY(-50%);
        margin: 0 auto;
        left: 0;
        right: 0;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #9a9a9a;
        color: #fff;
        width: 320px;
        visibility: visible;
    }
    .pro-lock[data-modal-show="ald_go-pro"]:hover:after {background: #000;}
    </style>
    <script>
    function triggerGoPro() {
        jQuery(document).trigger('am_modal_show', '#ald_go-pro');
    }
    </script>
    <?php
}
add_action( 'ald_options_js', 'ald_plugin_options_custom_js', 10 );