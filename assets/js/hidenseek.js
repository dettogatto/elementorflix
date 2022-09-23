(function($){

  function HideNSeekUpdateVisual(){
    $('.hidenseek-link-container .elementor-column-wrap').each(function(){
      if($(this).find('.hidenseek-active').length){
        $(this).css('border-color', '#23DB8B');
      } else {
        $(this).css('border-color', '#F1EFF2');
      }
    });

    $('.hidenseek-element').each(function(){
      if($(this).hasClass('hidenseek-active')){
        $(this).fadeIn();
      } else {
        $(this).hide();
      }
    });
  }

  $(document).ready(function(){

    HideNSeekUpdateVisual();

    $('.hidenseek-link').click(function(e){
      e.preventDefault();
      $('.hidenseek-link').removeClass('hidenseek-active');
      $(this).addClass('hidenseek-active');
      $('.hidenseek-element').removeClass('hidenseek-active');
      var showClass = $(this).attr('data-hidenseek');
      $('.hidenseek-element.hidenseek-' + showClass).addClass('hidenseek-active');
      HideNSeekUpdateVisual();
    });


  });
})(jQuery);
