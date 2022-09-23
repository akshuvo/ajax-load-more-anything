/**
 * Show Some Respect to my hard work and don't try to use the pro plugin illegally
 * It's only about $29.
 */
var ald_pro = alda_params.ald_pro;
(function($) {
	"use strict";
	$(document).ready(function(){

        // Modal Show Function
        function am_modal_show( id ){
            if ( id ) {
                $(document).find(id).fadeIn('fast');
            }
        }

        $( document ).on('am_modal_show', function(e,data){
            am_modal_show( data );
        });

        // Modal Close
        $( document ).on('click', '.am-modal-close', function(e){
            $(this).closest('.am_go-pro-modal-outer').fadeOut();
        });

		// Tab controlling
	    $(document).on('click', '.tf-tab-nav a', function(e){
	    	e.preventDefault();
	    	var targetDiv = $(this).attr('href');

    		if(!$(this).parent().hasClass('active')){
                $(this).parent().addClass('active').siblings().removeClass('active');
            }
            $('.tf-tab-container').find(targetDiv).addClass('active').siblings().removeClass('active');

            //location.hash = targetDiv;
            history.pushState({}, '', targetDiv);

    	});

	    // Active tab from location
    	var hash = window.location.hash;
    	$('.tf-tab-nav a[href="'+hash+'"]').click();

        $(window).on('hashchange', function(){
            var a = /^#?chapter(\d+)-section(\d+)\/?$/i.exec(location.hash);
        });

        // Add General Load More
        $(document).on('click', '.tf_add-general-wrapper', function(){
            var $this = $(this);

            var keyLen = jQuery('.tf_gen_sel_field').length;

            /**
             * Show Some Respect to my hard work and don't try to use the pro plugin illegally
             * It's only about $20. Here is coupon for you: WPUSER
             * Purchase Link: https://addonmaster.com/load-more-anything/?utm_source=code&utm_medium=js&utm_campaign=wpuser
             */
            if ( keyLen >= 6 && ald_pro == "0" ) {
                jQuery(document).trigger('am_modal_show', '#ald_go-pro');
                return;
            }

            var data = {
                action: 'ald_add_general_loadmore',
                key: keyLen,
                thiskey: keyLen,
            }

            $.ajax({
              url: ajaxurl,
              type: 'post',
              data: data,
              beforeSend : function ( xhr ) {
                $this.prop('disabled', true);
              },
              success: function( res ) {
                $this.prop('disabled', false);

                // Data push
                $('.tf_gen_sel_fields').append(res);
              },
              error: function( result ) {
                $this.prop('disabled', false);
                console.error( result );
              }
            });
        });

        // Add Ajax Load More
        $(document).on('click', '.tf_add-ajax-wrapper', function(){
            var $this = $(this);

            var keyLen = jQuery('.tf_ajax_sel_field').length;

            /**
             * Show Some Respect to my hard work and don't try to use the pro plugin illegally
             * It's only about $20. Here is coupon for you: WPUSER
             * Purchase Link: https://addonmaster.com/load-more-anything/?utm_source=code&utm_medium=js&utm_campaign=wpuser
             */
            if ( keyLen >= 1 && ald_pro == "0" ) {
                jQuery(document).trigger('am_modal_show', '#ald_go-pro');
                return;
            }

            var data = {
                action: 'ald_add_ajax_loadmore',
                key: keyLen,
                thiskey: keyLen,
            }

            $.ajax({
              url: ajaxurl,
              type: 'post',
              data: data,
              beforeSend : function ( xhr ) {
                $this.prop('disabled', true);
              },
              success: function( res ) {
                $this.prop('disabled', false);

                // Data push
                $('.tf_ajax_sel_fields').append(res);
              },
              error: function( result ) {
                $this.prop('disabled', false);
                console.error( result );
              }
            });
        });

        // Delete field
        $(document).on('click', 'span.delete_field', function(e){
            e.preventDefault();
            $(this).closest('.tf_gen_sel_field, .tf_ajax_sel_field').remove();
            return false;
        });

        // Field toggle
        $(document).on('click', '.ald-toggle-head', function(e){
        	e.preventDefault();
            $(this).parent().toggleClass('opened').find('.ald-toggle-wrap').slideToggle('fast');
            return false;
        });

        // Generate unique ID
		function makeid(length) {
		   var result           = '';
		   var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		   var charactersLength = characters.length;
		   for ( var i = 0; i < length; i++ ) {
		      result += characters.charAt(Math.floor(Math.random() * charactersLength));
		   }
		   return result;
		}

        // Add Data Implement Row
        $(document).on('click', '.add_disr', function(e){
        	var $this = $(this);

        	$this.closest('.data_implement_selectors_row').find('.delete_disr').removeClass('disabled');

            var row = $this.closest('tbody').find('.disr_empty-row').clone(true);
            row.removeClass( 'disr_empty-row screen-reader-text' );

            row.html(function(i, oldHTML) {
		        return oldHTML.replace(/\${j}/g, makeid(3));
		    });

            row.insertBefore( $this.closest('table').find('tbody>tr:last') );
            return false;
        });

        // Remove Data Implement Row
        $(document).on('click', '.delete_disr', function(e){

            if( $(this).closest('tbody').find('.data_implement_selectors_row').length > 2 ) {
            	$(this).closest('.data_implement_selectors_row').remove();
            } else {
            	$(this).addClass('disabled');
            }

            return false;
        });

        // Event Type Field Hide/Show
        $(document).on('change', '.ajax_loadmore-event_type', function(){
        	var $this = $(this);
        	var value = $this.val();

        	if ( value == "scroll_to_load" ) {

        		$this.closest('.tf_ajax_sel_field').find('tr[data-id="hide_selector_wrapper"], tr[data-id="click_selector"]').fadeIn();

        		$this.closest('.tf_ajax_sel_field').find('tr[data-id="button_trigger_selector"], tr[data-id="custom_button_append"]').hide();

        	} else if( value == "custom_button" ) {

        		$this.closest('.tf_ajax_sel_field').find('tr[data-id="hide_selector_wrapper"], tr[data-id="custom_button_append"], tr[data-id="button_trigger_selector"]').fadeIn();

        		$this.closest('.tf_ajax_sel_field').find('tr[data-id="click_selector"]').hide();
        	} else {

        		$this.closest('.tf_ajax_sel_field').find('tr[data-id="click_selector"]').fadeIn();

        		$this.closest('.tf_ajax_sel_field').find('[data-id="custom_button_append"], [data-id="button_trigger_selector"], [data-id="hide_selector_wrapper"], [data-id="wrapper_to_hide"]').hide();

        	}


        });

        // Hide Selector Event Handle
        $(document).on('change', '.ajax_loadmore-hide_selector_wrapper', function(){
        	var $this = $(this);
        	var value = $this.val();

        	if ( value == "yes" ) {
        		$this.closest('.tf_ajax_sel_field').find('tr[data-id="wrapper_to_hide"]').fadeIn();
        	} else {
        		$this.closest('.tf_ajax_sel_field').find('tr[data-id="wrapper_to_hide"]').hide();
        	}

        });

        // Enable Code Editor
        wp.codeEditor.initialize($('#ald_options_custom_css'), cmcss_settings);
        wp.codeEditor.initialize($('#ald_options_custom_js'), cmjs_settings);

        // Save Options
        $(document).on('submit', '#ald_option_form', function(e){
            e.preventDefault();
            var $this = $(this);

            var form_data = new FormData(this);
            form_data.append('action', 'ald_save_settings');

            $.ajax({
                url: ajaxurl,
                data: form_data,
                type: 'post',
                contentType: false,
                processData: false,
                beforeSend : function ( xhr ) {
                  $this.find('[type="submit"]').prop('disabled', true);
                },
                success: function( res ) {
                  $this.find('[type="submit"]').prop('disabled', false);

                },
                error: function( res ) {
                  $this.find('[type="submit"]').prop('disabled', false);

                }
            });
        });

        // Trigger Pro modal on option selection
        $(document).on('change', '.option-select-lmapro-modal-trigger', function(){

            if ( ald_pro == "0" ) {
                var $this = $(this);

                var value = $this.val();
                var checkVal = $this.attr('data-pro-val');

                if ( value == checkVal ) {
                    jQuery(document).trigger('am_modal_show', '#ald_go-pro');
                    return;
                }
            }
        });

        // Field toggle
        $(document).on('click', '.ald-trigger-pro', function(e){
            e.preventDefault();
            jQuery(document).trigger('am_modal_show', '#ald_go-pro');
        });
        

	});

    $(window).load(function(){
        // Reserialize wrappers
        $('.gen_wrap_sl').each(function(i,el){
            $(this).text( parseInt( i + 1 ) );
        });

        // Trigger on load
        $('.ajax_loadmore-hide_selector_wrapper').trigger('change');
        if ( ald_pro != "0" ) {
            $('.ajax_loadmore-event_type').trigger('change');
        }
    });
})(jQuery);