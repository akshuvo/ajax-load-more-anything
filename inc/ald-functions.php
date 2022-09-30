<?php

// CSS Minifier => http://ideone.com/Q5USEF + improvement(s)
function ald_minify_css($input) {
    if(trim($input) === "") return $input;
    return preg_replace(
        array(
            // Remove comment(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
            // Remove unused white-space(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
            // Replace `:0 0 0 0` with `:0`
            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
            // Replace `background-position:0` with `background-position:0 0`
            '#(background-position):0(?=[;\}])#si',
            // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
            '#(?<=[\s:,\-])0+\.(\d+)#s',
            // Minify string value
            '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
            '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
            // Minify HEX color code
            '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
            // Replace `(border|outline):none` with `(border|outline):0`
            '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
            // Remove empty selector(s)
            '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
        ),
        array(
            '$1',
            '$1$2$3$4$5$6$7',
            '$1',
            ':0',
            '$1:0 0',
            '.$1',
            '$1$3',
            '$1$2$4$5',
            '$1$2$3',
            '$1:0',
            '$1$2'
        ),
    $input);
}

/*
* Custom CSS script
*/
function ald_lite_custom_style(){

	$ald_options =  get_option( 'ald_options' ) ? get_option( 'ald_options' ) : array();
    $general_loadmore = isset( $ald_options['general_loadmore'] ) ? $ald_options['general_loadmore'] : array();
    $ajax_loadmore = isset( $ald_options['ajax_loadmore'] ) ? $ald_options['ajax_loadmore'] : array();
    $custom_css  = isset( $ald_options['custom_css'] ) ? $ald_options['custom_css'] : "";

    // Get only load more selectors
    $general_hide_selectors = array_column( $general_loadmore, 'load_selector' );
    // Trim spaces from selectors
    $general_hide_selectors = array_map( 'trim', $general_hide_selectors );
    // Remove empty selectors
    $general_hide_selectors = array_filter( $general_hide_selectors );

    // Handle ajax hidden selectors
    $ajax_hide_selectors = [];
    if( !empty( $ajax_loadmore ) ){
    	foreach ( $ajax_loadmore as $key => $alvalue ) {
    		if( $alvalue['event_type'] != "selectors_click" && $alvalue['hide_selector_wrapper'] == "yes" && !empty( $alvalue['wrapper_to_hide'] ) ) {
    			$ajax_hide_selectors[] = trim( $alvalue['wrapper_to_hide'] ); 
    		}
    	}
    }

    // Remove empty selectors
    $ajax_hide_selectors = array_filter( $ajax_hide_selectors );
		


    ob_start();
	?>
	<style type="text/css">
		
		/* General Selector Hidden Handle */
		<?php echo implode(',', $general_hide_selectors); ?>{ display: none; } 

		/* Ajax Selector Hidden Handle */
		<?php echo implode(',', $ajax_hide_selectors); ?>{ visibility: hidden; } 

		/* Custom CSS */
		<?php echo $custom_css;?>

	</style><?php
	$output = ob_get_clean();
	//echo _e( $output );
	echo ald_minify_css( $output );
}
add_action( 'wp_head', 'ald_lite_custom_style', 999 ); // Set high priority for execute later


// JavaScript Minifier
function ald_minify_js($input) {
    if(trim($input) === "") return $input;
    return preg_replace(
        array(
            // Remove comment(s)
            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
            // Remove white-space(s) outside the string and regex
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
            // Remove the last semicolon
            '#;+\}#',
            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
            // --ibid. From `foo['bar']` to `foo.bar`
            '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
        ),
        array(
            '$1',
            '$1$2',
            '}',
            '$1$3',
            '$1.$3'
        ),
    $input);
}

// button label
function ald_button_label( $label = null ){

	$wpml_string_id = 'General Wrapper: ' . sanitize_title( $label );

	// WMPL register strings for translation
	if (function_exists ( 'icl_register_string' )){
		icl_register_string('Load More Anyting', $wpml_string_id, $label);
	}

	// WPML Display Translation
	$label = apply_filters('wpml_translate_single_string', $label, 'Load More Anyting', $wpml_string_id );

	// Replace Count
	$label = str_replace("[count]", '<span class="ald-count"></span>', __($label, 'ajax-load-more-anything') );

	return $label;
}

// button label: for ajax wrapper
function ald_ajax_button_label( $label = '' ){

	$wpml_string_id = 'Ajax Wrapper: ' . sanitize_title( $label );

	// WMPL register strings for translation
	if (function_exists ( 'icl_register_string' )){
		icl_register_string('Load More Anyting', $wpml_string_id, $label);
	}

	// WPML Display Translation
	$label = apply_filters('wpml_translate_single_string', $label, 'Load More Anyting', $wpml_string_id );

	// Replace Count
	// $label = str_replace("[count]", '<span class="ald-count"></span>', __($label, 'ajax-load-more-anything') );

	return $label;
}

/**
 * Custom JS
 */
function ald_custom_javascript_code(){

	$ald_options =  get_option( 'ald_options' ) ? get_option( 'ald_options' ) : array();
    $general_loadmore = isset( $ald_options['general_loadmore'] ) ? $ald_options['general_loadmore'] : array();
    $ajax_loadmore = isset( $ald_options['ajax_loadmore'] ) ? $ald_options['ajax_loadmore'] : array();

    ob_start();

	?>
	<script type="text/javascript">
		
		jQuery(document).ready(function($) {

			var loader = '<div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div>';

			<?php if( $general_loadmore ) : ?>

				<?php foreach ( $general_loadmore as $key => $value ) : ?>

					<?php $ald_wrapper_class = isset( $value['btn_selector'] ) && !empty( $value['btn_selector'] ) ? sanitize_text_field( $value['btn_selector'] ) : ''; ?>
					<?php $ald_load_class =  isset( $value['load_selector'] ) && !empty( $value['load_selector'] ) ? sanitize_text_field( $value['load_selector'] ) : '';?>
					<?php $ald_item_show = isset( $value['visible_items'] ) && !empty( $value['visible_items'] ) ? sanitize_text_field( $value['visible_items'] ) : '3'; ?>
					<?php $ald_item_load = isset( $value['load_items'] ) && !empty( $value['load_items'] ) ? sanitize_text_field( $value['load_items'] ) : '3'; ?>
					<?php $ald_load_label = isset( $value['button_label'] ) && !empty( $value['button_label'] ) ? sanitize_text_field( $value['button_label'] ) : __( 'Load More', 'ajax-load-more-anything' );?>
					<?php $display_type = isset( $value['display_type'] ) && !empty( $value['display_type'] ) ? sanitize_text_field( $value['display_type'] ) : '';?>

					// Skip is selectors are empty
					<?php if( empty( $ald_wrapper_class ) || empty( $ald_load_class ) ) :?>
						<?php continue; ?>
					<?php endif; ?>

					// Append the Load More Button
					jQuery("<?php _e( $ald_wrapper_class ); ?>").append('<a data-glm-button-selector="<?php esc_attr_e( $ald_wrapper_class ); ?>"  href="#" class="btn loadMoreBtn" id="loadMore"><span class="loadMoreBtn-label"><?php echo ald_button_label( $ald_load_label ); ?></span></a>');

					<?php if ( $display_type == "flex" ) : ?>

						jQuery("<?php _e( $ald_load_class ); ?>").hide();

						// Show the initial visible items
						jQuery("<?php _e( $ald_load_class ); ?>").slice(0, <?php _e( $ald_item_show ); ?>).css({ 'display': 'flex' });

						// Calculate the hidden items
						jQuery(document).find("<?php _e( $ald_wrapper_class ); ?> .ald-count").text( jQuery("<?php _e( $ald_load_class ); ?>:hidden").length );

						// Button Click Trigger
						jQuery("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").on('click', function (e) {
							e.preventDefault();

							// Show the hidden items
							jQuery("<?php _e( $ald_load_class ); ?>:hidden").slice(0, <?php _e( $ald_item_load ); ?>).css({ 'display': 'flex' });

							// Hide if no more to load
							if ( jQuery("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
								jQuery(this).fadeOut('slow');
							}

							// ReCalculate the hidden items
							jQuery(document).find("<?php _e( $ald_wrapper_class ); ?> .ald-count").text( jQuery("<?php _e( $ald_load_class ); ?>:hidden").length );

						});


					<?php else: ?>

						// Show the initial visible items
						jQuery("<?php _e( $ald_load_class ); ?>").slice(0, <?php _e( $ald_item_show ); ?>).show();

						// Calculate the hidden items
						jQuery(document).find("<?php _e( $ald_wrapper_class ); ?> .ald-count").text( jQuery("<?php _e( $ald_load_class ); ?>:hidden").length );

						// Button Click Trigger
						jQuery("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").on('click', function (e) {
							e.preventDefault();

							// Show the hidden items
							jQuery("<?php _e( $ald_load_class ); ?>:hidden").slice(0, <?php _e( $ald_item_load ); ?>).slideDown();

							// Hide if no more to load
							if ( jQuery("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
								jQuery(this).fadeOut('slow');
							}

							// ReCalculate the hidden items
							jQuery(document).find("<?php _e( $ald_wrapper_class ); ?> .ald-count").text( jQuery("<?php _e( $ald_load_class ); ?>:hidden").length );

						});

					<?php endif; ?>

					// Hide on initial if no div to show
					if ( jQuery("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
						jQuery("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").fadeOut('slow');
						//console.log( 'Load more button hidden because no more item to load' );
					}

				<?php endforeach; ?>

			<?php endif;?> // End General Selector

			// Ajax Handle Function
			var flag = false;
			var main_xhr;

			var LoadMorePushAjax = function( url, args ){

				jQuery('.ald_loader_progress').css({
					"-webkit-transform":"translate3d(-100%, 0px, 0px)",
					"-ms-transform":"translate3d(-100%, 0px, 0px)",
					"transform":"translate3d(-100%, 0px, 0px)",
				});

				if ( args['data_implement_selectors'] ) {
					var dis = JSON.parse( args['data_implement_selectors'] );
				}

				if(main_xhr && main_xhr.readyState != 4){
					main_xhr.abort();
				}

				main_xhr = jQuery.ajax({
					url: url,
					asynch: true,
					beforeSend: function() {
						jQuery('.ald_laser_loader').addClass('show');
						jQuery('.ald_loader_progress').css({
							"transition-duration": "2000ms",
							"-webkit-transform":"translate3d(-20%, 0px, 0px)",
							"-ms-transform":"translate3d(-20%, 0px, 0px)",
							"transform":"translate3d(-20%, 0px, 0px)",
						});

						flag = true;
					},
					success: function(data) {

						// Custom Trigger: Before Load
						jQuery(document).trigger('ald_ajax_content_ready', [data, args]);

						// Data Implement
						if ( dis ) {
							for( var key in dis ) {
								var selector = dis[key].data_selector;
								var type = dis[key].implement_type;

								if ( selector ) {
									var newData = jQuery(selector, data).html();

									// console.log( newData );

									if ( type == "insert_before" ) {
										jQuery( selector ).prepend( newData );
									} else if ( type == "insert_after" ) {
										jQuery( selector ).append( newData );
									} else {
										jQuery( selector ).html( newData );
									}

								}
							}
						}


						flag = false;

						jQuery( document ).find( '.tf_posts_navigation' ).removeClass( 'loading' );

						// Remove loading class
						jQuery('.ald-ajax-btn[data-alm-click-selector]').each(function(){
							if ( jQuery(this).data('alm-click-selector') == args['click_selector'] ) {
								jQuery(this).removeClass('loading');
							}
						});

						// Finish the loading
						jQuery('.ald_loader_progress').css({
							"transition-duration": "500ms",
							"-webkit-transform":"translate3d(0%, 0px, 0px)",
							"-ms-transform":"translate3d(0%, 0px, 0px)",
							"transform":"translate3d(0%, 0px, 0px)",
						});

						// Reset the loader
						setTimeout(function(){
							jQuery('.ald_laser_loader').removeClass('show');
							jQuery('.ald_loader_progress').css({
								"transition-duration": "0ms",
								"-webkit-transform":"translate3d(-100%, 0px, 0px)",
								"-ms-transform":"translate3d(-100%, 0px, 0px)",
								"transform":"translate3d(-100%, 0px, 0px)",
							});
						}, 300);

						// Custom Trigger: Loaded
						jQuery(document).trigger('ald_ajax_content_loaded', data);

						// Ajax success
						jQuery(document).trigger('ald_ajax_content_success', [data, args]);

					}
				});
			};
			// End Ajax Handle Function


			// Start Ajax based
			<?php if( $ajax_loadmore ) : ?>
			<?php do_action( 'load_more_anything_ajax_loadmore', $ajax_loadmore ); ?>

				<?php foreach ( $ajax_loadmore as $key => $value ) : ?>

					<?php $event_type = isset( $value['event_type'] ) ? $value['event_type'] : "selectors_click"; ?>

					<?php $custom_button_append =  isset( $value['custom_button_append'] ) ? $value['custom_button_append'] : ""; ?>
					<?php $button_label =  isset( $value['button_label'] ) ? ald_ajax_button_label($value['button_label']) : ""; ?>

					<?php $button_trigger_selector = isset( $value['button_trigger_selector'] ) ? $value['button_trigger_selector'] : ""; ?>
					<?php $click_selector = isset( $value['click_selector'] ) ? $value['click_selector'] : ""; ?>

					<?php $hide_selector_wrapper = isset( $value['hide_selector_wrapper'] ) ? $value['hide_selector_wrapper'] : ""; ?>
					<?php $wrapper_to_hide = isset( $value['wrapper_to_hide'] ) ? $value['wrapper_to_hide'] : ""; ?>
					<?php $update_browser_url = isset( $value['update_browser_url'] ) ? $value['update_browser_url'] : ""; ?>
					<?php $update_page_title = isset( $value['update_page_title'] ) ? $value['update_page_title'] : ""; ?>
					<?php $data_implement_selectors = isset( $value['data_implement_selectors'] ) ? $value['data_implement_selectors'] : array(); ?>

					<?php if( $event_type == "custom_button" ) : ?>
						<?php $click_selector = $button_trigger_selector; ?>

						jQuery("<?php _e($custom_button_append); ?>").after('<button data-alm-click-selector="<?php esc_attr_e( $click_selector ); ?>" type="button" class="ald-ajax-btn button">'+loader+'<span class="ald-btn-label"><?php esc_attr_e( $button_label ); ?></span></button>');

					<?php endif; ?>

					<?php if( $click_selector ) : ?>

						jQuery( document ).on('click', '<?php _e( $click_selector ); ?>', function(e){
							e.preventDefault();

							// Javascript Array Args
							var args = [];
							args['event_type'] = "<?php _e( $event_type ); ?>";
							args['custom_button_append'] = "<?php _e( $custom_button_append ); ?>";
							args['button_trigger_selector'] = "<?php _e( $button_trigger_selector ); ?>";
							args['button_label'] = "<?php _e( $button_label ); ?>";
							args['click_selector'] = "<?php _e( $click_selector ); ?>";
							args['hide_selector_wrapper'] = "<?php _e( $hide_selector_wrapper ); ?>";
							args['wrapper_to_hide'] = "<?php _e( $wrapper_to_hide ); ?>";
							args['update_browser_url'] = "<?php _e( $update_browser_url ); ?>";
							args['update_page_title'] = "<?php _e( $update_page_title ); ?>";
							args['data_implement_selectors'] = '<?php echo json_encode( $data_implement_selectors ); ?>';

							var targetUrl = ( e.target.href ) ? e.target.href : jQuery(this).context.href;
							LoadMorePushAjax( targetUrl, args );

							<?php do_action( 'after_load_more_push_ajax', $value ); ?>
							

						});

					<?php endif; ?>


					<?php if( $event_type == "scroll_to_load"  ) : ?>

						jQuery( window ).on('scroll', function(e){
							jQuery('<?php _e( $click_selector ); ?>').each(function(i,el){

								var $this = jQuery(this);

								var H = jQuery(window).height(),
									r = el.getBoundingClientRect(),
									t=r.top,
									b=r.bottom;

								var tAdj = parseInt(t-(H/2));

								if ( flag === false && (H >= tAdj) ) {
									//console.log( 'inview' );
									$this.trigger('click');
								} else {
									//console.log( 'outview' );
								}
							});
						});

					<?php endif; ?>

				<?php endforeach; ?>

				// Ajax Custom Button Trigger
				jQuery( document ).on('click', 'button.ald-ajax-btn', function(e){
					if ( jQuery(this).data('alm-click-selector') ) {

						var selector = jQuery( document ).find(jQuery(this).data('alm-click-selector'));

						if ( selector.length ) {
							jQuery(this).addClass('loading');
							selector.trigger('click');
						} else {
							jQuery(this).find('.ald-btn-label').text('No New Data Found');
						}

					}
				});

			<?php endif; ?> // End Ajax Selector

		});


	</script>
	<?php
	$output = ob_get_clean();

	// Minify
	$output = ald_minify_js( $output );

	// Output
	echo $output;
}

add_action('wp_footer','ald_custom_javascript_code', 9999);

/**
 * Ajax Loader on top
 *
 */
function ald_ajax_laser_loader(){
	?>
	<div class="ald_laser_loader">
		<div class="ald_loader_progress"></div>
	</div>
	<?php
}
add_action('wp_footer', 'ald_ajax_laser_loader');