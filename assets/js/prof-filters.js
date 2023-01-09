(function ($) {
  $(function () {
    // Handle hide/show sub-menus
    $(".miraiedu-filter-button").click(function () {
      var $parent = $(this).parent();
      var $mycontent = $(".miraiedu-filter-content", $parent);
      var wasVisible = $mycontent.is(":visible");
      $(".miraiedu-filter .miraiedu-filter-content").fadeOut("fast");
      $(".miraiedu-filter").removeClass("open");
      if (!wasVisible) {
        $parent.addClass("open");
        $(".miraiedu-filter-content", $(this).parent()).fadeIn("fast");
      }
    });

    // Hide sub-menus when page is clicked
    $("html").click(function (e) {
      if ($(e.target).parents(".miraiedu-filters-container").length == 0) {
        $(".miraiedu-filter").removeClass("open");
        $(".miraiedu-filter .miraiedu-filter-content").fadeOut("fast");
      }
    });

    // Reset button
    $(".miraiedu-filter.reset-filters").click(function () {
      var resetUrl = $(this)
        .closest(".miraiedu-filters-container")
        .attr("data-reset-url");
      var url;
      if (resetUrl) {
        url = new URL(
          $(this).closest(".miraiedu-filters-container").attr("data-reset-url"),
          window.location
        );
      } else {
        url = new URL(window.location.href);
      }
      url.search = "";
      window.location = url.href;
    });

    // Change links in buttons
    $(".miraiedu-filters-container li a").attr("href", function (index, value) {
      var baseUrl = new URL(
        $(this).closest(".miraiedu-filters-container").attr("data-url"),
        window.location
      );
      var url = new URL(window.location.href);
      const urlParams = url.searchParams;
      const tax = $(this).attr("data-tax");
      const slug = $(this).attr("data-slug");
      if (urlParams.get(tax) === slug) {
        urlParams.delete(tax);
      } else {
        urlParams.set(tax, slug);
      }
      baseUrl.search = urlParams.toString();
      return baseUrl.href;
    });
  });
})(jQuery);
