<?php

add_shortcode("miraiedu_mixpanel_identify_js", function () {

  $child_data = miraiedu_get_current_user_child_data();
  $current_user = wp_get_current_user();
  $user_data = [
    'name' => $current_user->display_name,
    'email' => $current_user->user_email,
    'n_childs' => count($child_data)
  ];
  $stato = "in attesa";
  for ($i = 0; $i < 3; $i++) {
    if (isset($child_data[$i])) {
      $months = miraiedu_date_to_months($child_data[$i]["birth_date"]);
      if ($months > 0) {
        $stato = "già genitore";
      }
      $user_data["age_child_" . ($i + 1)] = $months;
      $user_data["gender_child_" . ($i + 1)] = $child_data[$i]["gender"];
    }
  }

  ob_start();
?>
<script>
  window.user_data = <?php echo (json_encode($user_data)); ?>;
</script>
<?php
  $result = ob_get_clean();
  return $result;
});


add_shortcode("miraiedu_mixpanel_content_js", function () {
  $post_type = get_post_type();
  $tags = [];
  foreach (get_the_terms(get_the_ID(), "filtri_temi") as $tag) {
    $tags[] = $tag->name;
  }

  $content_data = [
    'title' => get_the_title(),
    'type' => $post_type,
    'min_age_months' => miraiedu_age_to_months(get_field("age_min")),
    'max_age_months' => miraiedu_age_to_months(get_field("age_max")),
    'tags' => $tags
  ];
  ob_start();
?>
<script>
  window.content_data = <?php echo (json_encode($content_data)); ?>;
</script>
<?php
  $result = ob_get_clean();
  return $result;
});