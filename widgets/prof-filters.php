<?php

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use ElementorPro\Modules\QueryControl\Module as Module_Query;
use ElementorPro\Modules\QueryControl\Controls\Group_Control_Related;


if (!defined('ABSPATH'))
  exit; // Exit if accessed directly

/**
 * Elementor Form Poster
 *
 * Elementor widget for Form Poster.
 */
class MiraiflixProfFilters extends Widget_Base
{

  /**
   * Retrieve the widget name.
   *
   * @access public
   *
   * @return string Widget name.
   */
  public function get_name()
  {
    return 'prof-filters';
  }

  /**
   * Retrieve the widget title.
   *
   * @access public
   *
   * @return string Widget title.
   */
  public function get_title()
  {
    return 'Prof Filters';
  }

  /**
   * Retrieve the widget icon.
   *
   * @access public
   *
   * @return string Widget icon.
   */
  public function get_icon()
  {
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
  public function get_categories()
  {
    return ['miraiedu'];
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
  public function get_script_depends()
  {
    return ['miraiedu-prof-filters-js'];
  }

  public function get_style_depends()
  {
    return ['miraiedu-prof-filters-css'];
  }

  /**
   * Constructor
   */

  public function __construct($data = [], $args = null)
  {
    parent::__construct($data, $args);

    wp_register_script('miraiedu-prof-filters-js', plugins_url('/assets/js/prof-filters.js', __DIR__), array('jquery'));
    wp_register_style('miraiedu-prof-filters-css', plugins_url('/assets/css/prof-filters.css', __DIR__));
  }


  /**
   * Add CSS Typography control
   */

  private function add_typography_control($title, $slug, $selector, $close = true)
  {

    $this->start_controls_section(
      'section_' . $slug . '_style',
      [
        'label' => $title,
        'tab' => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
      'color_' . $slug,
      [
        'label' => esc_html__('Text Color', 'elementor'),
        'name' => 'color_' . $slug,
        'type' => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} ' . $selector => 'color: {{VALUE}};',
        ],
      ]
    );

    $this->add_group_control(
      'typography',
      [
        'Label' => 'Typography',
        'name' => 'typography_' . $slug,
        'selector' => '{{WRAPPER}} ' . $selector,
      ]
    );

    if ($close) {
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
  protected function register_controls()
  {
    $this->start_controls_section(
      'section_query',
      [
        'label' => esc_html__('Filters', 'elementor-pro'),
        'tab' => Controls_Manager::TAB_CONTENT,
      ]
    );

    $this->add_control(
      'target_link',
      [
        'label' => esc_html__('Link', 'textdomain'),
        'type' => \Elementor\Controls_Manager::URL,
        'placeholder' => esc_html__('https://your-link.com', 'textdomain'),
        'options' => ['url', 'is_external', 'nofollow'],
        'default' => [
          'url' => '',
          'is_external' => false,
          'nofollow' => true,
        ],
        'label_block' => true,
      ]
    );

    $this->add_control(
      'list',
      [
        'label' => 'Filters',
        'type' => \Elementor\Controls_Manager::REPEATER,
        'fields' => [
          [
            'name' => 'title',
            'label' => 'Title',
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'A nice title',
            'default' => 'Professione',
          ],
          [
            'name' => 'taxonomy',
            'label' => 'Taxonomy',
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'taxonomy_slug',
            'default' => 'filtri_professione',
          ],
        ],
        'default' => [
          [
            'title' => 'Professione',
            'taxonomy' => 'filtri_professione',
          ],
          [
            'title' => 'Titolo #2',
            'taxonomy' => 'slug_filtro',
          ],
        ],
        'title_field' => '{{{ title }}}',
      ]
    );




    $this->end_controls_section();

    $this->add_typography_control(
      "Heading",
      "filter_heading_lasdhbfkiadhbv",
      ".miraiedu-filters-container .miraiedu-filter-button"
    );






  }

  /**
   * Render the widget output on the frontend.
   *
   * Written in PHP and used to generate the final HTML.
   *
   * @access protected
   */
  protected function render()
  {
    $settings = $this->get_settings_for_display();

?>
<div class="miraiedu-filters-container" data-url="<?php echo ($settings["target_link"]["url"]); ?>">
  <?php

    // $args = miraiedu_get_widget_query_args('professionisti', $settings);

    // $query = new WP_Query($args);


    foreach ($settings['list'] as $index => $item):
      $filter_title = $item["title"];
      $filter_tax = $item["taxonomy"];

      $terms = get_terms(
        array(
          'taxonomy' => $filter_tax,
          'hide_empty' => true,
          'orderby' => 'count',
          'order' => 'DESC'
        )
      );

  ?>

  <div class="miraiedu-filter">
    <div class="miraiedu-filter-button">
      <div class="miraiedu-filter-heading">
        <?php
      echo ($filter_title);
        ?>
      </div>
      <div class="miraiedu-filter-caret"><i class="fas fa-angle-down"></i></div>
    </div>
    <div class="miraiedu-filter-content">
      <ul>

        <?php

      foreach ($terms as $term):
        ?>
        <li>
          <a href="#" data-slug="<?php echo ($term->slug); ?>" data-tax="<?php echo ($filter_tax); ?>">
            <?php echo ($term->name); ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php endforeach; ?>
</div>
<?php

  }
}