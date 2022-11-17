<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use ElementorPro\Modules\QueryControl\Module as Module_Query;
use ElementorPro\Modules\QueryControl\Controls\Group_Control_Related;


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
* Elementor Form Poster
*
* Elementor widget for Form Poster.
*/
class MiraieduTappaEta extends Widget_Base {

  /**
  * Retrieve the widget name.
  *
  * @access public
  *
  * @return string Widget name.
  */
  public function get_name() {
    return 'tappa-eta';
  }

  /**
  * Retrieve the widget title.
  *
  * @access public
  *
  * @return string Widget title.
  */
  public function get_title() {
    return 'Tappa età';
  }

  /**
  * Retrieve the widget icon.
  *
  * @access public
  *
  * @return string Widget icon.
  */
  public function get_icon() {
    return 'eicon-code';
  }

  /**
  * Retrieve the list of categories the widget belongs to.
  *
  * Used to determine where to display the widget in the editor.
  *
  * Note that currently Elementor supports only one category.
  * When multiple categories passed, Elementor uses the first one.
  *
  * @access public
  *
  * @return array Widget categories.
  */
  public function get_categories() {
    return [ 'miraiedu' ];
  }

  /**
  * Retrieve the list of scripts the widget depended on.
  *
  * Used to set scripts dependencies required to run the widget.
  *
  * @access public
  *
  * @return array Widget scripts dependencies.
  */
  public function get_script_depends() {
    return [ 'miraiflix-js' ];
  }

  public function get_style_depends() {
    return [ 'miraiflix-css' ];
  }

  /**
  * Constructor
  */

  public function __construct($data = [], $args = null) {
    parent::__construct($data, $args);

    wp_register_script( 'miraiflix-js', plugins_url( '/assets/js/miraiflix.js', __DIR__ ), array( 'jquery' ) );
    wp_register_style( 'miraiflix-css', plugins_url( '/assets/css/miraiflix.css', __DIR__ ) );
  }


  /**
  * Register the widget controls.
  *
  * Adds different input fields to allow the user to change and customize the widget settings.
  *
  * @access protected
  */
  protected function register_controls() {
    $this->start_controls_section(
      'section_query',
      [
        'label' => esc_html__( 'Filters', 'elementor-pro' ),
        'tab' => Controls_Manager::TAB_CONTENT,
      ]
    );

    $this->add_control(
      'child_number',
      [
        'label' => 'Numero del figlio',
        'type' => Controls_Manager::NUMBER,
        'min' => '1',
        'max' => '20',
        'step' => '1',
        'default' => '1',
        'description' => "Il numero del figlio su cui basare il filtro età. Se il figlio non esiste per l'utente corrente non verrà mostrato nulla."
      ]
    );

    $this->end_controls_section();


    $this->start_controls_section(
      'section_style',
      [
        'label' => 'Style',
        'tab' => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_group_control(
      'typography',
      [
        'Label' => 'Caption Typo',
        'name' => 'typography_filter',
        'selector' => '{{WRAPPER}} .miraiflix-fallback-text',
      ]
    );

    $this->end_controls_section();



  }

  /**
  * Render the widget output on the frontend.
  *
  * Written in PHP and used to generate the final HTML.
  *
  * @access protected
  */
  protected function render() {
    $settings = $this->get_settings_for_display();
    $childData = miraiedu_get_current_user_child_data();
    $childIndex = $settings['child_number'] - 1;

    $settings['dynamic_age'] = 'YES';

    $currentChild = $childData[$childIndex];
    $the_name = ucwords($currentChild['name']);

    $args = miraiedu_get_widget_query_args('tappe-eta', $settings);
    if(!$childData || !isset($childData[$childIndex])) {
      return;
    }

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
      $query->the_post();
      $postId = get_the_ID();
      $the_text = get_post_meta($postId, 'text_'.$currentChild['gender'], true);
      $the_text = str_replace('{nome}', $the_name, $the_text);
      $the_title = get_the_title();
      $the_title = str_replace('{nome}', $the_name, $the_title);


    ?>

    <div class="miraiedu-tappa-eta-container">
      <div class="miraiedu-tappa-eta-img">
        <img src="<?php the_post_thumbnail_url(); ?>" />
      </div>
      <div class="miraiedu-tappa-eta-testo">
        <h3><?php echo($the_title); ?></h3>
        <p><?php echo($the_text); ?></p>
      </div>
    </div>



    <?php
    } else {
      ?>
      <div class="miraiedu-tappa-eta-container">
        <div class="miraiedu-tappa-eta-testo">
          <h3><?php echo($the_name); ?></h3>
        </div>
      </div>
      <?php
    }


    // BEGIN VIDEO WIDGET
    $args = miraiedu_get_widget_query_args('video-post', $settings);
    $query = new WP_Query( $args );
    if ( $query->have_posts() ) {
    ?>




    <div class="miraiedu-video-slider-container">

        <div class="miraiflix-container">

          <div class="miraiflix-navigation-left">
            <div class="miraiflix-navigation-left-inner"></div>
          </div>
          <div class="miraiflix-navigation-right">
            <div class="miraiflix-navigation-right-inner"></div>
          </div>

          <div class="miraiflix-inner-container">

            <?php

            // Start looping over the query results.
            while ( $query->have_posts() ) {
              $query->the_post();

              ?>

              <div class="miraiflix-slide-container">
                <a href="<?php the_permalink(); ?>" class="miraiflix-slide" style="background-image: url(<?php the_post_thumbnail_url(); ?>)">
                  <div class="miraiflix-slide-content">
                    <div class="miraiflix-slide-header">
                    </div>
                    <div class="miraiflix-slide-footer">
                      <div class="miraiflix-slide-footer-text">
                        <h3><?php the_title(); ?></h3>
                        <p>
                          <?php the_excerpt(); ?>
                        </p>
                      </div>
                      <div class="miraiflix-slide-footer-icon">
                        <i class="fas fa-play-circle"></i>
                      </div>
                    </div>
                  </div>
                </a>
                <div class="miraiflix-small-caption">
                  <?php the_title(); ?>
                </div>
              </div>

              <?php

            }

          }
          ?>
        </div>
      </div>

      <?php

      // END VIDEO WIDGET

      // BEGIN POST WIDGET

      $args = miraiedu_get_widget_query_args('post', $settings);

      $query = new WP_Query( $args );
      if ( $query->have_posts() ) {
  
      ?>
  
      <div class="miraiedu-video-slider-container posts">
          <div class="miraiflix-container">
  
            <div class="miraiflix-navigation-left">
              <div class="miraiflix-navigation-left-inner"></div>
            </div>
            <div class="miraiflix-navigation-right">
              <div class="miraiflix-navigation-right-inner"></div>
            </div>
  
            <div class="miraiflix-inner-container">
  
              <?php
  
              // Start looping over the query results.
              while ( $query->have_posts() ) {
                $query->the_post();
  
                ?>
  
                <div class="miraiflix-slide-container">
                  <a href="<?php the_permalink(); ?>" class="miraiflix-slide" style="background-image: url(<?php the_post_thumbnail_url(); ?>)">
                    <div class="miraiflix-slide-content">
                      <div class="miraiflix-slide-header">
                        BLOG
                      </div>
                      <div class="miraiflix-slide-footer">
                        <div class="miraiflix-slide-footer-text">
                          <h3><?php the_title(); ?></h3>
                        </div>
  
                      </div>
                    </div>
                  </a>
                </div>
  
                <?php
  
              }
  
            }
  
            ?>
          </div>
        </div>
  
        <?php




  }
}