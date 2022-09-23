(function($){
  $(document).ready(function(){

    var map;
    var markers = [];
    var $map = $('#miraiedu-map-canvas');
    if(!$map || !$map.length){
      return;
    }
    console.log("Mappa trovata");

    $.getScript("https://maps.google.com/maps/api/js?key=AIzaSyBvxnSVqDIJ9qDtVk0ZNxzjAI2ZWoiZrdM&libraries=places", function(data, textStatus, jqxhr) {
      console.log("LTM Loaded");
      initMap();
    });

    function initMap() {
      var myLatLng = { lat: 0, lng: 0 };
      map = new google.maps.Map($map.find('.elementor-widget-wrap')[0], {
        zoom: 10,
        center: myLatLng,
      });
      loadMarkers();
    }

    function loadMarkers(){
      var params = new URLSearchParams(window.location.search);
      var currentProvincia = params.get('_professionista_provincia');
      var panned = false;
      var pannedToProvincia = !currentProvincia;
      clearMarkers();
      $('.miraiedu-addresses a').each(function(){
        var chref = $(this).attr('href');
        if(!chref || chref.length < 4){
          return;
        }
        chref = chref.replace('#', '');
        var coo = str2coo(chref);
        addMarker(coo);
        if( !pannedToProvincia ){
          var html = $(this).find('.elementor-icon-list-text').html();
          if( html.toLowerCase().includes('(' + currentProvincia + ')') ){
            map.panTo(coo);
            panned = true;
            pannedToProvincia = true;
          }
        }
        if( !panned ){
          map.panTo(coo);
          panned = true;
        }
        $(this).click(function(e){
          e.preventDefault();
          map.panTo(coo);
        });
      });
    }

    function addMarker(coo, title = ""){
      markers.push(
        new google.maps.Marker({
          position: coo,
          map,
          title: title,
        })
      );
    }

    function clearMarkers(){
      for(var marker of markers){
        marker.setMap(null);
      }
      markers.length = 0;
    }


    function str2coo(string){
      var val = string.split(',');
      var lat = parseFloat(val[0].trim());
      var lng = parseFloat(val[1].trim());
      var ret = { lat: lat, lng: lng };
      return ret;
    }

    document.addEventListener('facetwp-loaded', function() {
      if(typeof google !== 'undefined'){
        setTimeout(loadMarkers, 100);
      }
    });

  });
})(jQuery);
