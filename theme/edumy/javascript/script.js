(function($) {


  document.addEventListener("DOMContentLoaded", function() {
    
    var currentUrl = window.location.href;
        if (currentUrl.includes("question/edit1.php") ||
        currentUrl.includes("question/bank/managecategories/category1.php?courseid=") ||
        currentUrl.includes("question/bank/managecategories/category.php?courseid=") ||
        currentUrl.includes("question/bank/importquestions/import1.php?courseid=") ||
        currentUrl.includes("question/bank/exportquestions/export1.php")) {
        var breadcrumbWidgets = document.querySelector(".breadcrumb_widgets.ccn-clip-l ");
        if (breadcrumbWidgets) {
            breadcrumbWidgets.style.display = "none";
        }
        let regex = /(&category=[^&]*|&lastchanged=[^&]*)$/;

    // Check if the URL ends with '&category=' or '&lastchanged='
    if (regex.test(currentUrl)) {
        // Remove the matched part from the URL
        let newUrl = currentUrl.replace(regex, '');

        // Redirect to the new URL if it has been modified
        if (newUrl !== currentUrl) {
            window.location.href = newUrl;
        }
    }
        var secondaryMoreMenu = document.querySelector(".ccn-4-navigation");
        if (secondaryMoreMenu) {
            secondaryMoreMenu.style.display = "none";
        }
    }
    
});

$(document).ready(function() {
  // Get the quickmail block by class
  var quickmailBlock = $('.block_quickmail');
  
  // Check if the block exists
  if (quickmailBlock.length) {
      // Get the current URL
      var currentUrl = window.location.href;

      // Log URL for debugging


      // Check if the current URL contains 'course/view.php?id='
      if (!currentUrl.includes('/course/view.php?id=')) {
          // Add the d-none class to hide the element
          quickmailBlock.addClass('invisibleblock');
          $('.block_quickmail').remove();
      }
  }
});


// JavaScript function to check the URL and toggle display property
// JavaScript to hide the specific block
// JavaScript function to check for a specific class and URL condition, then add 'd-none'
// JavaScript function to check if an element contains a specific class and the URL condition







  // always false because of Owl bugs
  var $ccnDirection = $("body").hasClass("dir-rtl") ? false : false;

  (function($) {
    "use strict";
    /* ----- Preloader ----- */
    function preloaderLoad() {
      if ($('.preloader').length) {
        $('.preloader').delay(200).fadeOut(300);
      }
      $(".preloader_disabler").on('click', function() {
        $("#preloader").hide();
      });
    }
    /* ----- Navbar Scroll To Fixed ----- */
    function navbarScrollfixed() {
      $('.navbar-scrolltofixed:not(.ccn_disable_stricky)').scrollToFixed();
      // Causing in-course topic-description sticking: Ticket 2254
      // var summaries = $('.summary');
      // summaries.each(function(i) {
      //     var summary = $(summaries[i]);
      //     var next = summaries[i + 1];
      //     summary.scrollToFixed({
      //         marginTop: $('.navbar-scrolltofixed').outerHeight(true) + 10,
      //         limit: function() {
      //             var limit = 0;
      //             if (next) {
      //                 limit = $(next).offset().top - $(this).outerHeight(true) - 10;
      //             } else {
      //                 limit = $('.footer').offset().top - $(this).outerHeight(true) - 10;
      //             }
      //             return limit;
      //         },
      //         zIndex: 999
      //     });
      // });
    }
    /** Main Menu Custom Script Start **/
    $(window).on('load', function() {
      $(".ace-responsive-menu").aceResponsiveMenu({
        resizeWidth: '768', // Set the same in Media query
        animationSpeed: 'fast', //slow, medium, fast
        accoridonExpAll: false //Expands all the accordion menu on click
      });
    });

    function mobileNavToggle() {
      if ($('#main-nav-bar .navbar-nav .sub-menu').length) {
        var subMenu = $('#main-nav-bar .navbar-nav .sub-menu');
        subMenu.parent('li').children('a').append(function() {
          return '<button class="sub-nav-toggler"> <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>';
        });
        var subNavToggler = $('#main-nav-bar .navbar-nav .sub-nav-toggler');
        subNavToggler.on('click', function() {
          var Self = $(this);
          Self.parent().parent().children('.sub-menu').slideToggle();
          return false;
        });
      };
    }
    /* ----- Tags Bar Code for job list 1 page ----- */
    $('.tags-bar > span i').on('click', function() {
      $(this).parent().fadeOut();
    });
    $(function() {
      $('.btns').on('click', function() {
        $('.content_details').toggleClass('is-full-width');
      });
    });
    /* ----- This code for menu ----- */
    $(window).on('scroll', function() {
      if ($('.scroll-to-top').length) {
        var strickyScrollPos = 100;
        if ($(window).scrollTop() > strickyScrollPos) {
          $('.scroll-to-top').fadeIn(500);
        } else if ($(this).scrollTop() <= strickyScrollPos) {
          $('.scroll-to-top').fadeOut(500);
        }
      };
      if ($('.stricky').length) {
        var headerScrollPos = $('.header-nav').next().offset().top;
        var stricky = $('.stricky:not(.ccn_disable_stricky)');
        if ($(window).scrollTop() > headerScrollPos) {
          stricky.removeClass('slideIn animated');
          stricky.addClass('stricky-fixed slideInDown animated');
        } else if ($(this).scrollTop() <= headerScrollPos) {
          stricky.removeClass('stricky-fixed slideInDown animated');
          stricky.addClass('slideIn animated');
        }
      };
    });
    $(window).on('load', function() {
      $(".mouse_scroll").on('click', function() {
        $('html, body').animate({
          scrollTop: $("#our-courses").offset().top
        }, 1000);
      });
      $(".discover_scroll").on('click', function() {
        $('html, body').animate({
          scrollTop: $("#continue").offset().top
        }, 1000);
      });
    });
    /** Main Menu Custom Script End **/
    /* ----- Blog innerpage sidebar according ----- */
    $(window).on('load', function() {
      $('.collapse').on('show.bs.collapse', function() {
        $(this).siblings('.card-header').addClass('active');
      });
      $('.collapse').on('hide.bs.collapse', function() {
        $(this).siblings('.card-header').removeClass('active');
      });
      // $(function () {
      if ($('[data-toggle="tooltip"]').length) {
        // $('[data-toggle="tooltip"]').tooltip();
      }
      // })
    });
    /* ----- Menu Cart Button Dropdown ----- */
    $(window).on('load', function() {
      // Loop through each nav item
      $('.cart_btn').children('ul.cart').children('li').each(function(indexCount) {
        // loop through each dropdown, count children and apply a animation delay based on their index value
        $(this).children('ul.dropdown_content').children('li').each(function(index) {
          // Turn the index value into a reasonable animation delay
          var delay = 0.1 + index * 0.03;
          // Apply the animation delay
          $(this).css("animation-delay", delay + "s")
        });
      });
    });
    /* Menu Search Popup */
    $(window).on('load', function() {
      var wHeight = window.innerHeight;
      /* search bar middle alignment */
      $('#mk-fullscreen-searchform').css('top', wHeight / 2);
      $('#ccn_mk-fullscreen-search-wrapper').css('top', wHeight / 2);
      /* reform search bar */
      jQuery(window).resize(function() {
        wHeight = window.innerHeight;
        $('#mk-fullscreen-searchform').css('top', wHeight / 2);
        $('#ccn_mk-fullscreen-search-wrapper').css('top', wHeight / 2);
      });
      /* Search */
      $('#search-button, #search-button2').on('click', function() {
        console.log("Open Search, Search Centered");
        $("div.mk-fullscreen-search-overlay").addClass("mk-fullscreen-search-overlay-show");
      });
      $("a.mk-fullscreen-close").on('click', function() {
        console.log("Closed Search");
        $("div.mk-fullscreen-search-overlay").removeClass("mk-fullscreen-search-overlay-show");
      });
    });
    $(window).on('load', function() {

      const cd = new Date().getFullYear() + 1
      if ($('#countdown').length) {
        $('#countdown').countdown({
          year: cd
        });
      }
    });
    /* ----- fact-counter ----- */
    function counterNumber() {
      $('div.timer').counterUp({
        delay: 5,
        time: 2000
      });
    }
    // $('.circlechart').circlechart(); // Initialization
    /* ----- Mobile Nav ----- */
    function mobileNav() {
      $('nav#menu').mmenu();
    }
    // $(window).on('load', function() {
    // });
    /* ----- Candidate SIngle Page Price Slider ----- */
    $(window).on('load', function() {
      // $(function() {
      if ($('#slider-range').length) {
        $("#slider-range").slider({
          range: true,
          min: 50,
          max: 150,
          values: [50, 120],
          slide: function(event, ui) {
            $("#amount").val("$" + ui.values[0] + " - $" + ui.values[1]);
          }
        });
        $("#amount").val("$" + $("#slider-range").slider("values", 0) + " - $" + $("#slider-range").slider("values", 1));
      }
      // });
    });
    /* ----- Employee List v1 page range slider widget ----- */
    $(window).on('load', function() {
      if ($('#slider-range').length) {
        $(".slider-range").slider({
          range: true,
          min: 1998,
          max: 2040,
          values: [1998, 2018],
          slide: function(event, ui) {
            $(".amount").val(ui.values[0]);
            $(".amount2").val(ui.values[1]);
          }
        });
        $(".amount").change(function() {
          $(".slider-range").slider('values', 0, $(this).val());
        });
        $(".amount2").change(function() {
          $(".slider-range").slider('values', 1, $(this).val());
        });
      }
    });
    /* ----- Progress Bar ----- */
    if ($('.progress-levels .progress-box .bar-fill').length) {
      $(".progress-box .bar-fill").each(function() {
        var progressWidth = $(this).attr('data-percent');
        $(this).css('width', progressWidth + '%');
        $(this).children('.percent').html(progressWidth + '%');
      });
    }
    // Display the progress bar calling progressbar.js
    $(document).on('ready', function() {
      $('.progressbar1').progressBar({
        shadow: false,
        percentage: false,
        animation: true,
        barColor: "#2441e7",
      });
      $('.progressbar2').progressBar({
        shadow: false,
        percentage: false,
        animation: true,
        barColor: "#2441e7",
      });
      $('.progressbar3').progressBar({
        shadow: false,
        percentage: false,
        animation: true,
        animateTarget: true,
        barColor: "#2441e7",
      });
      $('.progressbar4').progressBar({
        shadow: false,
        percentage: false,
        animation: true,
        animateTarget: true,
        barColor: "#2441e7",
      });
      $('.progressbar5').progressBar({
        shadow: false,
        percentage: false,
        animation: true,
        animateTarget: true,
        barColor: "#2441e7",
      });
    });
    /* ----- Background Parallax ----- */
    var isMobile = {
      Android: function() {
        return navigator.userAgent.match(/Android/i);
      },
      BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
      },
      iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
      },
      Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
      },
      Windows: function() {
        return navigator.userAgent.match(/IEMobile/i);
      },
      any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
      }
    };
    /* ----- MagnificPopup ----- */
    $(document).on('ready', function() {
      if (($(".popup-img").length > 0) || ($(".popup-iframe").length > 0) || ($(".popup-img-single").length > 0)) {
        $(".popup-img").magnificPopup({
          type: "image",
          fixedContentPos: false,
          gallery: {
            enabled: true,
          }
        });
        $(".popup-img-single").magnificPopup({
          type: "image",
          fixedContentPos: false,
          gallery: {
            enabled: false,
          }
        });
        $('.popup-iframe').magnificPopup({
          // disableOn: 700,
          type: 'iframe',
          preloader: false,
          fixedContentPos: false
        });
        $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
          // disableOn: 700,
          type: 'iframe',
          mainClass: 'mfp-fade',
          removalDelay: 160,
          preloader: false,
          fixedContentPos: false
        });
      };
    });
    $('#myTab a').on('click', function(e) {
      e.preventDefault()
      $(this).tab('show')
    })
    /* ----- Wow animation ----- */
    function wowAnimation() {
      var wow = new WOW({
        animateClass: 'animated',
        mobile: true, // trigger animations on mobile devices (default is true)
        offset: 0
      });
      wow.init();
    }
    /* ----- Date & time Picker ----- */
    if ($('.datepicker').length) {
      $('.datepicker').datetimepicker();
    }
    /* ----- PG Slider ----- */
    if ($('#js-main-slider').length) {
      $('#js-main-slider').pogoSlider({
        autoplay: true,
        autoplayTimeout: 5000,
        displayProgess: true,
        generateNav: false,
        preserveTargetSize: true,
        targetWidth: 1000,
        targetHeight: 300,
        responsive: true
      }).data('plugin_pogoSlider');
    }
    /* ----- Slick Slider For Testimonial ----- */
    $(window).on('load', function() {
      if ($('.tes-for').length) {
        $('.tes-for').slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: false,
          fade: false,
          autoplay: true,
          autoplaySpeed: 2000,
          asNavFor: '.tes-nav'
        });
        $('.tes-nav').slick({
          slidesToShow: 3,
          slidesToScroll: 1,
          asNavFor: '.tes-for',
          dots: false,
          arrows: false,
          centerPadding: '1px',
          centerMode: true,
          focusOnSelect: true
        });
      }
    });
    $(document).on('ready', function() {
      $('[data-fancybox]').fancybox({
        youtube: {
          controls: 0,
          showinfo: 0
        },
        vimeo: {
          color: 'f00'
        }
      });
      /*  One-Grid-Owl-carousel  */
      if ($('.blog_slider_home1').length) {
        $('.blog_slider_home1').owlCarousel({
          loop: false,
          margin: 15,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: true,
          smartSpeed: 2000,
          singleItem: true,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            320: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 1
            },
            992: {
              items: 1
            },
            1200: {
              items: 1
            }
          }
        })
      }
      /*  Popular-Course-Slider-Owl-carousel  */
      if ($('.popular_course_slider').length) {
        $('.popular_course_slider').owlCarousel({
          loop: false,
          margin: 15,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: false,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 3
            },
            1200: {
              items: 4
            },
            1366: {
              items: 4
            },
            1400: {
              items: 5
            }
          }
        })
      }
      /*  Popular-Course-Slider-Owl-carousel  */
      if ($('.popular_course_slider_home3').length) {
        $('.popular_course_slider_home3').owlCarousel({
          loop: false,
          margin: 15,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: false,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 3
            },
            1200: {
              items: 3
            },
            1366: {
              items: 4
            },
            1400: {
              items: 4
            }
          }
        })
      }
      /*  Popular-Course-Slider-Owl-carousel  */
      if ($('.instructor_slider_home3').length) {
        $('.instructor_slider_home3').owlCarousel({
          loop: false,
          margin: 15,
          dots: true,
          nav: false,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: true,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 3
            },
            1200: {
              items: 4
            },
            1366: {
              items: 4
            },
            1400: {
              items: 5
            }
          }
        })
      }
      /*  Media-Home7-Slider-Owl-carousel  */
      if ($('.testimonial_slider_home2').length) {
        $('.testimonial_slider_home2').owlCarousel({
          center: true,
          loop: false,
          margin: 15,
          dots: true,
          nav: false,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: false,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 2
            },
            1200: {
              items: 3
            },
            1366: {
              items: 3
            },
            1400: {
              items: 3
            }
          }
        })
      }
      /*  One-Grid-Owl-carousel  */
      if ($('.testimonial_slider_home3').length) {
        $('.testimonial_slider_home3').owlCarousel({
          loop: false,
          margin: 15,
          dots: true,
          nav: false,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: true,
          smartSpeed: 2000,
          singleItem: true,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            320: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 1
            },
            992: {
              items: 1
            },
            1200: {
              items: 1
            }
          }
        })
      }
      /*  Media-Home7-Slider-Owl-carousel  */
      if ($('.media_slider_home7').length) {
        $('.media_slider_home7').owlCarousel({
          loop: false,
          margin: 15,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: true,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 3
            },
            1200: {
              items: 3
            },
            1366: {
              items: 3
            },
            1400: {
              items: 3
            }
          }
        })
      }
      /*  Team-Slider-Owl-carousel  */
      if ($('.team_slider').length) {
        $('.team_slider').owlCarousel({
          loop: false,
          margin: 30,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: false,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 2,
              center: false
            },
            600: {
              items: 2,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 3
            },
            1200: {
              items: 4
            },
            1366: {
              items: 5
            },
            1400: {
              items: 5
            }
          }
        })
      }
      /*  Team-Slider-Owl-carousel  */
      if ($('.shop_product_slider,.feature_post_slider').length) {
        $('.shop_product_slider,.feature_post_slider').owlCarousel({
          loop: false,
          margin: 30,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: false,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 2,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 3
            },
            1200: {
              items: 3
            },
            1366: {
              items: 4
            },
            1400: {
              items: 4
            }
          }
        })
      }
      /*  Team-Slider-Owl-carousel  */
      if ($('.single_product_slider').length) {
        $('.single_product_slider').owlCarousel({
          animateIn: 'fadeIn',
          loop: false,
          margin: 30,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: true,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 1
            },
            992: {
              items: 1
            },
            1200: {
              items: 1
            },
            1366: {
              items: 1
            },
            1400: {
              items: 1
            }
          }
        })
      }
      /*  Testimonial-Slider-Owl-carousel  */
      if ($('.blog_post_slider_home2').length) {
        $('.blog_post_slider_home2').owlCarousel({
          loop: false,
          margin: 15,
          dots: true,
          nav: false,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: true,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 3
            },
            1200: {
              items: 3
            }
          }
        })
      }
      /* ----- Home Maximage Slider ----- */
      if ($('#maximage').length) {
        $('#maximage').maximage({
          cycleOptions: {
            fx: 'fade',
            speed: 500,
            timeout: 20000,
            prev: '#arrow_left',
            next: '#arrow_right'
          },
          onFirstImageLoaded: function() {
            jQuery('#cycle-loader').hide();
            jQuery('#maximage').fadeIn('fast');
          }
        });
        // Helper function to Fill and Center the HTML5 Video
        jQuery('#html5video').maximage('maxcover');
        // To show it is dynamic html text
        jQuery('.in-slide-content').delay(2000).fadeIn();
      }
    }); /* end Document Ready */
    /*  Testimonial-Slider-Slick-Slider  */
    $(window).on('load', function() {
      var indexToGet = $('.slider .slick-slide').index($('#center_on_me'));
      $('.testimonial-slider-nav').slick({
        slidesToShow: 3,
        infinite: true,
        centerMode: true,
        slidesToScroll: 1,
        initialSlide: indexToGet,
        dots: true,
        focusOnSelect: true,
        responsive: [{
            breakpoint: 1024,
            settings: {
              slidesToShow: 3,
              slidesToScroll: 3,
              infinite: true,
              dots: true
            }
          }, {
            breakpoint: 600,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          }, {
            breakpoint: 480,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          }
          // You can unslick at a given breakpoint now by adding:
          // settings: "unslick"
          // instead of a settings object
        ]
      });
    });

    function ccnProcessSliderAttributes(selector) {
      var ccnSelectorValue = selector;
      var ccnSelector = document.querySelector(ccnSelectorValue);
      var $ccnDtCaroAo = ccnSelector.getAttribute('data-ccn-caro-ao') !== undefined ? ccnSelector.getAttribute('data-ccn-caro-ao') : 'fadeOut';
      var $ccnDtCaroAi = ccnSelector.getAttribute('data-ccn-caro-ai') !== undefined ? ccnSelector.getAttribute('data-ccn-caro-ai') : 'fadeIn';
      var $ccnDtCaroS = ccnSelector.getAttribute('data-ccn-caro-s') !== undefined ? ccnSelector.getAttribute('data-ccn-caro-s') : 1000;
      var $ccnDtCaroL = ccnSelector.getAttribute('data-ccn-caro-l') !== undefined ? ccnSelector.getAttribute('data-ccn-caro-l') : 1;
      var $ccnDtCaroAutoplayT = ccnSelector.getAttribute('data-ccn-caro-ap-to') !== undefined ? parseInt(ccnSelector.getAttribute('data-ccn-caro-ap-to')) : Boolean(0);
      var $ccnDtCaroAutoplayP = ccnSelector.getAttribute('data-ccn-caro-ap-p') !== undefined ? parseInt(ccnSelector.getAttribute('data-ccn-caro-ap-p')) : 0;
      var $ccnDtCaroAutoplay = ccnSelector.getAttribute('data-ccn-caro-ap') !== undefined ? parseInt(ccnSelector.getAttribute('data-ccn-caro-ap')) : 0;
      var ccnReturn = {
        selector: ccnSelector,
        ao: $ccnDtCaroAo,
        ai: $ccnDtCaroAi,
        s: $ccnDtCaroS,
        ap: Boolean($ccnDtCaroAutoplay),
        apt: $ccnDtCaroAutoplayT,
        app: Boolean($ccnDtCaroAutoplayP),
        l: Boolean($ccnDtCaroL)
      };
      return ccnReturn;
    }

    $(window).on('load', function() {
      /*  Expert-Freelancer-Owl-carousel  */
      if ($('.banner-style-one').length) {
        var ccnSliderAttr = ccnProcessSliderAttributes('.banner-style-one');
        $('.banner-style-one--single').owlCarousel({
          items: 1,
          stagePadding: 0,
          margin: 0,
          dots: false,
          nav: false,
          touchDrag: false,
          pullDrag: false,
          freeDrag: false,
          mouseDrag: false,
          // animateOut: 'fadeOut',
          // animateIn: 'fadeIn',
          active: true,
          smartSpeed: 1000,
          autoplay: false
        });
        $('.banner-style-one--multiple').owlCarousel({
          loop: ccnSliderAttr.l,
          items: 1,
          stagePadding: 0,
          margin: 0,
          dots: true,
          nav: true,
          animateOut: ccnSliderAttr.ao,
          animateIn: ccnSliderAttr.ai,
          active: true,
          smartSpeed: ccnSliderAttr.s,
          autoplayTimeout: ccnSliderAttr.apt,
          autoplayHoverPause: ccnSliderAttr.app,
          autoplay: ccnSliderAttr.ap
        });
        if ($('.banner-carousel-btn .left-btn').length) {
          $('.banner-carousel-btn .left-btn').on('click', function() {
            $('.banner-style-one').trigger('prev.owl.carousel');
            return false;
          });
        }
        if ($('.banner-carousel-btn .right-btn').length) {
          $('.banner-carousel-btn .right-btn').on('click', function() {
            $('.banner-style-one').trigger('next.owl.carousel');
            return false;
          });
        }
      }
      /*  Home7-Main-Slider-Owl-carousel  */
      if ($('.banner-style-two').length) {
        var ccnSliderAttr = ccnProcessSliderAttributes('.banner-style-two');

        $('.banner-style-two--single').owlCarousel({
          items: 1,
          stagePadding: 0,
          margin: 0,
          dots: false,
          nav: false,
          touchDrag: false,
          pullDrag: false,
          freeDrag: false,
          mouseDrag: false,
          // animateOut: 'fadeOut',
          // animateIn: 'fadeIn',
          active: true,
          smartSpeed: 1000,
          autoplay: false
        });
        $('.banner-style-two--multiple').owlCarousel({
          loop: ccnSliderAttr.l,
          items: 1,
          stagePadding: 0,
          margin: 0,
          dots: true,
          nav: true,
          animateOut: ccnSliderAttr.ao,
          animateIn: ccnSliderAttr.ai,
          active: true,
          smartSpeed: ccnSliderAttr.s,
          autoplayTimeout: ccnSliderAttr.apt,
          autoplayHoverPause: ccnSliderAttr.app,
          autoplay: ccnSliderAttr.ap
        });



        // $('.banner-style-two').owlCarousel({
        //   loop: ccnSliderAttr.l,
        //   items: 1,
        //   margin: 0,
        //   dots: true,
        //   nav: true,
        //   animateOut: ccnSliderAttr.ao,
        //   animateIn: ccnSliderAttr.ai,
        //   active: true,
        //   smartSpeed: ccnSliderAttr.s,
        //   autoplay: ccnSliderAttr.ap,
        //   autoplayTimeout: ccnSliderAttr.apt,
        //   autoplayHoverPause: ccnSliderAttr.app
        // });
        $('.banner-carousel-btn2 .left-btn').on('click', function() {
          $('.banner-style-two').trigger('next.owl.carousel');
          return false;
        });
        $('.banner-carousel-btn2 .right-btn').on('click', function() {
          $('.banner-style-two').trigger('prev.owl.carousel');
          return false;
        });
      }
      /*  Expert-Freelancer-Owl-carousel  */
      if ($('.blog_post_slider_home4').length) {
        $('.blog_post_slider_home4').owlCarousel({
          loop: false,
          margin: 15,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: true,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 2
            },
            992: {
              items: 3
            },
            1200: {
              items: 3
            }
          }
        })
      }
      /*  One-Grid-Owl-carousel  */
      if ($('.home5_slider.home5_slider--single').length) {
        $('.home5_slider.home5_slider--single').owlCarousel({
          animateIn: 'fadeIn',
          loop: false,
          margin: 15,
          dots: true,
          nav: false,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: false,
          smartSpeed: 2000,
          singleItem: true,
          navText: ['<i class="flaticon-left-arrow"></i> <span>PR </br> EV</span>', '<span>NE </br> XT</span> <i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            320: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 1
            },
            992: {
              items: 1
            },
            1200: {
              items: 1
            }
          }
        })
      }
      if ($('.home5_slider.home5_slider--multiple').length) {
        var ccnSliderAttr = ccnProcessSliderAttributes('.home5_slider');
        $('.home5_slider.home5_slider--multiple').owlCarousel({
          animateIn: ccnSliderAttr.ai,
          animateOut: ccnSliderAttr.ao,
          loop: true,
          margin: 15,
          dots: true,
          nav: false,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: ccnSliderAttr.ap,
          autoplayTimeout: ccnSliderAttr.apt,
          smartSpeed: ccnSliderAttr.s,
          singleItem: true,
          navText: ['<i class="flaticon-left-arrow"></i> <span>PR </br> EV</span>', '<span>NE </br> XT</span> <i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            320: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 1
            },
            992: {
              items: 1
            },
            1200: {
              items: 1
            }
          }
        })
      }
      if ($('.home10_slider').length) {
        $('.home10_slider').owlCarousel({
          loop: false,
          margin: 15,
          dots: false,
          nav: true,
          rtl: $ccnDirection,
          autoplayHoverPause: false,
          autoplay: false,
          singleItem: true,
          smartSpeed: 1200,
          navText: ['<i class="flaticon-left-arrow"></i>', '<i class="flaticon-right-arrow-1"></i>'],
          responsive: {
            0: {
              items: 1,
              center: false
            },
            480: {
              items: 1,
              center: false
            },
            520: {
              items: 1,
              center: false
            },
            600: {
              items: 1,
              center: false
            },
            768: {
              items: 1
            },
            992: {
              items: 1
            },
            1200: {
              items: 1
            }
          }
        })
      }
    });


    if ($('.mySwiper').length) {
      var swiper = new Swiper(".mySwiper", {
        slidesPerView: 4,
        centeredSlides: false,
        slidesPerGroupSkip: 0,
        spaceBetween: 0,
        grabCursor: false,
        autoplay: false,
        loop: true,
        keyboard: {
          enabled: false,
        },
        breakpoints: {
          320: {
            slidesPerView: 1,
            slidesPerGroup: 1,
          },
          // when window width is >= 480px
          480: {
            slidesPerView: 1,
            slidesPerGroup: 1,
          },
          // when window width is >= 640px
          640: {
            slidesPerView: 2,
            slidesPerGroup: 1,
          },
          991: {
            slidesPerView: 2,
            slidesPerGroup: 1,
          },
          1024: {
            slidesPerView: 3,
            slidesPerGroup: 1,
          },
          1200: {
            slidesPerView: 4,
            slidesPerGroup: 1,
          }
        },
        scrollbar: {
          el: ".swiper-scrollbar",
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: false,
        },
      });
    }


    if ($('.uv2_insta_slider').length) {
      $('.uv2_insta_slider').owlCarousel({
        loop: true,
        margin: 0,
        dots: false,
        nav: false,
        rtl: false,
        autoplayHoverPause: false,
        autoplay: 500,
        singleItem: true,
        smartSpeed: 1200,
        navText: [
          '<i class="flaticon-left-arrow"></i>',
          '<i class="flaticon-right-arrow-1"></i>'
        ],
        responsive: {
          0: {
            items: 1,
            center: false
          },
          480: {
            items: 1,
            center: false
          },
          520: {
            items: 1,
            center: false
          },
          600: {
            items: 2,
            center: false
          },
          768: {
            items: 2
          },
          992: {
            items: 3
          },
          1200: {
            items: 4
          },
          1400: {
            items: 5
          }
        }
      })
    }




    /* ----- Scroll To top ----- */
    function scrollToTop() {
      $(window).scroll(function() {
        if ($(this).scrollTop() > 600) {
          $('.scrollToHome').fadeIn();
        } else {
          $('.scrollToHome').fadeOut();
        }
      });
      //Click event to scroll to top
      $('.scrollToHome').on('click', function() {
        $('html, body').animate({
          scrollTop: 0
        }, 800);
        return false;
      });
    }
    /* ======
       When document is ready, do
       ====== */
    $(document).on('ready', function() {
      navbarScrollfixed();

      const observer = lozad('[data-ccn-lazy]', {
        rootMargin: '10px 0px',
        threshold: 0.1,
        enableAutoReload: true
      });
      observer.observe();
    });
    $(window).on('load', function() {
      // add your functions
      // mobileNav();
      scrollToTop();
      wowAnimation();
      mobileNavToggle();
    });
    /* ======
       CCN PRELOADER
       ====== */
    if ($('.preloader.ccn_preloader_5').length) {
      var ccnPreloaderTimeout5 = setTimeout(preloaderLoad, 4500);
    }
    if ($('.preloader.ccn_preloader_4').length) {
      var ccnPreloaderTimeout4 = setTimeout(preloaderLoad, 3500);
    }
    if ($('.preloader.ccn_preloader_3').length) {
      var ccnPreloaderTimeout3 = setTimeout(preloaderLoad, 2500);
    }
    if ($('.preloader.ccn_preloader_2').length) {
      var ccnPreloaderTimeout2 = setTimeout(preloaderLoad, 1500);
    }
    $(document).on('ready', function() {
      if ($('.preloader.ccn_preloader_ready').length) {
        preloaderLoad();
      }
    });
    $(window).on('load', function() {
      if ($('.preloader.ccn_preloader_load').length) {
        preloaderLoad();
      }
      if ($('.preloader.ccn_preloader_5').length) {
        clearTimeout(ccnPreloaderTimeout5);
        preloaderLoad();
      }
      if ($('.preloader.ccn_preloader_4').length) {
        clearTimeout(ccnPreloaderTimeout4);
        preloaderLoad();
      }
      if ($('.preloader.ccn_preloader_3').length) {
        clearTimeout(ccnPreloaderTimeout3);
        preloaderLoad();
      }
      if ($('.preloader.ccn_preloader_2').length) {
        clearTimeout(ccnPreloaderTimeout2);
        preloaderLoad();
      }
      // if ($('.preloader.ccn_preloader_4').length) {
      //   setTimeout(function() {
      //     preloaderLoad();
      //   }, 4000);
      // }
      // if ($('.preloader.ccn_preloader_3').length) {
      //   setTimeout(function() {
      //     preloaderLoad();
      //   }, 3000);
      // }
      // if ($('.preloader.ccn_preloader_2').length) {
      //   setTimeout(function() {
      //     preloaderLoad();
      //   }, 2000);
      // }
    });
    // window on Load function
    $(window).on('load', function() {
      // add your functions
      counterNumber();
        /* We do this for M4 More Menu.Should have no effect on earlier Mdl versions */ window.dispatchEvent(new Event('resize'));

      /* Stellar init function was responsible for causing messy menu bug. Disabling for now. */
      // jQuery(window).stellar({
      //     horizontalScrolling: false,
      //     hideDistantElements: true,
      //     verticalScrolling: !isMobile.any(),
      //     scrollProperty: 'scroll',
      //     responsive: true
      // });
    });
    // window on Scroll function
    $(window).on('scroll', function() {
      // add your functions
    });
    mobileNav();
  })(window.jQuery);
}(jQuery));
