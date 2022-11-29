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
class MiraiflixProfGrid extends Widget_Base {

  /**
  * Retrieve the widget name.
  *
  * @access public
  *
  * @return string Widget name.
  */
  public function get_name() {
    return 'prof-grid';
  }

  /**
  * Retrieve the widget title.
  *
  * @access public
  *
  * @return string Widget title.
  */
  public function get_title() {
    return 'Prof Grid';
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
  * Add CSS Typography control
  */

  private function add_typography_control($title, $slug, $selector, $close = true) {

    $this->start_controls_section(
      'section_'.$slug.'_style',
      [
        'label' => $title,
        'tab' => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
			'color_'.$slug,
			[
				'label' => esc_html__( 'Text Color', 'elementor' ),
        'name' => 'color_'.$slug,
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} '.$selector => 'color: {{VALUE}};',
				],
			]
		);

    $this->add_group_control(
      'typography',
      [
        'Label' => 'Typography',
        'name' => 'typography_'.$slug,
        'selector' => '{{WRAPPER}} '.$selector,
      ]
    );

    if($close){
      $this->end_controls_section();
    }
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
      'dynamic_age',
      [
        'label' => 'Età dinamica personalizzata',
        'type' => Controls_Manager::SWITCHER,
        'description' => "Attivalo per i recommended per l'utente connesso"
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
        'description' => "Il numero del figlio su cui basare il filtro età. Se il figlio non esiste per l'utente corrente non verrà mostrato nulla.",
        'condition' => [
					'dynamic_age' => 'yes',
				],
      ]
    );

    $this->add_control(
      'min_age',
      [
        'label' => 'Età minima',
        'type' => Controls_Manager::NUMBER,
        'min' => '0',
        'max' => '999',
        'step' => '0.01',
        'default' => '0',
        'condition' => [
					'dynamic_age' => '',
				],
      ]
    );

    $this->add_control(
      'max_age',
      [
        'label' => 'Età massima',
        'type' => Controls_Manager::NUMBER,
        'min' => '0',
        'max' => '999',
        'step' => '0.01',
        'default' => '99',
        'description' => 'Lascia vuoto per non filtrare. Ricorda:<br>0.01 = 1 mese<br>0.11 = 11 mesi<br>1.6 = 1 anno e mezzo<br>2 = 2 anni<br>etc.',
        'condition' => [
					'dynamic_age' => '',
				],
      ]
    );


    $this->add_control(
      'topics',
      [
        'label' => 'Temi',
        'label_block' => true,
        'type' => Controls_Manager::TEXT,
        'description' => 'Inserisci gli <strong>slug</strong> dei temi da mostrare, separati da una virgola. Lascia vuoto per non filtrare.',
        'default' => ''
      ]
    );

    $this->add_control(
      'filter_search',
      [
        'label' => 'Filtra per query di ricerca',
        'type' => Controls_Manager::SWITCHER,
        'description' => 'Se selezionato mostra solo professionisti che corrispondono alla ricerca e ai filtri selezionati dall\'utente.<br>I filtri dell\'utente funzionano solo se il filtro corrispondente qui sopra è lasciato vuoto.'
      ]
    );

    $this->add_control(
      'posts_per_page',
      [
        'label' => 'Massimo risultati',
        'type' => Controls_Manager::NUMBER,
        'min' => '0',
        'max' => '999',
        'step' => '1',
        'default' => '20',
        'description' => 'Quanti professionisti mostrare',
      ]
    );

    $this->add_control(
      'fallback',
      [
        'label' => 'Testo in assenza di risultati',
        'label_block' => true,
        'type' => Controls_Manager::TEXT,
        'description' => 'Il testo da mostrare quando non vengono trovati risultati',
        'default' => 'Nessun professionista trovato'
      ]
    );

    $this->end_controls_section();

    $this->add_typography_control(
      "Title",
      "title",
      ".miraiflix-slide-header h3"
    );

    $this->add_typography_control(
      "Profession",
      "profession",
      ".miraiflix-slide-header h4"
    );

    $this->add_typography_control(
      "Curriculum",
      "curriculum_hover",
      ".miraiedu-video-slider-container.prof .miraiflix-slide-footer p"
    );    

    $this->add_typography_control(
      "Button",
      "button_hover",
      ".miraiedu-video-slider-container.prof .miraiflix-footer-button"
    );




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

    $args = miraiedu_get_widget_query_args('professionisti', $settings);

    $query = new WP_Query( $args );

    // echo('<pre>');
    // var_dump($args);
    // echo('</pre>');

    ?>

    <div class="miraiedu-video-slider-container grid prof miraiedu-prof-container">
      <?php
      if ( $query->have_posts() ) {
        ?>



        <div class="miraiflix-container">

          <div class="miraiflix-inner-container">

            <?php

            // Start looping over the query results.
            while ( $query->have_posts() ) {
              $query->the_post();
              $the_id = get_the_ID();

              $professione = get_post_meta($the_id, 'professione', true);
              // $location = get_post_meta($the_id, 'posizione', true);
              $professione = get_post_meta($the_id, 'professione', true);
              $curriculum = get_post_meta($the_id, 'curriculum', true);


              // $tariffa_t_1 = get_post_meta($the_id, 'tariffa_testo_1', true);
              // $tariffa_c_1 = get_post_meta($the_id, 'tariffa_costo_1', true);
              // $tariffa_t_2 = get_post_meta($the_id, 'tariffa_testo_2', true);
              // $tariffa_c_2 = get_post_meta($the_id, 'tariffa_costo_2', true);

              // $tariffe = '';
              // if($tariffa_t_1){
              //   $tariffe .= '<div class="rate-row"><div>'.$tariffa_t_1.'</div><div class="green">'.$tariffa_c_1.' €</div></div>';
              // }
              // if($tariffa_t_2){
              //   $tariffe .= '<div class="rate-row"><div>'.$tariffa_t_2.'</div><div class="green">'.$tariffa_c_2.' €</div></div>';
              // }

              ?>

              <div class="miraiflix-slide-container">
                <a href="<?php the_permalink(); ?>" class="miraiflix-slide" style="background-image: url(<?php the_post_thumbnail_url(); ?>)">
                  <div class="miraiflix-slide-content">
                    <div class="miraiflix-slide-header">
                      <h3><?php the_title(); ?></h3>
                      <h4><?php echo($professione); ?></h4>
                    </div>
                    <div class="miraiflix-slide-footer">
                      <div class="miraiflix-slide-footer-content">
                        <p><?php echo($curriculum); ?></p>
                        <div class="miraiflix-footer-button">Prenota una consulenza</div>
                      </div>
                    </div>
                  </div>
                </a>
              </div>

              <?php

            }

          } elseif(!$settings['dynamic_age']) {
            // No posts found
            echo('<p class="miraiflix-fallback-text">'.$settings['fallback'].'</p>');
          }

          ?>
        </div>
      </div>

      <?php
    }

  }
