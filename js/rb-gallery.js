jQuery(document).ready(function($){

	// $( '.swipebox' ).swipebox();


	$('.js-rbacfgallery-pagination a').on('click', function(e){
		e.preventDefault();

		$('.js-rbacfgallery-pagination a').removeClass('active');
		$(this).addClass('active');

		var $gallery = $(this).closest('.rbacfgallery');
		var id = $(this).attr('href').replace('#', '');

		$gallery.find('.rbacfgallery__page').hide();
		$gallery.find('.rbacfgallery__wrapper .rbacfgallery__page').eq( id ).show();
	});


    $('.js-rbacfgallery__slick').slick({
        nextArrow: '<button class="icon-arrow-right" aria-label="right arrow"></button>',
        prevArrow: '<button class="icon-arrow-left" aria-label="left arrow"></button>',
    });

});



;( function( $ ) {

	$( '.swipebox' ).swipebox();

} )( jQuery );