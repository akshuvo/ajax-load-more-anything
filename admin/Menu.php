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

        $general_loadmore = $ald_options['general_loadmore'] ? $ald_options['general_loadmore'] : array();


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

        $general_loadmore = $ald_options['general_loadmore'] ? $ald_options['general_loadmore'] : array();

        ppr( $ald_options );


        ?>
        <div class="wrap ald-wrap">
            <h1></h1>

            <form method="post" id="ald_option_form">


                <table class="form-table">
                    <tr>
                        <td class="left-col">
                            <div class="postbox">
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
                                                    <div class="tf-label">
                                                        <label for="additional_information"><?php esc_html_e( 'Add Property Informations', 'aldtd' ); ?></label>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Wrapper One Start -->
                            <div id="postimagediv" class="postbox">
                                <a class="header" data-toggle="collapse" href="#divone">
                                    <span id="poststuff">
                                        <h2 class="hndle"><?php esc_html_e( 'Wrapper - 1', 'aldtd' ); ?></h2>
                                    </span>
                                </a>
                                <div id="divone" class="collapse show">
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

                            <!-- Wrapper Two Start -->
                            <div id="postimagediv" class="postbox">
                                <a class="header" data-toggle="collapse" href="#divtwo">
                                    <span id="poststuff">
                                        <h2 class="hndle"><?php esc_html_e( 'Wrapper - 2', 'aldtd' ); ?></h2>
                                    </span>
                                </a>
                                <div id="divtwo" class="collapse">
                                    <div class="inside">
                                        <table class="form-table">
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_button_wrapper ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_wrapper_classa" value="<?php echo esc_attr( get_option('ald_wrapper_classa') ); ?>" />
                                                    <p><?php _e( $load_more_button_wrapper_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_item_selector ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_classa" value="<?php echo esc_attr( get_option('ald_load_classa') ); ?>" />
                                                    <p><?php _e( $load_more_item_selector_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $visiable_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_showa" value="<?php echo esc_attr( get_option('ald_item_showa') ); ?>" />
                                                    <p><?php _e( $visiable_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_loada" value="<?php echo esc_attr( get_option('ald_item_loada') ); ?>" />
                                                    <p><?php _e( $load_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $button_label ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_labela" value="<?php echo esc_attr( get_option('ald_load_labela') ); ?>" />
                                                    <p><?php _e( $button_label_desc ) ?></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Wrapper Two end -->

                            <!-- Wrapper Three Start -->
                            <div id="postimagediv" class="postbox">
                                <a class="header" data-toggle="collapse" href="#divthree">
                                    <span id="poststuff">
                                        <h2 class="hndle"><?php esc_html_e( 'Wrapper - 3', 'aldtd' ); ?></h2>
                                    </span>
                                </a>
                                <div id="divthree" class="collapse">
                                    <div class="inside">
                                        <table class="form-table">
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_button_wrapper ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_wrapper_classb" value="<?php echo esc_attr( get_option('ald_wrapper_classb') ); ?>" />
                                                    <p><?php _e( $load_more_button_wrapper_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_item_selector ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_classb" value="<?php echo esc_attr( get_option('ald_load_classb') ); ?>" />
                                                    <p><?php _e( $load_more_item_selector_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $visiable_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_showb" value="<?php echo esc_attr( get_option('ald_item_showb') ); ?>" />
                                                    <p><?php _e( $visiable_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_loadb" value="<?php echo esc_attr( get_option('ald_item_loadb') ); ?>" />
                                                    <p><?php _e( $load_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $button_label ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_labelb" value="<?php echo esc_attr( get_option('ald_load_labelb') ); ?>" />
                                                    <p><?php _e( $button_label_desc ) ?></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Wrapper Three end -->

                            <!-- Wrapper Four Start -->
                            <div id="postimagediv" class="postbox">
                                <a class="header" data-toggle="collapse" href="#divfour">
                                    <span id="poststuff">
                                        <h2 class="hndle"><?php esc_html_e( 'Wrapper - 4', 'aldtd' ); ?></h2>
                                    </span>
                                </a>
                                <div id="divfour" class="collapse">
                                    <div class="inside">
                                        <table class="form-table">
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_button_wrapper ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_wrapper_classc" value="<?php echo esc_attr( get_option('ald_wrapper_classc') ); ?>" />
                                                    <p><?php _e( $load_more_button_wrapper_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_item_selector ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_classc" value="<?php echo esc_attr( get_option('ald_load_classc') ); ?>" />
                                                    <p><?php _e( $load_more_item_selector_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $visiable_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_showc" value="<?php echo esc_attr( get_option('ald_item_showc') ); ?>" />
                                                    <p><?php _e( $visiable_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_loadc" value="<?php echo esc_attr( get_option('ald_item_loadc') ); ?>" />
                                                    <p><?php _e( $load_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $button_label ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_labelc" value="<?php echo esc_attr( get_option('ald_load_labelc') ); ?>" />
                                                    <p><?php _e( $button_label_desc ) ?></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Wrapper Four end -->

                            <!-- Wrapper Five Start -->
                            <div id="postimagediv" class="postbox">
                                <a class="header" data-toggle="collapse" href="#divfive">
                                    <span id="poststuff">
                                        <h2 class="hndle"><?php esc_html_e( 'Wrapper - 5', 'aldtd' ); ?></h2>
                                    </span>
                                </a>
                                <div id="divfive" class="collapse">
                                    <div class="inside">
                                        <table class="form-table">
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_button_wrapper ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_wrapper_classd" value="<?php echo esc_attr( get_option('ald_wrapper_classd') ); ?>" />
                                                    <p><?php _e( $load_more_button_wrapper_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_item_selector ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_classd" value="<?php echo esc_attr( get_option('ald_load_classd') ); ?>" />
                                                    <p><?php _e( $load_more_item_selector_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $visiable_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_showd" value="<?php echo esc_attr( get_option('ald_item_showd') ); ?>" />
                                                    <p><?php _e( $visiable_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_loadd" value="<?php echo esc_attr( get_option('ald_item_loadd') ); ?>" />
                                                    <p><?php _e( $load_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $button_label ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_labeld" value="<?php echo esc_attr( get_option('ald_load_labeld') ); ?>" />
                                                    <p><?php _e( $button_label_desc ) ?></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Wrapper Five end -->

                            <!-- Wrapper Five Start -->
                            <div id="postimagediv" class="postbox">
                                <a class="header" data-toggle="collapse" href="#divsix">
                                    <span id="poststuff">
                                        <h2 class="hndle"><?php esc_html_e( 'Wrapper - 6 ( For Flex Display )', 'aldtd' ); ?></h2>
                                    </span>
                                </a>
                                <div id="divsix" class="collapse">
                                    <div class="inside">
                                        <table class="form-table">
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_button_wrapper ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_wrapper_classe" value="<?php echo esc_attr( get_option('ald_wrapper_classe') ); ?>" />
                                                    <p><?php _e( $load_more_button_wrapper_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_more_item_selector ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_classe" value="<?php echo esc_attr( get_option('ald_load_classe') ); ?>" />
                                                    <p><?php _e( $load_more_item_selector_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $visiable_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_showe" value="<?php echo esc_attr( get_option('ald_item_showe') ); ?>" />
                                                    <p><?php _e( $visiable_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $load_items ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="number" name="ald_item_loade" value="<?php echo esc_attr( get_option('ald_item_loade') ); ?>" />
                                                    <p><?php _e( $load_items_desc ); ?></p>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"><?php _e( $button_label ); ?></th>
                                                <td>
                                                    <input class="regular-text" type="text" name="ald_load_labele" value="<?php echo esc_attr( get_option('ald_load_labele') ); ?>" />
                                                    <p><?php _e( $button_label_desc ) ?></p>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Wrapper Five end -->

                        </td>
                        <td class="right-col">
                            <h2><?php esc_html_e( 'Custom CSS', 'aldtd' ); ?></h2>
                            <pre><textarea style="width:100%" name="asr_ald_css_class" id="" rows="10"><?php if(empty(get_option('asr_ald_css_class'))){echo ".btn.loadMoreBtn {
                color: #333333;
                text-align: center;
            }

            .btn.loadMoreBtn:hover {
                text-decoration: none;
            }";}else {echo __( get_option('asr_ald_css_class') ); } ?></textarea></pre>

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
    }
}

new ALD_Menu();