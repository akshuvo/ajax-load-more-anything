<?php

/**
 *	Admin Notice Class Including
 */
require_once( dirname( __FILE__ ) . '/class-admin-notice.php' );

/**
 * Review Notice
 */
function ald_review_admin_notices($args){

	$args[] = array(
		'id' => "load_more_review_notices",
		'text' => "<b>We hope you're enjoying this plugin! Could you please give a 5-star rating on WordPress to inspire us?</b>",
		'logo' => "https://ps.w.org/ajax-load-more-anything/assets/icon-256x256.png",
		'border_color' => "#000",
		'is_dismissable' => "true",
		'dismiss_text' => "Dismiss",
		'buttons' => array(
			array(
				'text' => "Ok, you deserve it!",
				'link' => "https://wordpress.org/support/plugin/ajax-load-more-anything/reviews/?filter=5",
				'target' => "_blank",
				'icon' => "dashicons dashicons-external",
				'class' => "button-primary",
			),
		)
	);

	return $args;
}
add_filter( 'addonmaster_admin_notice', 'ald_review_admin_notices' );

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
function ald_custom_style(){

	$ald_options =  get_option( 'ald_options' ) ? get_option( 'ald_options' ) : array();
    $general_loadmore = isset( $ald_options['general_loadmore'] ) ? $ald_options['general_loadmore'] : array();
    $ajax_loadmore = isset( $ald_options['ajax_loadmore'] ) ? $ald_options['ajax_loadmore'] : array();
    $custom_css  = isset( $ald_options['custom_css'] ) ? $ald_options['custom_css'] : "";

    ob_start();
	?>
	<style type="text/css">
		<?php if( $general_loadmore ) : $glcount = 1; ?>
			<?php foreach ( $general_loadmore as $key => $value ) : ?>
				<?php $ald_load_class =  $value['load_selector'];?>
				<?php _e( $ald_load_class ); ?><?php if ( $glcount < count( $general_loadmore ) ) { _e( "," ); } ?>
				<?php $glcount++; ?>
			<?php endforeach; ?> { display: none; }
		<?php endif;?>

		/* Custom CSS */
		<?php _e( $custom_css );?>

	</style><?php
	$output = ob_get_clean();
	echo ald_minify_css( $output );
}
add_action( 'wp_head', 'ald_custom_style', 999 ); // Set high priority for execute later


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

	$label = str_replace("[count]", '<span class="ald-count"></span>', $label );

	return $label;
}


/**
 * Custom JS
 */
function ald_custom_javascript_code(){

	$ald_options =  get_option( 'ald_options' ) ? get_option( 'ald_options' ) : array();
    $general_loadmore = isset( $ald_options['general_loadmore'] ) ? $ald_options['general_loadmore'] : array();
    $ajax_loadmore = isset( $ald_options['ajax_loadmore'] ) ? $ald_options['ajax_loadmore'] : array();
    $custom_css  = isset( $ald_options['custom_css'] ) ? $ald_options['custom_css'] : "";

    ob_start();

	?>
	<script type="text/javascript">
		(function($) {
			'use strict';

			jQuery(document).ready(function() {

				var noItemMsg = "Load more button hidden because no more item to load";

				//wrapper 1
				<?php if( $general_loadmore ) : ?>

					<?php foreach ( $general_loadmore as $key => $value ) : ?>

						<?php //ppr($value);  ?>
						<?php $ald_wrapper_class = $value['btn_selector']; ?>
						<?php $ald_load_class =  $value['load_selector'];?>
						<?php $ald_item_show = $value['visible_items']; ?>
						<?php $ald_item_load = $value['load_items']; ?>
						<?php $ald_load_label = $value['button_label'];?>
						<?php $display_type = $value['display_type'];?>

						// Append the Load More Button
						$("<?php _e( $ald_wrapper_class ); ?>").append('<a href="#" class="btn loadMoreBtn" id="loadMore"><span class="loadMoreBtn-label"><?php echo ald_button_label( $ald_load_label ); ?></span></a>');

						<?php if ( $display_type == "flex" ) : ?>

							$("<?php _e( $ald_load_class ); ?>").hide();

							// Show the initial visible items
							$("<?php _e( $ald_load_class ); ?>").slice(0, <?php _e( $ald_item_show ); ?>).css({ 'display': 'flex' });

							// Calculate the hidden items
							$(document).find("<?php _e( $ald_wrapper_class ); ?> .ald-count").text( $("<?php _e( $ald_load_class ); ?>:hidden").length );

							// Button Click Trigger
							$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").on('click', function (e) {
								e.preventDefault();

								// Show the hidden items
								$("<?php _e( $ald_load_class ); ?>:hidden").slice(0, <?php _e( $ald_item_load ); ?>).css({ 'display': 'flex' });

								// Hide if no more to load
								if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
									$(this).fadeOut('slow');
								}

								// ReCalculate the hidden items
								$(document).find("<?php _e( $ald_wrapper_class ); ?> .ald-count").text( $("<?php _e( $ald_load_class ); ?>:hidden").length );

							});


						<?php else: ?>

							// Show the initial visible items
							$("<?php _e( $ald_load_class ); ?>").slice(0, <?php _e( $ald_item_show ); ?>).show();

							// Calculate the hidden items
							$(document).find("<?php _e( $ald_wrapper_class ); ?> .ald-count").text( $("<?php _e( $ald_load_class ); ?>:hidden").length );

							// Button Click Trigger
							$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").on('click', function (e) {
								e.preventDefault();

								// Show the hidden items
								$("<?php _e( $ald_load_class ); ?>:hidden").slice(0, <?php _e( $ald_item_load ); ?>).slideDown();

								// Hide if no more to load
								if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
									$(this).fadeOut('slow');
								}

								// ReCalculate the hidden items
								$(document).find("<?php _e( $ald_wrapper_class ); ?> .ald-count").text( $("<?php _e( $ald_load_class ); ?>:hidden").length );

							});

						<?php endif; ?>

						// Hide on initial if no div to show
						if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
							$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").fadeOut('slow');
							console.log( noItemMsg );
						}

					<?php endforeach; ?>

				<?php endif;?>
				// end wrapper 1


			});

		})(jQuery);
	</script>
	<?php
	$output = ob_get_clean();
	echo ald_minify_js( $output );
}

add_action('wp_footer','ald_custom_javascript_code');