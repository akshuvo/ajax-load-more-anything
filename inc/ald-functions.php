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



/*
* Custom CSS script
*/
function ald_custom_style(){?>
	<style type="text/css">
		<?php
		if(!empty(get_option('ald_load_class'))){
			echo __( get_option('ald_load_class') );
		}
		if(!empty(get_option('ald_load_classa'))){
			echo ','.__( get_option('ald_load_classa') );
		}
		if(!empty(get_option('ald_load_classb'))){
			echo ','.__( get_option('ald_load_classb') );
		}
		if(!empty(get_option('ald_load_classc'))){
			echo ','.__( get_option('ald_load_classc') );
		}
		if(!empty(get_option('ald_load_classd'))){
			echo ','.__( get_option('ald_load_classd') );
		}
		?>{
			display: none;
		}

		<?php

		$default = '
		.btn.loadMoreBtn {
			color: #333333;
			text-align: center;
		}

		.btn.loadMoreBtn:hover {
		  text-decoration: none;
		}';

		echo __( get_option('asr_ald_css_class', $default) );
		?>

	</style>
<?php }

add_action('wp_head','ald_custom_style');


// button label
function ald_button_label( $label = null ){

	$label = str_replace("[count]", '<span class="ald-count"></span>', $label );

	return $label;
}


/**
 * Custom JS
 */
function ald_custom_code(){?>
	<script type="text/javascript">
		(function($) {
			'use strict';

			jQuery(document).ready(function() {

				var noItemMsg = "Load more button hidden because no more item to load";

				//wrapper 1
				<?php if(!empty(get_option('ald_wrapper_class'))):?>

					<?php $ald_wrapper_class = get_option('ald_wrapper_class'); ?>
					<?php $ald_load_class = get_option('ald_load_class'); ?>
					<?php $ald_load_label = get_option('ald_load_label'); ?>
					<?php $ald_item_show = get_option('ald_item_show'); ?>
					<?php $ald_item_load = get_option('ald_item_load'); ?>

					// Append the Load More Button
					$("<?php _e( $ald_wrapper_class ); ?>").append('<a href="#" class="btn loadMoreBtn" id="loadMore"><span class="loadMoreBtn-label"><?php echo ald_button_label( $ald_load_label ); ?></span></a>');

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

					// Hide on initial if no div to show
					if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
						$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").fadeOut('slow');
						console.log( noItemMsg );
					}


				<?php endif;?>
				// end wrapper 1

				//wrapper 2
				<?php if(!empty(get_option('ald_wrapper_classa'))):?>

					<?php $ald_wrapper_class = get_option('ald_wrapper_classa'); ?>
					<?php $ald_load_class = get_option('ald_load_classa'); ?>
					<?php $ald_load_label = get_option('ald_load_labela'); ?>
					<?php $ald_item_show = get_option('ald_item_showa'); ?>
					<?php $ald_item_load = get_option('ald_item_loada'); ?>

					// Append the Load More Button
					$("<?php _e( $ald_wrapper_class ); ?>").append('<a href="#" class="btn loadMoreBtn" id="loadMore"><span class="loadMoreBtn-label"><?php echo ald_button_label( $ald_load_label ); ?></span></a>');

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

					// Hide on initial if no div to show
					if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
						$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").fadeOut('slow');
						console.log( noItemMsg );
					}
				<?php endif;?>
				// end wrapper 2

				// wrapper 3
				<?php if(!empty(get_option('ald_wrapper_classb'))):?>

					<?php $ald_wrapper_class = get_option('ald_wrapper_classb'); ?>
					<?php $ald_load_class = get_option('ald_load_classb'); ?>
					<?php $ald_load_label = get_option('ald_load_labelb'); ?>
					<?php $ald_item_show = get_option('ald_item_showb'); ?>
					<?php $ald_item_load = get_option('ald_item_loadb'); ?>

					// Append the Load More Button
					$("<?php _e( $ald_wrapper_class ); ?>").append('<a href="#" class="btn loadMoreBtn" id="loadMore"><span class="loadMoreBtn-label"><?php echo ald_button_label( $ald_load_label ); ?></span></a>');

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

					// Hide on initial if no div to show
					if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
						$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").fadeOut('slow');
						console.log( noItemMsg );
					}
				<?php endif;?>
				// end wrapper 3

				//wraper 4
				<?php if(!empty(get_option('ald_wrapper_classc'))):?>
					<?php $ald_wrapper_class = get_option('ald_wrapper_classc'); ?>
					<?php $ald_load_class = get_option('ald_load_classc'); ?>
					<?php $ald_load_label = get_option('ald_load_labelc'); ?>
					<?php $ald_item_show = get_option('ald_item_showc'); ?>
					<?php $ald_item_load = get_option('ald_item_loadc'); ?>

					// Append the Load More Button
					$("<?php _e( $ald_wrapper_class ); ?>").append('<a href="#" class="btn loadMoreBtn" id="loadMore"><span class="loadMoreBtn-label"><?php echo ald_button_label( $ald_load_label ); ?></span></a>');

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

					// Hide on initial if no div to show
					if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
						$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").fadeOut('slow');
						console.log( noItemMsg );
					}
				<?php endif;?>
				// end wrapper 4

				//wrapper 5
				<?php if(!empty(get_option('ald_wrapper_classd'))):?>
					<?php $ald_wrapper_class = get_option('ald_wrapper_classd'); ?>
					<?php $ald_load_class = get_option('ald_load_classd'); ?>
					<?php $ald_load_label = get_option('ald_load_labeld'); ?>
					<?php $ald_item_show = get_option('ald_item_showd'); ?>
					<?php $ald_item_load = get_option('ald_item_loadd'); ?>

					// Append the Load More Button
					$("<?php _e( $ald_wrapper_class ); ?>").append('<a href="#" class="btn loadMoreBtn" id="loadMore"><span class="loadMoreBtn-label"><?php echo ald_button_label( $ald_load_label ); ?></span></a>');

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

					// Hide on initial if no div to show
					if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
						$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").fadeOut('slow');
						console.log( noItemMsg );
					}
				<?php endif;?>
				// end wrapper 5

				//wrapper 6
				<?php if(!empty(get_option('ald_wrapper_classe'))):?>

					<?php $ald_wrapper_class = get_option('ald_wrapper_classe'); ?>
					<?php $ald_load_class = get_option('ald_load_classe'); ?>
					<?php $ald_load_label = get_option('ald_load_labele'); ?>
					<?php $ald_item_show = get_option('ald_item_showe'); ?>
					<?php $ald_item_load = get_option('ald_item_loade'); ?>

					// Append the Load More Button
					$("<?php _e( $ald_wrapper_class ); ?>").append('<a href="#" class="btn loadMoreBtn" id="loadMore"><span class="loadMoreBtn-label"><?php echo ald_button_label( $ald_load_label ); ?></span></a>');

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

					// Hide on initial if no div to show
					if ( $("<?php _e( $ald_load_class ); ?>:hidden").length == 0 ) {
						$("<?php _e( $ald_wrapper_class ); ?>").find("#loadMore").fadeOut('slow');
						console.log( noItemMsg );
					}
				<?php endif;?>
				// end wrapper 6

			});

		})(jQuery);
	</script>
<?php }

add_action('wp_footer','ald_custom_code');