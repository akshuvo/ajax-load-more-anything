(function($) {
	"use strict";
	$(document).ready(function(){

		// Tab controlling
	    $('.tf-tab-nav a').on('click',function(e){
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

        // Add Room Ajax
        $(document).on('click', '.tf_add-general-wrapper', function(){
            var $this = $(this);

            var keyLen = jQuery('.tf_gen_sel_field').length;

            var data = {
                action: 'ald_add_general_loadmore',
                key: keyLen,
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


        // Add Room Ajax
        $(document).on('click', '.room-remove', function(){
            $(this).closest('.tf-add-single-room-wrap').remove();
            return false;
        });

        // Field toggle
        $(document).on('click', '.ald-toggle-head', function(e){
        	e.preventDefault();
            $(this).parent().toggleClass('opened').find('.ald-toggle-wrap').slideToggle('fast');
            return false;
        });

        // Enable Code Editor
        wp.codeEditor.initialize($('#ald_options_custom_css'), cm_settings);



	});
})(jQuery);