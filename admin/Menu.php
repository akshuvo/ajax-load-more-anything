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
            __( 'Load More Anything Option Panel', 'aldtd' ),
            __( 'Load More Anything', 'aldtd' ),
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
                                            <li class="active"><a href="#welcome"><?php echo esc_html__( 'Welcome', 'aldtd' ); ?></a></li>
                                            <li><a href="#general"><?php echo esc_html__( 'General Selectors', 'aldtd' ); ?></a></li>
                                            <li><a href="#ajax-based"><?php echo esc_html__( 'Ajax Based', 'aldtd' ); ?></a></li>
                                            <li><a href="#custom-code"><?php echo esc_html__( 'Custom Code', 'aldtd' ); ?></a></li>
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
                                                            <h4><?php esc_html_e( 'Welcome to Load More Anything', 'aldtd' ); ?></h4>
                                                            <div class="box-content">
                                                                <p>Checkout our new features.</p>
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
                                                                <hr>
                                                                <h3>Checkout more features on <strong>Load More Anything Pro</strong></h3>
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
                                                                            <td colspan="2">
                                                                                <p>21% OFF only for you. Coupon Code: <input type="text" value="WPUSER" readonly style=" width: 90px; "></p>
                                                                                <p><a class="button button-primary" target="_blank" href="https://addonmaster.com/load-more-anything/?utm_source=dashboard&utm_medium=table&utm_campaign=wpuser">(<del>$25</del> <strong>$19.75</strong>) <?php esc_html_e( 'Upgrade to Pro', 'aldtd' ); ?></a><br><small>100% secure transaction</small></p>
                                                                            </td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="single-box">
                                                        <div class="box-inner">
                                                            <h4><?php esc_html_e( 'Awesome Support', 'aldtd' ); ?></h4>
                                                            <div class="box-content">
                                                                <p>We are ready to give you the best support. If you facing any kind of technical or non-technical issues just create a support topic. We will response real fast.</p>
                                                                <br>
                                                                <p><a class="button" target="_blank" href="https://wordpress.org/support/plugin/ajax-load-more-anything"><?php esc_html_e( 'View Support Forum', 'aldtd' ); ?></a></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="single-box">
                                                        <div class="box-inner">
                                                            <h4><?php esc_html_e( 'Show your Love', 'aldtd' ); ?></h4>
                                                            <div class="box-content">
                                                                <p>If you want to show me some love, The review section is always open for you :). Your awesome review provides me energy to continue development for free.</p>
                                                                <br>
                                                                <p><a class="button" target="_blank" href="https://wordpress.org/support/plugin/ajax-load-more-anything/reviews/#new-post"><?php esc_html_e( 'Write a Review Now', 'aldtd' ); ?></a></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="general" class="tf-tab-content">

                                                <h4>
                                                    <?php esc_html_e( 'General Selectors Panel', 'aldtd' ); ?>
                                                    <div class="desc"><?php esc_html_e( 'Here you can enable load more items which are already visible in your web page', 'aldtd' ); ?></div>
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
                                                        <button type="button" class="tf_add-general-wrapper button"><?php esc_html_e( 'Add Wrapper', 'aldtd' ); ?></button>
                                                    </div>
                                                </div>

                                            </div>

                                            <div id="ajax-based" class="tf-tab-content">
                                                <h4>
                                                    <?php esc_html_e( 'Ajaxify Selector', 'aldtd' ); ?>
                                                    <div class="desc"><?php esc_html_e( 'This options is for ajax based load like: Posts, Products, Custom Post Type, etc', 'aldtd' ); ?></div>
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
                                                        <button type="button" class="tf_add-ajax-wrapper button"><?php esc_html_e( 'Add Ajax Wrapper', 'aldtd' ); ?></button>
                                                    </div>
                                                </div>

                                            </div>

                                            <div id="custom-code" class="tf-tab-content">
                                                <h4>
                                                    <?php esc_html_e( 'Custom Code Panel', 'aldtd' ); ?>
                                                    <div class="desc"><?php esc_html_e( 'Here you can add/modify custom css', 'aldtd' ); ?></div>
                                                </h4>
                                                <div class="tf-field-wrap">
                                                    <div class="ald-label">
                                                        <label for="ald_options_custom_css"><?php esc_html_e( 'Custom CSS', 'aldtd' ); ?></label>
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
                                <tr>
                                    <td>
                                        <strong>
                                            <h3 style=" margin: 0 0 2px 0; "><?php esc_html_e( 'Do you have any work need to be done ?', 'aldtd' ); ?></h3>
                                            <?php esc_html_e( 'We do WordPress Theme & Plugin development or Customization  and Website Maintainance', 'aldtd' ); ?>
                                            <a class="button" title="Get me in touch if you have any custom request" href="mailto:addonmasterwp@gmail.com" style="vertical-align: middle; margin-left: 4px;"><?php esc_html_e( 'Email Us', 'aldtd' ); ?></a>
                                        </strong>
                                        <hr>
                                    </td>
                                </tr>

                                <tr><td><strong><?php esc_html_e( 'If you like my plugin please leave a review for inspire me', 'aldtd' ); ?> <a class="button" target="_blank" href="https://wordpress.org/support/plugin/ajax-load-more-anything/reviews/#new-post" style=" vertical-align: middle; margin-left: 4px; "><?php esc_html_e( 'Review Now', 'aldtd' ); ?></a></strong><hr></td></tr>

                                <tr>
                                    <td>
                                        <div><strong><?php esc_html_e( 'Questions/Suggestions/Support:', 'aldtd' ); ?></strong></div>
                                        <a class="button" target="_blank" href="https://www.youtube.com/watch?v=km6V2bcfc6o" style="margin-left: 4px; "><?php esc_html_e( 'Video Tutorial', 'aldtd' ); ?></a>

                                        <a class="button" target="_blank" href="https://wordpress.org/support/plugin/ajax-load-more-anything" style="margin-left: 4px; "><?php esc_html_e( 'View Support Forum', 'aldtd' ); ?></a>

                                        <a class="button" target="_blank" href="https://github.com/akshuvo/load-more-anything/issues" style="margin-left: 4px; "><?php esc_html_e( 'Create Issue on Github', 'aldtd' ); ?></a>
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
        if ( isset( $args['thiskey'] ) && $args['thiskey'] > 5 ) {
            return;
        }

        echo $output;
    }

    // Ajax Content Render
    function _ajax_content( $output, $args ){
        if ( isset( $args['thiskey'] ) && $args['thiskey'] > 0 ) {
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
}

new ALD_Menu();