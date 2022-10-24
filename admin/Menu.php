<?php

/**
 * The Menu handler class
 */
class ALD_Menu {

    /**
     * Initialize the class
     */
    function __construct() {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'ald_general_loadmore_after_wrap', [ $this, '_general_content' ], 10, 2 );
        add_action( 'ald_ajax_loadmore_after_wrap', [ $this, '_ajax_content' ], 10, 2 );

        add_filter( 'ald_before_options_save', [ $this, 'options_save' ] );
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function admin_menu() {
        $parent_slug = 'ald_setting';
        $capability = 'manage_options';

        $hook = add_menu_page(
            __( 'Load More Anything Option Panel', 'ajax-load-more-anything' ),
            __( 'Load More Anything', 'ajax-load-more-anything' ),
            $capability,
            $parent_slug,
            [ $this, 'plugin_page' ],
            'dashicons-update'
        );

        add_action( 'admin_head-' . $hook, [ $this, 'enqueue_assets' ] );
    }

    /**
     * Handles Plugin pages
     *
     * @return void
     */
    public function plugin_page() {

        $ald_options =  get_option( 'ald_options' ) ? get_option( 'ald_options' ) : array();

        // Get Old Options
        if ( get_option('ald_wrapper_class') ) {
            $ald_options['general_loadmore'][] = array(
                'btn_selector' => get_option('ald_wrapper_class'),
                'load_selector' => get_option('ald_load_class'),
                'visible_items' => get_option('ald_item_show'),
                'load_items' => get_option('ald_item_load'),
                'button_label' => get_option('ald_load_label'),
                'display_type' => 'normal',
            );
        }
        if ( get_option('ald_wrapper_classa') ) {
            $ald_options['general_loadmore'][] = array(
                'btn_selector' => get_option('ald_wrapper_classa'),
                'load_selector' => get_option('ald_load_classa'),
                'visible_items' => get_option('ald_item_showa'),
                'load_items' => get_option('ald_item_loada'),
                'button_label' => get_option('ald_load_labela'),
                'display_type' => 'normal',
            );
        }
        if ( get_option('ald_wrapper_classb') ) {
            $ald_options['general_loadmore'][] = array(
                'btn_selector' => get_option('ald_wrapper_classb'),
                'load_selector' => get_option('ald_load_classb'),
                'visible_items' => get_option('ald_item_showb'),
                'load_items' => get_option('ald_item_loadb'),
                'button_label' => get_option('ald_load_labelb'),
                'display_type' => 'normal',
            );
        }
        if ( get_option('ald_wrapper_classc') ) {
            $ald_options['general_loadmore'][] = array(
                'btn_selector' => get_option('ald_wrapper_classc'),
                'load_selector' => get_option('ald_load_classc'),
                'visible_items' => get_option('ald_item_showc'),
                'load_items' => get_option('ald_item_loadc'),
                'button_label' => get_option('ald_load_labelc'),
                'display_type' => 'normal',
            );
        }
        if ( get_option('ald_wrapper_classd') ) {
            $ald_options['general_loadmore'][] = array(
                'btn_selector' => get_option('ald_wrapper_classd'),
                'load_selector' => get_option('ald_load_classd'),
                'visible_items' => get_option('ald_item_showd'),
                'load_items' => get_option('ald_item_loadd'),
                'button_label' => get_option('ald_load_labeld'),
                'display_type' => 'normal',
            );
        }
        if ( get_option('ald_wrapper_classe') ) {
            $ald_options['general_loadmore'][] = array(
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
            $ald_options['custom_css'] = get_option('asr_ald_css_class');
        }

        $general_loadmore = isset( $ald_options['general_loadmore'] ) ? $ald_options['general_loadmore'] : array();
        $ajax_loadmore = isset( $ald_options['ajax_loadmore'] ) ? $ald_options['ajax_loadmore'] : array();
        $custom_css  = isset( $ald_options['custom_css'] ) ? $ald_options['custom_css'] : "";

        ?>
        <div class="wrap ald-wrap">
            <h1></h1>

            <form method="post" id="ald_option_form">

                <table class="form-table">
                    <tr>
                        <td class="left-col">
                            <div class="postbox ald-postbox">
                                <div class="tf_panel-header">
                                    <div class="left-panel">
                                        <?php do_action( 'ald_left_panel' ); ?>
                                    </div>
                                    <div class="right-panel">
                                        <?php ald_ajax_save_btn(); ?>
                                    </div>

                                </div>
                                <div class="tf-tab-container-wrap">
                                    <div class="tf-box-head">
                                        <ul class="tf-tab-nav">
                                            <li class="active"><a href="#welcome"><?php echo esc_html__( 'Welcome', 'ajax-load-more-anything' ); ?></a></li>
                                            <li><a href="#general"><?php echo esc_html__( 'General Selectors', 'ajax-load-more-anything' ); ?></a></li>
                                            <li><a href="#ajax-based"><?php echo esc_html__( 'Ajax Based', 'ajax-load-more-anything' ); ?></a></li>
                                            <li><a href="#custom-code"><?php echo esc_html__( 'Custom Code', 'ajax-load-more-anything' ); ?></a></li>
                                            <?php do_action( 'ald_options_menu', $ald_options ); ?>
                                        </ul>
                                    </div>

                                    <div class="tf-box-content">
                                        <?php ald_ajax_save_btn(); ?>

                                        <div class="tf-tab-container">

                                            <div id="welcome" class="tf-tab-content active">
                                                <div class="welcome-boxes">
                                                    <div class="single-box" style=" width: 100%; ">
                                                        <div class="box-inner">
                                                            <h4><?php esc_html_e( 'Welcome to Load More Anything', 'ajax-load-more-anything' ); ?></h4>
                                                            <div class="box-content">
                                                                <p><?php _e('Checkout our new features.', 'ajax-load-more-anything'); ?></p>
                                                                <ul class="ald-list">
                                                                    <li>Ajax wrapper added</li>
                                                                    <li>Make ajax data loading</li>
                                                                    <li>Zero coding/ Hassle free installation</li>
                                                                    <li>Full control over all data</li>
                                                                    <li>Custom Event Selector</li>
                                                                    <li>Infinite Scroll</li>
                                                                    <li>Add custom button trigger</li>
                                                                    <li>Button Insert Selector Options</li>
                                                                    <li>Button Label Change options</li>
                                                                    <li>Ajax response Data Implement</li>
                                                                    <li>Update Browser URL on Ajax Action</li>
                                                                    <li>Update Page Title on Ajax Action</li>
                                                                    <li>Insert Ajax data anywhere</li>
                                                                    <li>Append/Prepend Ajax response data</li>
                                                                    <li>Unlimited Data Implement Selector</li>
                                                                    <li>Wrapper Title Option</li>
                                                                    <li>JavaScript Based Load More</li>
                                                                    <li>Display type options</li>
                                                                    <li>Controls for Hidden/Visible items</li>
                                                                    <li>Custom CSS & Custom JS</li>
                                                                    <li>Nice UI Panel</li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="single-box">
                                                        <div class="box-inner">
                                                            <h4><?php esc_html_e( 'Awesome Support', 'ajax-load-more-anything' ); ?></h4>
                                                            <div class="box-content">
                                                                <p><?php _e('We are ready to give you the best support. If you facing any kind of technical or non-technical issues just create a support topic. We will response real fast.', 'ajax-load-more-anything'); ?></p>
                                                                <br>
                                                                <p><a class="button" target="_blank" href="https://wordpress.org/support/plugin/ajax-load-more-anything"><?php esc_html_e( 'View Support Forum', 'ajax-load-more-anything' ); ?></a></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="single-box">
                                                        <div class="box-inner">
                                                            <h4><?php esc_html_e( 'Show your Love', 'ajax-load-more-anything' ); ?></h4>
                                                            <div class="box-content">
                                                                <p><?php _e('If you want to show me some love, The review section is always open for you :). Your awesome review provides me energy to continue development for free.', 'ajax-load-more-anything'); ?></p>
                                                                <br>
                                                                <p><a class="button" target="_blank" href="https://wordpress.org/support/plugin/ajax-load-more-anything/reviews/#new-post"><?php esc_html_e( 'Write a Review Now', 'ajax-load-more-anything' ); ?></a></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="general" class="tf-tab-content">

                                                <h4>
                                                    <?php esc_html_e( 'General Selectors Panel', 'ajax-load-more-anything' ); ?>
                                                    <div class="desc"><?php esc_html_e( 'Here you can enable load more items which are already visible in your web page', 'ajax-load-more-anything' ); ?></div>
                                                </h4>

                                                <div class="tf-field-wrap">
                                                    <div class="tf_gen_sel_fields">
                                                        <?php
                                                        if ( $general_loadmore ) {
                                                            $i = 0;
                                                            foreach ( $general_loadmore as $key => $selector ) {
                                                                if ( (isset( $selector['wrapper_title'] ) && $selector['wrapper_title'] != "") || (isset( $selector['btn_selector'] ) && $selector['btn_selector'] != "") ) {
                                                                    echo ald_add_general_loadmore_wrap( array(
                                                                        'key' => $key,
                                                                        'thiskey' => $i,
                                                                        'selector' => $selector,
                                                                    ) );
                                                                    $i++;
                                                                }
                                                            }
                                                        } ?>
                                                    </div>
                                                    <div class="tf_add-wrapper-buttons">
                                                        <button type="button" class="tf_add-general-wrapper button"><?php esc_html_e( 'Add Wrapper', 'ajax-load-more-anything' ); ?></button>
                                                    </div>
                                                </div>

                                            </div>

                                            <div id="ajax-based" class="tf-tab-content">
                                                <h4>
                                                    <?php esc_html_e( 'Ajaxify Selector', 'ajax-load-more-anything' ); ?>
                                                    <div class="desc"><?php esc_html_e( 'This options is for ajax based load like: Posts, Products, Custom Post Type, etc', 'ajax-load-more-anything' ); ?></div>
                                                </h4>

                                                <div class="tf-field-wrap">
                                                    <div class="tf_ajax_sel_fields">
                                                        <?php
                                                        if ( $ajax_loadmore ) {
                                                            $i = 0;
                                                            foreach ( $ajax_loadmore as $key => $selector ) {
                                                                //if ( (isset( $selector['wrapper_title'] ) && $selector['wrapper_title'] != "") ) {
                                                                    echo ald_add_ajax_loadmore_wrap( array(
                                                                        'key' => $key,
                                                                        'thiskey' => $i,
                                                                        'selector' => $selector,
                                                                    ) );
                                                                    $i++;
                                                                //}
                                                            }
                                                        } ?>
                                                    </div>
                                                    <div class="tf_add-wrapper-buttons">
                                                        <button type="button" class="tf_add-ajax-wrapper button"><?php esc_html_e( 'Add Ajax Wrapper', 'ajax-load-more-anything' ); ?></button>
                                                    </div>
                                                </div>

                                            </div>

                                            <div id="custom-code" class="tf-tab-content">
                                                <h4>
                                                    <?php esc_html_e( 'Custom Code Panel', 'ajax-load-more-anything' ); ?>
                                                    <div class="desc"><?php esc_html_e( 'Here you can add/modify custom css', 'ajax-load-more-anything' ); ?></div>
                                                </h4>
                                                <div class="tf-field-wrap">
                                                    <div class="ald-label">
                                                        <label for="ald_options_custom_css"><?php esc_html_e( 'Custom CSS', 'ajax-load-more-anything' ); ?></label>
                                                    </div>

                                                    <textarea name="ald_options[custom_css]" class="wfull" rows="5" id="ald_options_custom_css"><?php _e( $custom_css ); ?></textarea>

                                                </div>

                                                <?php do_action( 'ald_options_js', $ald_options ); ?>

                                            </div>
                                            <?php do_action( 'ald_options_content', $ald_options ); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>

                        </td>

                        <td class="right-col">
                            <table>
                                <?php if ( !defined('ALD_PRO_PLUGIN_VERSION') ) : ?>
                                <tr>
                                    <td>
                                        <h3 style=" margin: 0 0 2px 0; "><?php echo __( 'Introducing Load More Anyting <code>Pro</code>', 'ajax-load-more-anything' ); ?></h3>
                                        <p>
                                            <?php esc_html_e( 'Everything now you can make ajax based!', 'ajax-load-more-anything' ); ?>
                                        </p>
                                        <hr>
                                        <ul class="elementor-icon-list-items">
                                            <li>
                                                <span class="dashicons dashicons-yes-alt"></span> Unlimited General Wrapper 
                                            </li>
                                            <li>
                                                <span class="dashicons dashicons-yes-alt"></span> Unlimited Ajax Based Wrapper
                                            </li>
                                            <li>
                                                <span class="dashicons dashicons-yes-alt"></span> Dynamically Update Browser URL
                                            </li>
                                            <li>
                                                <span class="dashicons dashicons-yes-alt"></span> Dynamically Update Page Title
                                            </li>
                                            <li>
                                                <span class="dashicons dashicons-yes-alt"></span> Custom JavaScript 
                                            </li>
                                            <li>
                                                <span class="dashicons dashicons-yes-alt"></span> Priority Support and More...
                                            </li>
                                        </ul>
                                        <div class="action-btns">
                                            <a class="button d-inline-flex items-center" target="_blank" href="<?php echo esc_url( ALD_GOPRO_URL ); ?>">
                                                <?php echo __( 'Learn More <span class="ml-half dashicons dashicons-external"></span>', 'ajax-load-more-anything' ); ?>
                                            </a>
                                            <a class="button ald-trigger-pro" style="vertical-align: middle; margin-left: 4px;"><?php esc_html_e( 'Free vs Pro Comparison', 'ajax-load-more-anything' ); ?></a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>

                                <tr>
                                    <td>
                                        <br>
                                        <h3 style=" margin: 0 0 2px 0; "><?php echo __( 'Are you stuck somewhere?', 'ajax-load-more-anything' ); ?></h3>
                                        <p>
                                            <?php esc_html_e( 'Get support from our developers. Easy, Quick, and Professional!', 'ajax-load-more-anything' ); ?>
                                        </p>
                                        <hr>
                                        
                                        <ul class="action-btns">
                                            <li>
                                                <a class="button d-inline-flex items-center" target="_blank" href="https://wordpress.org/support/plugin/ajax-load-more-anything"><?php echo __( 'View Support Forum <span class="ml-half dashicons dashicons-external"></span>', 'ajax-load-more-anything' ); ?></a>
                                                
                                            </li>
                                            <li>
                                                <a class="button d-inline-flex items-center" target="_blank" href="<?php echo esc_url('https://addonmaster.com/submit-a-ticket/'); ?>"><?php echo __( 'Submit A Ticket <span class="ml-half dashicons dashicons-external"></span>', 'ajax-load-more-anything' ); ?></a>
                                            </li>
                                            <li>
                                                <a class="button d-inline-flex items-center" href="mailto:addonmasterwp@gmail.com" ><?php echo __( 'Email Us <span class="ml-half dashicons dashicons-email"></span>', 'ajax-load-more-anything' ); ?></a>
                                            </li>
                                        </ul>
                                    </td>
                                </tr>   

                                <tr>
                                    <td>
                                        <br>
                                        <h3 style=" margin: 0 0 2px 0; "><?php echo __( 'Show Your Love', 'ajax-load-more-anything' ); ?></h3>
                                        <p>
                                            <?php esc_html_e( 'If you want to show some love, The review section is always open for you :). Your awesome review provide us energy to continue development for free.', 'ajax-load-more-anything' ); ?>
                                        </p>
                                        <hr>
                                        
                                        <a class="button d-inline-flex items-center" target="_blank" href="<?php echo esc_url('https://wordpress.org/support/plugin/ajax-load-more-anything/reviews/#new-post'); ?>"><?php echo __( 'Write a Review', 'ajax-load-more-anything' ); ?></a>
                                    </td>
                                </tr>   

                            </table>
                        </td>
                    </tr>
                </table>

            </form>

            <?php do_action( 'ald_form_end' ); ?>
        </div>
        <?php
    }

    // General Content Render
    function _general_content( $output, $args ){
        /**
         * Show Some Respect to my hard work and don't try to use the pro plugin illegally
         */
        if ( isset( $args['thiskey'] ) && $args['thiskey'] > 5 && !defined('ALD_PRO_PLUGIN_VERSION') ) {
            echo "<script>triggerGoPro();</script>";
            return;
        }

        echo $output;
    }

    // Ajax Content Render
    function _ajax_content( $output, $args ){
        /**
         * Show Some Respect to my hard work and don't try to use the pro plugin illegally
         */
        if ( isset( $args['thiskey'] ) && $args['thiskey'] > 0 && !defined('ALD_PRO_PLUGIN_VERSION') ) {
            echo "<script>triggerGoPro();</script>";
            return;
        }

        echo $output;
    }

    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'ald-admin-styles' );
        wp_enqueue_script( 'ald-admin-scripts' );
        //wp_enqueue_script( 'wp-codemirror' );

        $cmcss_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
        $cmjs_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/javascript' ) );
        wp_localize_script('ald-admin-scripts', 'cmcss_settings', $cmcss_settings);
        wp_localize_script('ald-admin-scripts', 'cmjs_settings', $cmjs_settings);
        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');
    }

    // Option Save
    public function options_save( $options ) {

        // Validate General Load More
        if ( isset( $options['general_loadmore'] ) && !empty( $options['general_loadmore'] ) && is_array( $options['general_loadmore'] ) ) {
            // Count General Load More
            $general_count = 1;

            // Loop through $options['general_loadmore']
            foreach ( $options['general_loadmore'] as $key => $value ) {

                // Validate $value
                if ( !empty( $value ) ) {
                    $options['general_loadmore'][$key] = array_map( 'sanitize_text_field', $value );
                }

                // Unset $key if $general_count more than 6
                if ( $general_count > 6 && !defined('ALD_PRO_PLUGIN_VERSION') ) {
                    unset( $options['general_loadmore'][$key] );
                }

                $general_count++;
            }
        }
        

        // Validate Ajax Load More
        if ( isset( $options['ajax_loadmore'] ) && !empty( $options['ajax_loadmore'] ) && is_array( $options['ajax_loadmore'] ) ) {
            // Count General Load More
            $ajax_count = 1;

            // Loop through $options['general_loadmore']
            foreach ( $options['ajax_loadmore'] as $key => $value ) {

                // Validate $value
                if ( !empty( $value ) ) {
                    $options['ajax_loadmore'][$key] = $value;
                }

                // Unset $key if $ajax_count more than 1
                if ( $ajax_count > 1 && !defined('ALD_PRO_PLUGIN_VERSION') ) {
                    unset( $options['ajax_loadmore'][$key] );
                }

                $ajax_count++;
            }
        }

        return $options;
    }
}

// Initialize the class
function ALD_Menu() {
    new ALD_Menu();
}

// Initialize the function
ALD_Menu();