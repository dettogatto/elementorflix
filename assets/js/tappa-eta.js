(function ($) {
  $(".read-more-btn").click(function (e) {
    e.preventDefault();
    if ($(this).hasClass("active")) {
      $(this).removeClass("active").html("Leggi tutto");
      $(this)
        .parent()
        .find("p")
        .css("-webkit-line-clamp", "3")
        .css("line-clamp", "3");
    } else {
      $(this).addClass("active").html("Chiudi");
      $(this)
        .parent()
        .find("p")
        .css("-webkit-line-clamp", "1000")
        .css("line-clamp", "1000");
    }
  });
})(jQuery);
