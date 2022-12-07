(function ($) {
  $(function () {
    // Handle hide/show sub-menus
    $(".miraiedu-filter-button").click(function () {
      var $mycontent = $(".miraiedu-filter-content", $(this).parent());
      var wasVisible = $mycontent.is(":visible");
      $(".miraiedu-filter .miraiedu-filter-content").fadeOut("fast");
      if (!wasVisible) {
        $(".miraiedu-filter-content", $(this).parent()).fadeIn("fast");
      }
    });

    // Hide sub-menus when page is clicked
    $("html").click(function (e) {
      if ($(e.target).parents(".miraiedu-filters-container").length == 0) {
        $(".miraiedu-filter .miraiedu-filter-content").fadeOut("fast");
      }
    });

    // Change links in buttons
    $(".miraiedu-filters-container li a").attr("href", function (index, value) {
      var baseUrl = new URL(
        $(this).closest(".miraiedu-filters-container").attr("data-url"),
        window.location
      );
      var url = new URL(window.location.href);
      const urlParams = url.searchParams;
      urlParams.set($(this).attr("data-tax"), $(this).attr("data-slug"));
      baseUrl.search = urlParams.toString();
      return baseUrl.href;
    });
  });
})(jQuery);
