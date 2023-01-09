(function ($) {
  var averageSlideWidth = 300;
  var slidesMargin = 0;
  var pageWidth = $(window).width();

  function miraiflixSetup() {
    $(".miraiflix-container").each(function () {
      var totWidth = $(this).width();
      var $slides = $(this).find(".miraiflix-slide-container");
      slidesMargin = parseInt($slides.css("marginLeft"));
      var slidesCount = Math.min(
        Math.max(1, Math.round(totWidth / averageSlideWidth)),
        10
      );
      var availableSpace = totWidth;
      var slidesWidth = availableSpace / slidesCount - slidesMargin * 2;
      var totPages = Math.ceil($slides.length / slidesCount);
      $slides.width(slidesWidth);
      $(this).attr("data-n-slides", slidesCount);
      $(this).attr("data-slides-width", slidesWidth);
      $(this).attr("data-page", 0);
      $(this).attr("data-n-pages", totPages);
      hideShowNavigation($(this));
      if (!$(this).parent().hasClass("grid")) {
        setSlidesClasses($(this));
      }
    });
    $(".miraiflix-inner-container").css("margin-left", "0px");
  }

  function setSlidesClasses($container) {
    var $slides = $container.find(".miraiflix-slide-container");
    var page = parseInt($container.attr("data-page"));
    var slidesCount = parseInt($container.attr("data-n-slides"));
    $slides.removeClass("first");
    $slides.removeClass("last");
    var count = 0;
    $slides.each(function () {
      if (slidesCount * page === count) {
        $(this).addClass("first");
      } else if (slidesCount * page + slidesCount - 1 === count) {
        $(this).addClass("last");
      }
      count++;
    });
  }

  function hideShowNavigation($container) {
    var page = parseInt($container.attr("data-page"));
    var nPages = parseInt($container.attr("data-n-pages"));
    if (page <= 0) {
      $container.find(".miraiflix-navigation-left").fadeOut();
    } else {
      $container.find(".miraiflix-navigation-left").fadeIn();
    }
    if (page + 1 >= nPages) {
      $container.find(".miraiflix-navigation-right").fadeOut();
    } else {
      $container.find(".miraiflix-navigation-right").fadeIn();
    }
  }

  function miraiedu_navigate_slider($container, direction = 1) {
    var $slider = $container.find(".miraiflix-inner-container");
    var slidesWidth = parseFloat($container.attr("data-slides-width"));
    var slidesCount = parseInt($container.attr("data-n-slides"));
    var currentPage = parseInt($container.attr("data-page"));
    var totPages = parseInt($container.attr("data-n-pages"));
    var newPage = currentPage + direction;
    if (newPage < 0 || newPage >= totPages) {
      return;
    }
    $container.attr("data-page", newPage);
    var newMargin = newPage * (slidesWidth + slidesMargin * 2) * slidesCount;
    $slider.css("margin-left", "-" + newMargin + "px");
    hideShowNavigation($container);
    setSlidesClasses($container);
  }

  $(document).on(
    "click",
    ".miraiflix-navigation-right, .miraiflix-navigation-left",
    function (e) {
      var $container = $(this).closest(".miraiflix-container");
      if ($(this).hasClass("miraiflix-navigation-right")) {
        miraiedu_navigate_slider($container, 1);
      } else {
        miraiedu_navigate_slider($container, -1);
      }
    }
  );

  $(".elementor-heading-title").on("swipe", function () {
    console.log("swipeleft");
    miraiedu_navigate_slider($(this), 1);
  });

  var xDown = null;
  var yDown = null;
  $(document)
    .on("touchstart", ".miraiflix-container", function (e) {
      var touch =
        e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
      xDown = touch.clientX;
      yDown = touch.clientY;
      console.log("touchstart");
    })
    .on("touchmove", ".miraiflix-container", function (e) {
      console.log("touchmove");
      if (!xDown || !yDown) {
        return;
      }
      var touch =
        e.originalEvent.touches[0] || e.originalEvent.changedTouches[0];
      xUp = touch.clientX;
      yUp = touch.clientY;
      var xDiff = xDown - xUp;
      var yDiff = yDown - yUp;
      if (Math.abs(xDiff) > Math.abs(yDiff) && Math.abs(xDiff) > 50) {
        console.log("x is grater");
        if (xDiff > 0) {
          console.log("swipe left");
          miraiedu_navigate_slider($(this), 1);
        } else if (xDiff < 0) {
          console.log("swipe right");
          miraiedu_navigate_slider($(this), -1);
        }
        xDown = null;
        yDown = null;
      }
    });

  var lastWheeled = 0;
  $(document).on("wheel", ".miraiflix-container", function (e) {
    var now = Date.now();
    if (now - lastWheeled > 400) {
      if (e.originalEvent.deltaX > 30) {
        miraiedu_navigate_slider($(this), 1);
      } else if (e.originalEvent.deltaX < -30) {
        miraiedu_navigate_slider($(this), -1);
      }
      lastWheeled = now;
    }
  });

  $(document).ready(miraiflixSetup);
  $(window).resize(function () {
    // Only detect X axis resize
    var newW = $(window).width();
    if (newW != pageWidth) {
      miraiflixSetup();
      pageWidth = newW;
    }
  });
})(jQuery);
