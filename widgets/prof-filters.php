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
    return 'Filters';
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

    $this->add_group_control(
      'typography',
      [
        'Label' => 'Typography',
        'name' => 'typography_' . $slug,
        'selector' => '{{WRAPPER}} ' . $selector,
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
            'name' => 'type',
            'label' => 'Type',
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
              'tax' => 'Taxonomy',
              'age' => 'Age'
            ],
            'default' => 'tax',
          ],
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
            'condition' => [
              'type' => 'tax',
            ],
          ],
          [
            'name' => 'max_terms',
            'label' => 'Maximum terms',
            'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 1,
            'max' => 200,
            'step' => 1,
            'default' => 15,
            'condition' => [
              'type' => 'tax',
            ],
          ],
          [
            'name' => 'columns',
            'label' => 'Columns',
            'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 1,
            'max' => 10,
            'step' => 1,
            'default' => 1,
            'condition' => [
              'type' => 'tax',
            ],
          ],
          [
            'name' => 'age_data',
            'label' => 'Options',
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'placeholder' => '0, 0.06 | 0-6 mesi
0.06, 1 | 6 - 12 mesi
1, 3 | 1 - 3 anni',
            'condition' => [
              'type' => 'age',
            ],
          ]
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


    $this->add_control(
      'content_spacing',
      [
        'label' => 'Spacing',
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => -100,
        'max' => 100,
        'step' => 1,
        'default' => 0,
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter .miraiedu-filter-content ul' => 'margin: -{{VALUE}}px 0px;',
          '{{WRAPPER}} .miraiedu-filter .miraiedu-filter-content li' => 'margin: {{VALUE}}px 0px;',
        ],
      ]
    );




    $this->end_controls_section();


    $this->start_controls_section(
      'section_DEBUG_style',
      [
        'label' => "DEBUG",
        'tab' => Controls_Manager::TAB_STYLE,
      ]
    );

    $this->add_control(
      'show_first_element',
      [
        'label' => 'Show First',
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'label_on' => 'Show',
        'label_off' => 'No',
        'return_value' => 'yes',
        'default' => 'no',
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter:first-child .miraiedu-filter-content' => 'display: block',
        ],
      ]
    );

    $this->end_controls_section();


    $this->add_typography_control(
      "Heading",
      "filter_heading_lasdhbfkiadhbv",
      ".miraiedu-filters-container .miraiedu-filter-button",
    false
    );

    $this->add_control(
      'heading_color_active',
      [
        'label' => esc_html__('Text Color (Active)', 'elementor'),
        'type' => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter.active .miraiedu-filter-button' => 'color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'background_color',
      [
        'label' => esc_html__('Background Color', 'elementor'),
        'type' => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter' => 'background-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'background_color_active',
      [
        'label' => esc_html__('Background Color (Active)', 'elementor'),
        'type' => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter.active' => 'background-color: {{VALUE}};',
        ],
      ]
    );

    $this->end_controls_section();

    $this->add_typography_control(
      "Content",
      "filter_content_style",
      ".miraiedu-filters-container .miraiedu-filter-content li",
    false
    );

    $this->add_control(
      'color_content_active',
      [
        'label' => esc_html__('Text Color (Active)', 'elementor'),
        'type' => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter .miraiedu-filter-content li.active a' => 'color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'background_color_content',
      [
        'label' => esc_html__('Background Color', 'elementor'),
        'type' => Controls_Manager::COLOR,
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter .miraiedu-filter-content' => 'background-color: {{VALUE}};',
        ],
      ]
    );

    $this->add_control(
      'content_v_spacing',
      [
        'label' => 'Vertical Spacing',
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => 0,
        'max' => 200,
        'step' => 1,
        'default' => 0,
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter .miraiedu-filter-content ul' => 'margin: -{{VALUE}}px 0px;',
          '{{WRAPPER}} .miraiedu-filter .miraiedu-filter-content li' => 'margin: {{VALUE}}px 0px;',
        ],
      ]
    );

    $this->add_control(
      'content_column_spacing',
      [
        'label' => 'Column Spacing',
        'type' => \Elementor\Controls_Manager::NUMBER,
        'min' => 0,
        'max' => 200,
        'step' => 1,
        'default' => 20,
        'selectors' => [
          '{{WRAPPER}} .miraiedu-filter .miraiedu-filter-content ul' => 'column-gap: {{VALUE}}px;',
        ],
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
  protected function render()
  {
    $settings = $this->get_settings_for_display();

?>
<div class="miraiedu-filters-container <?php echo ($settings['show_first_element']); ?>"
  data-url="<?php echo ($settings["target_link"]["url"]); ?>">
  <?php

    // $args = miraiedu_get_widget_query_args('professionisti', $settings);

    // $query = new WP_Query($args);


    foreach ($settings['list'] as $index => $item):
      $filter_title = $item["title"];
      $filter_tax = "";
      $terms = array();

      if ($item["type"] == "age") {
        $filter_tax = "filtri_eta";
        foreach (explode("\n", $item["age_data"]) as $line) {
          $line = explode("|", $line);
          $terms[] = [
            "slug" => str_replace(" ", "", $line[0]),
            "name" => trim($line[1]),
          ];
        }
      } else {
        $filter_tax = $item["taxonomy"];
        $tmp_terms = get_terms(
          array(
            'taxonomy' => $item["taxonomy"],
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC'
          )
        );
        $count = intval($item['max_terms']);
        foreach ($tmp_terms as $tt) {
          $terms[] = (array) $tt;
          $count--;
          if ($count <= 0) {
            break;
          }
        }
      }


      $active = isset($_GET[$filter_tax]) && $_GET[$filter_tax];
      if ($active) {
        foreach ($terms as $i => $t) {
          if ($t["slug"] == $_GET[$filter_tax]) {
            $filter_title = $t["name"];
            $terms[$i]["active"] = true;
            break;
          }
        }
      }

      $ul_style = "";
      if ($item["columns"] > 1) {
        $c = $item["columns"];
        $ul_style = "columns: $c; -webkit-columns: $c; -moz-columns: $c;";
      }


  ?>

  <div class="miraiedu-filter <?php echo ($active ? "active" : ""); ?> miraiedu-filter-<?php echo ($filter_tax); ?>">
    <div class="miraiedu-filter-button">
      <div class="miraiedu-filter-heading">
        <?php
      echo ($filter_title);
        ?>
      </div>
      <div class="miraiedu-filter-caret"><i class="fas fa-angle-down"></i></div>
    </div>
    <div class="miraiedu-filter-content">
      <ul style="<?php echo ($ul_style); ?>">

        <?php

      foreach ($terms as $term):
        ?>
        <li class="<?php echo ($term["active"] ? "active" : "") ?>">
          <a href="#" data-slug="<?php echo ($term["slug"]); ?>" data-tax="<?php echo ($filter_tax); ?>">
            <?php echo ($term["name"]); ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <?php endforeach; ?>
  <?php if (true): ?>
  <div class="miraiedu-filter reset-filters">
    <i class="fas fa-redo"></i>
  </div>
  <?php endif; ?>
</div>
<?php

  }
}