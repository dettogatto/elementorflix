<?php
/*
Plugin Name: MiraiEdu - Elementorflix
Plugin URI: https://cosmo.cat
Description: Shortcode per le icone professionisti
Version: 0.0.3
Author: Nicola
Author URI: https://cosmo.cat
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
  echo 'Hi dear :)';
  exit;
}

require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/shortcodes.php');
require_once(__DIR__ . '/cron.php');

add_action('elementor/query/posts_same_author', function ($query) {
  // Pull your author dynamically however you would
  $author_id = get_the_author_ID();
  $query->set('author', $author_id);
  return $query;
});

if (is_admin()) {
  add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script('miraiedu-geocoding', plugins_url('/assets/js/geocoding.js', __FILE__), array('jquery'));
  });
}

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_script('miraiedu-map-js', plugins_url('/assets/js/frontend-map.js', __FILE__), array('jquery'));
  wp_enqueue_script('miraiflix-js', plugins_url('/assets/js/miraiflix.js', __FILE__), array('jquery'), '0.0.2');
  wp_enqueue_style('miraiflix-css', plugins_url('/assets/css/miraiflix.css', __FILE__), [], '0.0.3');
});


/* WIDGETS */

add_action('elementor/elements/categories_registered', function ($elements_manager) {
  $elements_manager->add_category(
    'miraiedu',
    [
      'title' => 'MiraiEdu',
      'icon' => 'fa fa-plug'
    ]
  );
});


add_action('elementor/widgets/widgets_registered', function () {
  require_once(__DIR__ . '/widgets/video-slider.php');
  require_once(__DIR__ . '/widgets/video-grid.php');
  require_once(__DIR__ . '/widgets/post-slider.php');
  require_once(__DIR__ . '/widgets/post-grid.php');
  require_once(__DIR__ . '/widgets/prof-slider.php');
  require_once(__DIR__ . '/widgets/prof-grid.php');
  require_once(__DIR__ . '/widgets/tappa-eta.php');
  require_once(__DIR__ . '/widgets/prof-filters.php');
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new MiraiflixVideoSlider());
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new MiraiflixVideoGrid());
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new MiraiflixPostSlider());
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new MiraiflixPostGrid());
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new MiraiflixProfSlider());
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new MiraiflixProfGrid());
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new MiraieduTappaEta());
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new MiraiflixProfFilters());
});

add_action('elementor_pro/init', function () {
  // Here its safe to include our action class file
  include_once(__DIR__ . '/form-actions/user-login.php');
  include_once(__DIR__ . '/form-actions/user-register.php');
  include_once(__DIR__ . '/form-actions/user-edit-child-data.php');
  include_once(__DIR__ . '/form-actions/user-reset-password-1.php');
  include_once(__DIR__ . '/form-actions/user-reset-password-2.php');

  // // Instantiate the action class
  $miraiedu_login = new Elementor_Miraiedu_Login();
  $miraiedu_register = new Elementor_Miraiedu_Register();
  $miraiedu_edit_child = new Elementor_Miraiedu_Edit_Child_Data();
  $miraiedu_reset_psw_1 = new Elementor_Miraiedu_Reset_Psw_1();
  $miraiedu_reset_psw_2 = new Elementor_Miraiedu_Reset_Psw_2();

  // Register the action with form widget
  \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($miraiedu_login->get_name(), $miraiedu_login);
  \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($miraiedu_register->get_name(), $miraiedu_register);
  \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($miraiedu_edit_child->get_name(), $miraiedu_edit_child);
  \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($miraiedu_reset_psw_1->get_name(), $miraiedu_reset_psw_1);
  \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms')->add_form_action($miraiedu_reset_psw_2->get_name(), $miraiedu_reset_psw_2);

});