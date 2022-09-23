<?php
/*
Plugin Name: MiraiEdu - Elementorflix
Plugin URI: https://cosmo.cat
Description: Shortcode per le icone professionisti
Version: 0.0.2
Author: Nicola
Author URI: https://cosmo.cat
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
  echo 'Hi dear :)';
  exit;
}

require_once( __DIR__ . '/functions.php' );

add_action( 'elementor/query/posts_same_author', function( $query ) {
  // Pull your author dynamically however you would
  $author_id = get_the_author_ID();
  $query->set( 'author', $author_id );
  return $query;
} );

if(is_admin()){
  add_action( 'admin_enqueue_scripts', function(){
    wp_enqueue_script( 'miraiedu-geocoding', plugins_url( '/assets/js/geocoding.js', __FILE__ ), array( 'jquery' ) );
  } );
}

add_action( 'wp_enqueue_scripts', function(){
  wp_enqueue_script( 'mir-hidenseek-js', plugins_url( '/assets/js/hidenseek.js', __FILE__ ), array( 'jquery' ) );
  wp_enqueue_script( 'miraiedu-map-js', plugins_url( '/assets/js/frontend-map.js', __FILE__ ), array( 'jquery' ) );
  // wp_enqueue_script( 'miraiflix-js', plugins_url( '/assets/js/miraiflix.js', __FILE__ ), array( 'jquery' ) );
  // wp_enqueue_style( 'miraiflix-css', plugins_url( '/assets/css/miraiflix.css', __FILE__ ) );
} );


/* WIDGETS */

add_action( 'elementor/elements/categories_registered', function($elements_manager){
  $elements_manager->add_category(
      'miraiedu',
      [
        'title' => 'MiraiEdu',
        'icon' => 'fa fa-plug'
      ]
    );
} );


add_action( 'elementor/widgets/widgets_registered', function(){
  require_once( __DIR__ . '/widgets/video-slider.php' );
  require_once( __DIR__ . '/widgets/post-slider.php' );
  require_once( __DIR__ . '/widgets/prof-slider.php' );
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MiraiflixVideoSlider() );
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MiraiflixPostSlider() );
  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new MiraiflixProfSlider() );
} );

add_action( 'elementor_pro/init', function(){
  // Here its safe to include our action class file
  include_once( __DIR__ . '/form-actions/user-login.php' );

  // // Instantiate the action class
  $miraiedu_login = new Elementor_Miraiedu_Login();

  // Register the action with form widget
  \ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $miraiedu_login->get_name(), $miraiedu_login );

} );