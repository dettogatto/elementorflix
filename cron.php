<?php

add_action('miraiedu_cron_activecampaign_sync', function () {
  $per_page = 1;
  $page = 1;
  $users = null;
  $updated_users = 0;
  do {
    $args = array(
      'role' => 'subscriber',
      'number' => $per_page,
      'offset' => ($page - 1) * $per_page,
      'paged' => $page
    );
    $users = get_users($args);

    foreach ($users as $user) {
      $kids_json = get_user_meta($user->ID, 'miraiedu_child_data_json', true);
      $kids = json_decode($kids_json, true);
      $new_kids = miraiedu_child_data_calculate_ages($kids);
      if ($new_kids != $kids) {
        $resp = miraiedu_ac_sync_user($user->ID);
        if ($resp) {
          $updated_users++;
          update_user_meta($user->ID, 'miraiedu_child_data_json', json_encode($new_kids, JSON_PRETTY_PRINT));
        }
      }
    }
    $page++;
  } while (!empty($users));
  echo ('updated users: ' . $updated_users);
});

// Make ac_sync available from admin-ajax.php?action=ac_sync_all
add_action('wp_ajax_ac_sync_all', function () {
  do_action('miraiedu_cron_activecampaign_sync');
  wp_die();
});


function miraiedu_child_data_calculate_ages($child_data)
{
  return array_map(
    function ($el) {
      $el['calculated_age'] = miraiedu_date_to_age($el['birth_date']);
      return $el;
    }
    ,
    $child_data
  );
}

function miraiedu_ac_sync_user($user_id, $new_user = false)
{
  require_once(__DIR__ . '/api/ac-v3.php');
  $data = miraiedu_ac_get_user_sync_data($user_id, $new_user);
  if ($data) {
    $ac = new ActiveCampaign_API_Miraiedu();
    $res = $ac->super_sync_contact($data['contact'], $data['lists'], $data['tags'], $data['fields']);
    if ($res) {
      return true;
    }
  }
  return false;
}

function miraiedu_ac_set_user_tmp_code($user_id, $code)
{
  require_once(__DIR__ . '/api/ac-v3.php');
  $user = get_userdata($user_id);
  $data = [
    "contact" => [
      "email" => $user->user_email
    ],
    "fields" => [
      26 => $code
    ]
  ];
  if ($data) {
    $ac = new ActiveCampaign_API_Miraiedu();
    $res = $ac->super_sync_contact($data['contact'], NULL, NULL, $data['fields']);
    if ($res) {
      return true;
    }
  }
  return false;
}

function miraiedu_ac_get_user_sync_data($user_id, $new_user = false)
{
  $user = get_userdata($user_id);
  if (!$user) {
    return null;
  }
  $contact = [
    'email' => $user->user_email,
    'fullName' => $user->display_name,
  ];
  $child_data = json_decode(get_user_meta($user_id, 'miraiedu_child_data_json', true), true);

  $fields = array();
  $tags = array();

  if (!empty($child_data)) {
    $child = array_shift($child_data);
    $fields[14] = $child["birth_date"]; // BDay kid #1
    $fields[15] = $child["gender"]; // Gender kid #1
    $fields[16] = $child["name"]; // Name kid #1
    $fields[17] = $child["role"]; // Role
    $fields[13] = miraiedu_date_to_age($child["birth_date"]); // Age kid #1
  }
  if (!empty($child_data)) {
    $child = array_shift($child_data);
    $fields[20] = $child["birth_date"]; // BDay kid #2
    $fields[18] = $child["gender"]; // Gender kid #2
    $fields[19] = $child["name"]; // Name kid #2
    $fields[24] = miraiedu_date_to_age($child["birth_date"]); // Age kid #2
  }
  if (!empty($child_data)) {
    $child = array_shift($child_data);
    $fields[23] = $child["birth_date"]; // BDay kid #3
    $fields[22] = $child["gender"]; // Gender kid #3
    $fields[21] = $child["name"]; // Name kid #3
    $fields[25] = miraiedu_date_to_age($child["birth_date"]); // Age kid #3
  }

  if ($new_user) {
    $tags[] = 41; // AZIONE: Sign up Parentube 2.0
  }

  return [
    'contact' => $contact,
    // UTENTI REGISTRATI SITO
    'lists' => [19],
    'fields' => $fields,
    'tags' => $tags
  ];
}