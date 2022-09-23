<?php

function miraiedu_get_widget_query_args($post_type, $settings){

  $meta_query = array('relation' => 'AND');
  $tax_query = array();
  $posts_per_page = 20;
  $page = 0;

  if($settings['posts_per_page']){
    $posts_per_page = $settings['posts_per_page'];
  }


  if($settings['topics']){ // Topic filter
    $tax_query[] = array(
      'taxonomy' => 'filtri_temi',
      'field' => 'slug',
      'terms' => explode(',', $settings['topics'])
    );
  } elseif($settings['filter_search'] && isset($_GET['temi'])){
    $tax_query[] = array(
      'taxonomy' => 'filtri_temi',
      'field' => 'slug',
      'terms' => explode(',', $_GET['temi'])
    );
  }

  if($settings['min_age'] || $settings['min_age'] === 0){ // Min age filter
    $meta_query[] = array(
      'key' => 'age_max',
      'compare' => '>=',
      'value' => $settings['min_age'],
      'type' => 'NUMERIC'
    );
  } elseif($settings['filter_search'] && isset($_GET['eta-min'])){
    $meta_query[] = array(
      'key' => 'age_max',
      'compare' => '>=',
      'value' => $_GET['eta-min'],
      'type' => 'NUMERIC'
    );
  }

  if($settings['max_age']){ // Max age filter
    $meta_query[] = array(
      'key' => 'age_min',
      'compare' => '<=',
      'value' => $settings['max_age'],
      'type' => 'NUMERIC'
    );
  } elseif($settings['filter_search'] && isset($_GET['eta-max'])){
    $meta_query[] = array(
      'key' => 'age_max',
      'compare' => '>=',
      'value' => $_GET['eta-max'],
      'type' => 'NUMERIC'
    );
  }

  $args = array(
    'post_type' => array( $post_type ),
    'orderby' => 'ASC',
    'tax_query' => $tax_query,
    'meta_query' => $meta_query,
    'posts_per_page' => $posts_per_page,
    'paged' => $page
  );

  if(isset($settings['filter_author']) && $settings['filter_author']){ // Author filter
    $args['author'] = get_the_author_meta('ID');
  }

  if($settings['filter_search'] && isset($_GET['s'])){ // Search string
    $args['s'] = $_GET['s'];
  }

  return $args;

}

add_shortcode( "miraiedu_icons_single", function($atts){
  $field = $atts["field"];
  $contents = [];
  $icons=[];
  for($i = 1; $i < 11; $i++){
    $cont = get_field($field . $i);
    if($cont && !empty($cont)){
      $contents[] = $cont;
      if(!empty($atts["icon"])){
        $icons[] = $atts["icon"];
      }
    }
  }
  return miraiedu_build_icon_list($contents, $icons);
} );

add_shortcode("miraiedu_address", function($atts){
  return miraiedu_build_address($atts['num']);
});

add_shortcode( "miraiedu_icons_and_coordinates", function($atts){
  $field_coo = "coordinate_";
  $contents = [];
  $icons = [];
  $links = [];
  for($i = 1; $i < 11; $i++){
    $cont = get_field($field . $i);

    $addr = miraiedu_build_address($i);

    if($addr){
      $contents[] = $addr;
      $coo = str_replace(' ','', get_field($field_coo . $i));
      if($coo && strpos($coo, ',') !== false){
        $links[] = '#' . $coo;
      } else {
        $links[] = "";
      }
      if(!empty($atts["icon"])){
        $icons[] = $atts["icon"];
      }
    }
  }
  return miraiedu_build_icon_list($contents, $icons, $links);
} );

add_shortcode( "miraiedu_icons_group", function($atts){
  $field = $atts["field"];
  $contents = [];
  $icons = [];
  for($i = 1; $i < 11; $i++){
    $group = (array) get_field($field . $i);
    $icon = NULL;
    $cont = NULL;
    foreach ($group as $key => $value) {
      if(strpos($key, "icon") !== false){$icon = $value;}
      if(strpos($key, "text") !== false || strpos($key, "testo") !== false || strpos($key, "content") !== false){$cont = $value;}
    }
    if(!empty($cont)){
      $contents[] = $cont;
      $icons[] = $icon;
    }
  }
  return miraiedu_build_icon_list($contents, $icons);
} );

add_shortcode( "miraiedu_professionista_contatti", function($atts){
  $icons = ["fas fa-phone-alt", "fas fa-envelope", "fas fa-location-arrow"];
  $contents = [get_field('telefono'), get_field('email'), get_field('posizione')];
  $links = [
    'tel:' . str_replace(" ", "", $contents[0]),
    'mailto:' . str_replace(" ", "", $contents[1]),
    ''
  ];
  return miraiedu_build_icon_list($contents, $icons, $links);
});

function miraiedu_build_icon_list($contents, $icons = [], $links = []){
  ob_start();
  ?>



  <div class="elementor-element elementor-align-left elementor-icon-list--layout-traditional elementor-list-item-link-full_width elementor-widget elementor-widget-icon-list" data-element_type="widget" data-widget_type="icon-list.default">
    <div class="elementor-widget-container">
      <ul class="elementor-icon-list-items">

        <?php

        for($i=0; $i < count($contents); $i++){

          $cont = $contents[$i];

          if(!empty($cont)){
            $icon = (isset($icons[$i]) && !empty($icons[$i])) ? $icons[$i] : "far fa-thumbs-up";

            ?>

            <li class="elementor-icon-list-item">
              <?php
              if(isset($links[$i])){
                echo('<a ');
                if(!empty($links[$i])){
                  echo('href="'.$links[$i].'"');
                }
                echo('>');
              }
              ?>
              <span class="elementor-icon-list-icon">
                <i aria-hidden="true" class="<?php echo($icon); ?>"></i>
              </span>
              <span class="elementor-icon-list-text"><?php echo($cont); ?></span>
              <?php
              if(isset($links[$i])){echo('</a>');}
              ?>

            </li>

            <?php

          }


        }

        ?>
      </ul>
    </div>
  </div>

  <?php

  $ret = ob_get_clean();
  return $ret;
}

add_shortcode( "miraiedu_professionista_js", function($atts){
  $video = [];

  for($i = 1; $i < 7; $i++){
    $group = get_field("video_". $i);
    $img = NULL;
    $url = NULL;
    foreach ($group as $key => $value) {
      if(strpos($key, "im") !== false){
        $img = $value;
      } elseif(strpos($key, "url") !== false || strpos($key, "lin") !== false){
        $url = $value;
      }
    }
    if($img && $url){
      $video[] = ["img" => $img, "url" => $url];
    }
  }

  if(empty($video)){
    ?>

    <script>
    (function($){
      $('.miraiedu-video-slider-container').hide();
    })(jQuery);
    </script>

    <?php
  } else {
    ?>

    <script>
    (function($){

      var video = <?php echo(json_encode($video)); ?>;

      var $wrapper = $('.miraiedu-video-slider').find('.swiper-wrapper');
      var $slide = $wrapper.find('.swiper-slide').first().clone();
      $wrapper.html("");

      for(i = 0; i < video.length; i++){
        $current = $slide.clone();
        $current.find("a").attr("data-elementor-lightbox-video", video[i]["url"]);
        $image = $current.find('.elementor-carousel-image');
        $image.css("background-image", "url(" + video[i]["img"] + ")");
        $image.removeClass('rocket-lazyload');
        $wrapper.append($current);
      }



    })(jQuery);
    </script>

    <?php
  }

});

function miraiedu_build_address($num){
  $addr = trim(get_field("indirizzo_mappa_" . $num));
  $city = trim(get_field("citta_mappa_" . $num));
  $prov = trim(get_field("provincia_mappa_" . $num));
  if(!$addr || !$city || !$prov){
    return "";
  }
  $cont = miraiedu_titleize($city);
  $cont .= " (" . strtoupper($prov) . "), ";
  $addr = miraiedu_titleize($addr);
  $addr[0] = strtolower($addr[0]);
  $cont .= $addr;
  return $cont;
}

function miraiedu_titleize($string){
  $string = strtolower($string);
  $string = ucwords($string, " '-");
  $pattern = '/( \w+\')/i';
  $string = preg_replace_callback($pattern, function($match){
    return strtolower($match[1]);
  }, $string);
  return $string;
}
