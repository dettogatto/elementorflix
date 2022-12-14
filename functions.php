<?php

// Remove admin bar for non admins
add_action('after_setup_theme', function () {
  if (!current_user_can('administrator') && !is_admin()) {
    show_admin_bar(false);
  }
});

function issetsetting($settings, $name)
{
  return isset($settings[$name]) && $settings[$name];
}

function miraiedu_get_widget_query_args($post_type, $settings)
{

  $meta_query = array('relation' => 'AND');
  $tax_query = array();
  $posts_per_page = 20;
  $page = 0;

  if (issetsetting($settings, 'posts_per_page')) {
    $posts_per_page = $settings['posts_per_page'];
  }


  if (issetsetting($settings, 'topics')) { // Topic filter
    $tax_query[] = array(
      'taxonomy' => 'filtri_temi',
      'field' => 'slug',
      'terms' => explode(',', $settings['topics'])
    );
  } elseif (issetsetting($settings, 'filter_search') && isset($_GET['filtri_temi'])) {
    $tax_query[] = array(
      'taxonomy' => 'filtri_temi',
      'field' => 'slug',
      'terms' => explode(',', $_GET['filtri_temi'])
    );
  }

  $childData = miraiedu_get_current_user_child_data();
  if (isset($settings['dynamic_age']) && $settings['dynamic_age']) { // User-custom age filter on child age
    $index = intval($settings['child_number']) - 1;
    if ($childData && isset($childData[$index]) && $childData[$index]['birth_date']) {
      $age = miraiedu_date_to_age($childData[$index]['birth_date']);
      $meta_query[] = array(
        'key' => 'age_max',
        'compare' => '>=',
        'value' => $age,
        'type' => 'DECIMAL(10, 2)'
      );
      $meta_query[] = array(
        'key' => 'age_min',
        'compare' => '<=',
        'value' => $age,
        'type' => 'DECIMAL(10, 2)'
      );
    } else {
      $post_type = 'none';
    }

  } else { // Normal widget age filter
    $min_age_filter = null;
    if ($settings['min_age'] || $settings['min_age'] === 0) { // Min age filter
      $min_age_filter = $settings['min_age'];
    } elseif ($settings['filter_search'] && isset($_GET['filtri_eta'])) {
      $min_age_filter = explode(",", $_GET['filtri_eta'])[0];
    }
    if ($min_age_filter == 0) {
      // If min age is 0 don't consider pre-natal
      $min_age_filter = 0.001;
    }
    if ($min_age_filter) {
      $meta_query[] = array(
        'key' => 'age_max',
        'compare' => '>=',
        'value' => $min_age_filter,
        'type' => 'DECIMAL(10, 3)'
      );
    }

    if (isset($settings['max_age']) && $settings['max_age']) { // Max age filter
      $meta_query[] = array(
        'key' => 'age_min',
        'compare' => '<',
        'value' => $settings['max_age'],
        'type' => 'DECIMAL(10, 2)'
      );
    } elseif (isset($settings['filter_search']) && $settings['filter_search'] && isset($_GET['filtri_eta'])) {
      $tmp = explode(",", $_GET['filtri_eta']);
      $max_age = isset($tmp[1]) ? $tmp[1] : 99;
      $meta_query[] = array(
        'key' => 'age_min',
        'compare' => '<',
        'value' => $max_age,
        'type' => 'DECIMAL(10, 2)'
      );
    }

  } // Normal widget age filter END

  $args = array(
    'post_type' => array($post_type),
    'orderby' => 'ASC',
    'tax_query' => $tax_query,
    'meta_query' => $meta_query,
    'posts_per_page' => $posts_per_page,
    'paged' => $page
  );

  if ($post_type == "professionisti") {
    $args = miraiedu_add_prof_filters_args($args);
  }

  if (isset($settings['filter_author']) && $settings['filter_author']) { // Author filter
    $args['author'] = get_the_author_meta('ID');
  }

  if (issetsetting($settings, 'filter_search') && isset($_GET['s'])) { // Search string
    $string = trim($_GET['s']);
    $string = strtolower($string);
    // Remove accents and weird stuff
    $string = preg_replace(
      '/&([a-z]{1,2})[^ ;]+;/i',
      '$1',
      htmlentities($string)
    );
    $patterns = array();
    $patterns[] = '/[^\w ]/'; // remove non-alphanumeric characters
    $patterns[] = '/\b\w{1,3}\b/'; // remove short words
    $patterns[] = '/\b(perche|quando|come|fare|faccio|facciamo|faremo)\b/'; // remove dumb words
    $patterns[] = '/\b(bambin.|figl.{1,2})\b/'; // remove words referring to kids
    $patterns[] = '/(a|o|essa|ore|rice|ista)\b/'; // remove gender endings
    $patterns[] = '/(re|mento)\b/'; // remove verb endings
    $string = preg_replace($patterns, '', $string);
    $args['s'] = $string;
  }

  return $args;

}

function miraiedu_add_prof_filters_args($args)
{
  $tax_query = $args['tax_query'];
  $meta_query = $args['meta_query'];
  $filters = [
    "filtri_professione",
    "filtri_provincia",
    "filtri_modalita",
    "filtri_genere"
  ];
  foreach ($filters as $filter) {
    if (isset($_GET[$filter])) {
      $tax_query[] = array(
        'taxonomy' => $filter,
        'field' => 'slug',
        'terms' => explode(',', $_GET[$filter])
      );
    }
  }
  $args['meta_key'] = 'valutazione';
  $args['orderby'] = 'meta_value title';
  $args['order'] = 'DESC';
  $args['tax_query'] = $tax_query;
  $args['meta_query'] = $meta_query;
  return $args;
}

function miraiedu_date_to_age($date)
{
  $birth = new DateTime($date);
  $now = new DateTime("now");
  $sign = ($birth < $now) ? 1 : -1;
  $interval = $birth->diff($now);
  $years = floatval($interval->y);
  $months = floatval($interval->m) / 100;
  return ($years + $months) * $sign;
}

function miraiedu_date_to_months($date)
{
  $birth = new DateTime($date);
  $now = new DateTime("now");
  $sign = ($birth < $now) ? 1 : -1;
  $interval = $birth->diff($now);
  $years = intval($interval->y);
  $months = intval($interval->m);
  return ($years * 12 + $months) * $sign;
}

function miraiedu_age_to_months($age)
{
  $age = floatval($age);
  $years = floor($age);
  $months = floor(($age - $years) * 100);
  return ($years * 12 + $months);
}

function miraiedu_get_current_user_child_data()
{
  $currentUser = get_current_user_id();
  if (!$currentUser) {
    return null;
  }
  $child_data = json_decode(get_user_meta($currentUser, 'miraiedu_child_data_json', true), true);
  if (is_array($child_data)) {
    usort($child_data, function ($a, $b) {
      $ta = strtotime($a['birth_date']);
      $tb = strtotime($b['birth_date']);
      return $tb - $ta;
    });
  }
  return $child_data;
}

add_action('wp_ajax_get_child_data', function () {
  $currentUser = get_current_user_id();
  if (!$currentUser) {
    wp_die(403);
  }
  echo (get_user_meta($currentUser, 'miraiedu_child_data_json', true));
  wp_die();
});

add_shortcode("miraiedu_nome_figlio", function ($atts) {
  $childN = 1;
  if (isset($atts["n"])) {
    $childN = intval($atts["n"]);
  }
  if ($childN > 0) {
    $childN--;
  } else {
    return "";
  }
  $childData = miraiedu_get_current_user_child_data();
  if (isset($childData[$childN]) && isset($childData[$childN]["name"])) {
    $return = $childData[$childN]["name"];
    if (isset($atts["before"])) {
      $return = $atts["before"] . '<span>' . $return . '</span>';
    }
    return $return;
  }
  return "";
});

add_shortcode("miraiedu_eta_figlio", function ($atts) {
  $childN = 1;
  if (isset($atts["n"])) {
    $childN = intval($atts["n"]);
  }
  if ($childN > 0) {
    $childN--;
  } else {
    return "";
  }
  $childData = miraiedu_get_current_user_child_data();
  if (isset($childData[$childN]) && isset($childData[$childN]["birth_date"])) {
    $return = miraiedu_date_to_age($childData[$childN]["birth_date"]);
    if (isset($atts["before"])) {
      $return = $atts["before"] . '<span>' . $return . '</span>';
    }
    return $return;
  }
  return "";
});

add_shortcode("miraiedu_querystring_figlio", function ($atts) {
  $childN = 1;
  if (isset($atts["n"])) {
    $childN = intval($atts["n"]);
  }
  if ($childN > 0) {
    $childN--;
  } else {
    return "";
  }
  $childData = miraiedu_get_current_user_child_data();
  if (isset($childData[$childN]) && isset($childData[$childN]["name"]) && isset($childData[$childN]["birth_date"])) {
    $age = miraiedu_date_to_age($childData[$childN]["birth_date"]);
    $return = "filtri_eta_nome=" . $childData[$childN]["name"];
    $return .= "&filtri_eta=" . $age;
    return $return;
  }
  return "";
});

add_shortcode("miraiedu_icons_single", function ($atts) {
  $field = $atts["field"];
  $contents = [];
  $icons = [];
  for ($i = 1; $i < 11; $i++) {
    $cont = get_field($field . $i);
    if ($cont && !empty($cont)) {
      $contents[] = $cont;
      if (!empty($atts["icon"])) {
        $icons[] = $atts["icon"];
      }
    }
  }
  return miraiedu_build_icon_list($contents, $icons);
});

add_shortcode("miraiedu_address", function ($atts) {
  return miraiedu_build_address($atts['num']);
});

add_shortcode("miraiedu_icons_and_coordinates", function ($atts) {
  $field_coo = "coordinate_";
  $contents = [];
  $icons = [];
  $links = [];
  for ($i = 1; $i < 2; $i++) {
    $addr = miraiedu_build_address($i);

    if ($addr) {
      $contents[] = $addr;
      $coo = str_replace(' ', '', get_field($field_coo . $i));
      if ($coo && strpos($coo, ',') !== false) {
        $links[] = '#' . $coo;
      } else {
        $links[] = "";
      }
      if (!empty($atts["icon"])) {
        $icons[] = $atts["icon"];
      }
    }
  }
  return miraiedu_build_icon_list($contents, $icons, $links);
});

add_shortcode("miraiedu_icons_group", function ($atts) {
  $field = $atts["field"];
  $contents = [];
  $icons = [];
  for ($i = 1; $i < 11; $i++) {
    $group = (array) get_field($field . $i);
    $icon = NULL;
    $cont = NULL;
    foreach ($group as $key => $value) {
      if (strpos($key, "icon") !== false) {
        $icon = $value;
      }
      if (strpos($key, "text") !== false || strpos($key, "testo") !== false || strpos($key, "content") !== false) {
        $cont = $value;
      }
    }
    if (!empty($cont)) {
      $contents[] = $cont;
      $icons[] = $icon;
    }
  }
  return miraiedu_build_icon_list($contents, $icons);
});

add_shortcode("miraiedu_professionista_contatti", function ($atts) {
  $icons = ["fas fa-phone-alt", "fas fa-envelope", "fas fa-location-arrow"];
  $contents = [get_field('telefono'), get_field('email'), get_field('posizione')];
  $links = [
    'tel:' . str_replace(" ", "", $contents[0]),
    'mailto:' . str_replace(" ", "", $contents[1]),
    ''
  ];
  return miraiedu_build_icon_list($contents, $icons, $links);
});

function miraiedu_build_icon_list($contents, $icons = [], $links = [])
{
  ob_start();
?>



<div
  class="elementor-element elementor-align-left elementor-icon-list--layout-traditional elementor-list-item-link-full_width elementor-widget elementor-widget-icon-list"
  data-element_type="widget" data-widget_type="icon-list.default">
  <div class="elementor-widget-container">
    <ul class="elementor-icon-list-items">

      <?php

  for ($i = 0; $i < count($contents); $i++) {

    $cont = $contents[$i];

    if (!empty($cont)) {
      $icon = (isset($icons[$i]) && !empty($icons[$i])) ? $icons[$i] : "far fa-thumbs-up";

      ?>

      <li class="elementor-icon-list-item">
        <?php
      if (isset($links[$i])) {
        echo ('<a ');
        if (!empty($links[$i])) {
          echo ('href="' . $links[$i] . '"');
        }
        echo ('>');
      }
        ?>
        <span class="elementor-icon-list-icon">
          <i aria-hidden="true" class="<?php echo ($icon); ?>"></i>
        </span>
        <span class="elementor-icon-list-text">
          <?php echo ($cont); ?>
        </span>
        <?php
      if (isset($links[$i])) {
        echo ('</a>');
      }
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

add_shortcode("miraiedu_professionista_js", function ($atts) {
  $video = [];

  for ($i = 1; $i < 7; $i++) {
    $group = get_field("video_" . $i);
    $img = NULL;
    $url = NULL;
    foreach ($group as $key => $value) {
      if (strpos($key, "im") !== false) {
        $img = $value;
      } elseif (strpos($key, "url") !== false || strpos($key, "lin") !== false) {
        $url = $value;
      }
    }
    if ($img && $url) {
      $video[] = ["img" => $img, "url" => $url];
    }
  }

  if (empty($video)) {
?>

<script>tion($) {
    $('.miraiedu-video-slider-container').hide()</script>

<?php
  } else {
?>

<script>function ($) {

      var video = < ? php echo (json_encode($video)); ?>;

      var $wrapper = $('.miraiedu-video-slider').find('.swiper-wrapper');
      var $slide = $wrapper.find('.swiper-slide').first().clone();
      $wrapper.html("");

      for (i = 0; i < video.length; i++) {
        $current = $slide.clone();
        $current.find("a").attr("data-elementor-lightbox-video", video[i]["url"]);
        $image = $current.find('.elementor-carousel-image');
        $image.css("background-image", "url(" + video[i]["img"] + ")");
        $image.removeClass('rocket-lazyload');
        $wrapper.append($current);
      }



    }) (jQuery);
</script>

<?php
  }

});

add_shortcode("miraiedu-link-professionista", function ($atts) {
  $post = miraiedu_get_current_prof_page();
  if ($post) {
    return get_permalink($post);
  }
  return "/esperti/";
});

add_shortcode("miraiedu-nome-professionista", function ($atts) {
  $post = miraiedu_get_current_prof_page();
  if ($post) {
    return get_post_meta($post->ID, 'nome_su_scheda', true);
  }
  return "No Name";
});

add_shortcode("miraiedu-titolo-professionista", function ($atts) {
  $post = miraiedu_get_current_prof_page();
  if ($post) {
    return get_post_meta($post->ID, 'titolo', true);
  }
  return "Dr.";
});

add_shortcode("miraiedu-valutazione-professionista", function ($atts) {
  $post = miraiedu_get_current_prof_page();
  if ($post) {
    return get_post_meta($post->ID, 'valutazione', true);
  }
  return "";
});

add_shortcode("miraiedu-professione-professionista", function ($atts) {
  $post = miraiedu_get_current_prof_page();
  if ($post) {
    return get_post_meta($post->ID, 'professione', true);
  }
  return "";
});

$current_prof_page;
function miraiedu_get_current_prof_page()
{
  global $wpdb, $current_prof_page;
  if (!$current_prof_page) {
    $args = [
      'author' => get_the_author_meta('ID'),
      'post_type' => 'professionisti',
      'posts_per_page' => 1
    ];
    $query = new WP_Query($args);
    if ($query->have_posts()) {
      $current_prof_page = $query->posts[0];
    }
  }
  return $current_prof_page;
}

function miraiedu_build_address($num)
{
  $addr = trim(get_field("indirizzo_mappa_" . $num));
  $city = trim(get_field("citta_mappa_" . $num));
  $prov = trim(get_field("provincia_mappa_" . $num));
  if (!$addr || !$city || !$prov) {
    return "";
  }
  $cont = miraiedu_titleize($city);
  $cont .= " (" . strtoupper($prov) . "), ";
  $addr = miraiedu_titleize($addr);
  $addr[0] = strtolower($addr[0]);
  $cont .= $addr;
  return $cont;
}

function miraiedu_titleize($string)
{
  $string = strtolower($string);
  $string = ucwords($string, " '-");
  $pattern = '/( \w+\')/i';
  $string = preg_replace_callback($pattern, function ($match) {
    return strtolower($match[1]);
  }, $string);
  return $string;
}


/* Make Wp Seach engine search in meta tags as well */

// add_filter('posts_join', function ($join) {
//   global $wpdb;
//   if (is_search()) {
//     $join .= ' LEFT JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
//   }
//   return $join;
// });

// add_filter('posts_where', function ($where) {
//   global $pagenow, $wpdb;
//   if (is_search()) {
//     $where = preg_replace(
//       "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
//       "(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1)",
//       $where
//     );
//   }
//   return $where;
// });

// add_filter(
//   'posts_distinct',
//   function ($where) {
//     global $wpdb;
//     if (is_search()) {
//       return "DISTINCT";
//     }
//     return $where;
//   }
// );