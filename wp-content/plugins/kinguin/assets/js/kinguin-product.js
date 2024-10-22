var thumbnails = new Swiper("#kinguin_product_gallery_thumbs", {
	spaceBetween: 7,
	slidesPerView: 3,
	slidesPerGroup: 3,
	watchSlidesVisibility: true,
	watchSlidesProgress: true,
	navigation: {
		nextEl: ".swiper-button-next",
		prevEl: ".swiper-button-prev",
	},
	breakpoints: {
		576: {
			slidesPerView: 4,
			slidesPerGroup: 4,
		},
		768: {
			slidesPerView: 5,
			slidesPerGroup: 5,
		},
		992: {
			slidesPerView: 6,
			slidesPerGroup: 6
		},
		1200: {
			slidesPerView: 7,
			slidesPerGroup: 7,
		},
	}
});

var fullPhoto = new Swiper("#kinguin_product_gallery_fulls", {
	autoHeight: true,
	slidesPerView: 1,
	autoResize: true,
	navigation: {
		nextEl: ".swiper-button-next",
		prevEl: ".swiper-button-prev",
	},
	thumbs: {
		swiper: thumbnails,
	},
});

var lightbox = GLightbox();
lightbox.on('slide_changed', ({ prev, current }) => {
	thumbnails.slideTo( current.index );
	fullPhoto.slideTo( current.index );
});

var accordion = new KinguinAccordion( "#kinguin_product_accordion" );

// to fix slider width with Astra theme
jQuery(document).ready(function() {
    let astra_container = jQuery('.ast-container');
    let kinguin_container = jQuery('.kinguin_product_gallery');

    if(typeof kinguin_container != 'undefined' && kinguin_container !== null) {
        if (typeof astra_container != 'undefined' && astra_container !== null) {
            jQuery(astra_container).css('display', 'block');
        }
    }
});