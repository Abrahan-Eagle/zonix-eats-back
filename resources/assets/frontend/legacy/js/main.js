/*  ---------------------------------------------------
    Template Name: Dreams
    Description: Dreams wedding template
    Author: Colorib
    Author URI: https://colorlib.com/
    Version: 1.0
    Created: Colorib
---------------------------------------------------------  */

'use strict';

(function ($) {

    /*------------------
        Preloader
    --------------------*/
    $(window).on('load', function () {
        $(".loader").fadeOut();
        $("#preloder").delay(200).fadeOut("slow");

        /*------------------
            Portfolio filter
        --------------------*/
        $('.portfolio__filter li').on('click', function () {
            $('.portfolio__filter li').removeClass('active');
            $(this).addClass('active');
        });
        if ($('.portfolio__gallery').length > 0) {
            var containerEl = document.querySelector('.portfolio__gallery');
            var mixer = mixitup(containerEl);
        }
    });

    /*------------------
        Background Set
    --------------------*/
    $('.set-bg').each(function () {
        var bg = $(this).data('setbg');
        $(this).css('background-image', 'url(' + bg + ')');
    });

    //Masonary
    $('.work__gallery').masonry({
        itemSelector: '.work__item',
        columnWidth: '.grid-sizer',
        gutter: 10
    });

    /*------------------
		Navigation
	--------------------*/
    $(".mobile-menu").slicknav({
        prependTo: '#mobile-menu-wrap',
        allowParentLinks: true
    });

    /*------------------
		Hero Slider
	--------------------*/
    $('.hero__slider').owlCarousel({
        loop: true,
        dots: true,
        mouseDrag: false,
        animateOut: 'fadeOut',
        animateIn: 'fadeIn',
        items: 1,
        margin: 0,
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true,
    });

    var dot = $('.hero__slider .owl-dot');
    dot.each(function () {
        var index = $(this).index() + 1;
        if (index < 10) {
            $(this).html('0').append(index);
        } else {
            $(this).html(index);
        }
    });

    /*------------------
        Testimonial Slider
    --------------------*/
    $(".testimonial__slider").owlCarousel({
        loop: true,
        margin: 0,
        items: 3,
        dots: true,
        dotsEach: 2,
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true,
        responsive: {
            992: {
                items: 3
            },
            768: {
                items: 2
            },
            320: {
                items: 1
            }
        }
    });

    /*------------------
        Latest Slider
    --------------------*/
    $(".latest__slider").owlCarousel({
        loop: true,
        margin: 0,
        items: 3,
        dots: true,
        dotsEach: 2,
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true,
        responsive: {
            992: {
                items: 3
            },
            768: {
                items: 2
            },
            320: {
                items: 1
            }
        }
    });

    /*------------------
        Logo Slider
    --------------------*/
    $(".logo__carousel").owlCarousel({
        loop: true,
        margin: 100,
        items: 6,
        dots: false,
        smartSpeed: 1200,
        autoHeight: false,
        autoplay: true,
        responsive: {
            992: {
                items: 5
            },
            768: {
                items: 4
            },
            480: {
                items: 3
            },
            320: {
                items: 2
            }
        }
    });

    /*------------------
        Video Popup
    --------------------*/
    $('.video-popup').magnificPopup({
        type: 'iframe'
    });

    /*------------------
        Counter
    --------------------*/
    $('.counter_num').each(function () {
        $(this).prop('Counter', 0).animate({
            Counter: $(this).text()
        }, {
            duration: 4000,
            easing: 'swing',
            step: function (now) {
                $(this).text(Math.ceil(now));
            }
        });
    });

})(jQuery);







$(document).ready(function(){
    // Inicializa solo el carrusel de planes
    $('.aiblock-plans-slider').owlCarousel({
        loop: true,
        margin: 20,
        nav: true,
        responsive: {
            0: { items: 1 },
            600: { items: 2 },
            1000: { items: 3 }
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
// Inicializar Swiper
var swiper = new Swiper('.team-slider', {
slidesPerView: 1,
spaceBetween: 20,
centeredSlides: true,
loop: true,
autoplay: {
delay: 3000,
disableOnInteraction: false,
},
pagination: {
el: '.swiper-pagination',
clickable: true,
},
breakpoints: {
576: {
    slidesPerView: 1.2,
},
768: {
    slidesPerView: 1.5,
},
992: {
    slidesPerView: 2.5,
},
1200: {
    slidesPerView: 3,
}
},
on: {
init: function() {
    // Forzar redibujado para activar transiciones
    this.update();
},
}
});
});

document.addEventListener('DOMContentLoaded', function() {
// Inicializar Swiper
var swiper = new Swiper('.team-slider', {
slidesPerView: 1,
spaceBetween: 20,
centeredSlides: true,
loop: true,
autoplay: {
delay: 3000,
disableOnInteraction: false,
},
pagination: {
el: '.swiper-pagination',
clickable: true,
},
breakpoints: {
576: {
    slidesPerView: 1.2,
},
768: {
    slidesPerView: 1.5,
},
992: {
    slidesPerView: 2.5,
},
1200: {
    slidesPerView: 3,
}
},
on: {
init: function() {
    // Forzar redibujado para activar transiciones
    this.update();
},
}
});
});
