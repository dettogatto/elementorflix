(function($){

  $(document).ready(function(){

    $coos = $('.acf-field[data-name^="coordinate"]');
    if(!$coos || $coos.length < 1){
      return;
    }

    $.getScript("https://maps.google.com/maps/api/js?key=AIzaSyBvxnSVqDIJ9qDtVk0ZNxzjAI2ZWoiZrdM&libraries=places", function(data, textStatus, jqxhr) {
      console.log("LTM Loaded");
      init();
    });

    function init(){

      geocoder = new google.maps.Geocoder();

      $coos.each(function(){
        var $coo_container = $(this);
        var $coo_field = $(this).find('input');

        var addressElements = [];
        for(var w of ['indirizzo_mappa', 'citta_mappa', 'provincia_mappa']){
          var add_name = $coo_container.attr('data-name').replace('coordinate', w);
          var $add_container = $('.acf-field[data-name="'+add_name+'"]');
          var $add_field = $add_container.find('input');
          addressElements.push($add_field);
        }

        var $btn = $('<a style="float: left; margin-right: 4px;" class="button acf-button">geocode</a>');
        $coo_container.find('.acf-input').prepend($btn);
        $btn.click(function(){
          $coo_field.val("");
          var fullAddress = [];
          for(e of addressElements){
            fullAddress.push(e.val());
          }
          console.log('geocoding address: ' + fullAddress.join(", "));
          var request = fullAddress.join(", ");
          if(request && request.length > 0){
            geocoder.geocode({address: request}, function(result){
              if(result && result.length){
                var lat = result[0].geometry.location.lat();
                var lng = result[0].geometry.location.lng();
                $coo_field.val(lat + ', ' + lng);
              } else {
                $coo_field.val('error');
              }
            });
          }
        });
      });
    }

  });

})(jQuery);
