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
        $(document).on('click', '.tf_add-room', function(){
            var $this = $(this);

            var keyLen = jQuery('.tf-add-single-room-wrap').length;

            var data = {
                action: 'tf_add_new_room',
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
                $('.tf_room-fields').append(res);
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

        // Room Field toggle
        $(document).on('click', '.room-expend', function(){
            $(this).closest('.tf-add-single-room-wrap').toggleClass('active').find('.tf-add-single-room-body').slideToggle('fast');
            return false;
        });

        // Room title push on head
        $(document).on('keyup change', '.tf_room-name', function(){
            var thisVal = ( $(this).val() ) ? $(this).val() : "# Room Title";
            $(this).closest('.tf-add-single-room-wrap').find('.tf-room-title').text( thisVal );
        });


	});
})(jQuery);