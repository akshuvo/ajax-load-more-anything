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

        $general_loadmore = $ald_options['general_loadmore'] ? $ald_options['general_loadmore'] : array();
        $custom_css  = $ald_options['custom_css'] ? $ald_options['custom_css'] : "";

        ppr( $ald_options );


        ?>
        <div class="wrap ald-wrap">
            <h1></h1>

            <form method="post" id="ald_option_form">


                <table class="form-table">
                    <tr>
                        <td class="left-col">
                            <div class="postbox ald-postbox">
                                <div class="tf_panel-header">
                                    <h2><?php esc_html_e( 'Load More Anyting', 'aldtd' ); ?></h2>
                                </div>
                                <div class="tf-tab-container-wrap">
                                    <div class="tf-box-head">
                                        <ul class="tf-tab-nav">
                                            <li class="active"><a href="#welcome"><?php echo esc_html__( 'Welcome', 'aldtd' ); ?></a></li>
                                            <li><a href="#general"><?php echo esc_html__( 'General Selectors', 'aldtd' ); ?></a></li>
                                            <li><a href="#ajax-based"><?php echo esc_html__( 'Ajax Based', 'aldtd' ); ?></a></li>
                                            <li><a href="#custom-code"><?php echo esc_html__( 'Custom Code', 'aldtd' ); ?></a></li>

                                        </ul>
                                    </div>

                                    <div class="tf-box-content">
                                        <div class="tf-tab-container">

                                            <div id="welcome" class="tf-tab-content active">
                                                <h1><?php echo esc_html__( 'Welcome On Board', 'aldtd' ); ?></h1>
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
                                                            foreach ( $general_loadmore as $key => $selector ) {
                                                                echo ald_add_general_loadmore_wrap( array(
                                                                    'key' => $key,
                                                                    'selector' => $selector,
                                                                ) );
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
                                                    <div class="tf-label">
                                                        <label for="additional_information"><?php esc_html_e( 'Add Property Informations', 'aldtd' ); ?></label>
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

                                            </div>

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
                                            <a class="button" title="Get me in touch if you have any custom request" href="mailto:akhtarujjamanshuvo@gmail.com" style="vertical-align: middle; margin-left: 4px;"><?php esc_html_e( 'Email Us', 'aldtd' ); ?></a>
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

                <?php ald_ajax_save_btn(); ?>

            </form>
        </div>
        <?php
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

        $cm_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );
        wp_localize_script('ald-admin-scripts', 'cm_settings', $cm_settings);
        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');
    }
}

new ALD_Menu();