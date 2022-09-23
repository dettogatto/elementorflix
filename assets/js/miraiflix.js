(function($){

  var averageSlideWidth = 270;
  var slidesMargin = 0;

  function miraiflixSetup(){
    $('.miraiflix-container').each(function(){
      var totWidth = $(this).width();
      var $slides = $(this).find('.miraiflix-slide-container');
      slidesMargin = parseInt($slides.css('marginLeft'));
      var slidesCount = Math.min(Math.max(1, Math.round(totWidth/averageSlideWidth)), 6);
      var availableSpace = totWidth;
      var slidesWidth = (availableSpace / slidesCount)-(slidesMargin*2);
      var totPages = Math.ceil($slides.length/slidesCount);
      $slides.width(slidesWidth);
      $(this).attr('data-n-slides', slidesCount);
      $(this).attr('data-slides-width', slidesWidth);
      $(this).attr('data-page', 0);
      $(this).attr('data-n-pages', totPages);
      hideShowNavigation($(this));
      setSlidesClasses($(this));
    });
    $('.miraiflix-inner-container').css('margin-left', '0px');
  }

  function setSlidesClasses($container){
    var $slides = $container.find('.miraiflix-slide-container');
    var page = parseInt($container.attr('data-page'));
    var slidesCount = parseInt($container.attr('data-n-slides'));
    $slides.removeClass('first');
    $slides.removeClass('last');
    var count = 0;
    $slides.each(function(){
      if(slidesCount * page === count){
        $(this).addClass('first');
      } else if(slidesCount * page + slidesCount - 1 === count){
        $(this).addClass('last');
      }
      count ++;
    });
  }

  function hideShowNavigation($container){
    var page = parseInt($container.attr('data-page'));
    var nPages = parseInt($container.attr('data-n-pages'));
    if(page <= 0){
      $container.find('.miraiflix-navigation-left').fadeOut();
    } else {
      $container.find('.miraiflix-navigation-left').fadeIn();
    }
    if((page + 1) >= nPages){
      $container.find('.miraiflix-navigation-right').fadeOut();
    } else {
      $container.find('.miraiflix-navigation-right').fadeIn();
    }
  }

  $(document).on('click', '.miraiflix-navigation-right, .miraiflix-navigation-left', function(e){
    var $container = $(this).closest('.miraiflix-container');
    var $slider = $container.find('.miraiflix-inner-container');
    var slidesWidth = parseFloat($container.attr('data-slides-width'));
    var slidesCount = parseInt($container.attr('data-n-slides'));
    var currentPage = parseInt($container.attr('data-page'));
    var totPages = parseInt($container.attr('data-n-pages'));
    if($(this).hasClass('miraiflix-navigation-right')){
      var newPage = parseInt(currentPage) + 1;
    } else {
      var newPage = parseInt(currentPage) - 1;
    }
    if(newPage < 0 || newPage >= totPages){
      return;
    }
    $container.attr('data-page', newPage);
    var newMargin = newPage * (slidesWidth + slidesMargin*2) * slidesCount;
    $slider.css('margin-left', '-' + newMargin + 'px');
    hideShowNavigation($container);
    setSlidesClasses($container);
  });

  $(document).ready(miraiflixSetup);
  $(window).resize(miraiflixSetup);

})(jQuery);
