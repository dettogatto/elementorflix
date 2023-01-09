<?php

class Elementor_Miraiedu_Register extends \ElementorPro\Modules\Forms\Classes\Action_Base
{
  /**
   * Get Name
   *
   * Return the action name
   *
   * @access public
   * @return string
   */
  public function get_name()
  {
    return 'miraiedu_register';
  }

  /**
   * Get Label
   *
   * Returns the action label
   *
   * @access public
   * @return string
   */
  public function get_label()
  {
    return 'User Register';
  }

  /**
   * Run
   *
   * Runs the action after submit
   *
   * @access public
   * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
   * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
   */
  public function run($record, $ajax_handler)
  {

    $settings = $record->get('form_settings');
    $emailField = $settings[$this->get_name() . "_email"];
    $passField = $settings[$this->get_name() . "_password"];
    $nameField = $settings[$this->get_name() . "_name"];
    $childDataField = $settings[$this->get_name() . "_child_data_json"];

    // Get submitted Form data
    $rawFields = (array) $record->get('fields');
    $creds = array();
    $creds['user_login'] = $rawFields[$emailField]["value"];
    $creds['user_email'] = $rawFields[$emailField]["value"];
    $creds['user_pass'] = $rawFields[$passField]["value"];
    $creds['nickname'] = $rawFields[$nameField]["value"];
    $creds['first_name'] = $rawFields[$nameField]["value"];

    $login_user = wp_insert_user($creds);

    if (!is_wp_error($login_user)) {
      $kids = json_decode($rawFields[$childDataField]["value"], true);
      add_user_meta(
        $login_user,
        'miraiedu_child_data_json',
        json_encode($kids, JSON_PRETTY_PRINT)
      );
      $redirect_to = $settings[$this->get_name() . "_url_success"];
      $redirect_to = $record->replace_setting_shortcodes($redirect_to, true);
      if (!empty($redirect_to)) {
        $ajax_handler->add_response_data('redirect_url', $redirect_to);
      }
      // Sync to AC
      miraiedu_ac_sync_user($login_user, true);
      // Update child data so that cron won't run tonight
      $new_kids = miraiedu_child_data_calculate_ages($kids);
      update_user_meta($login_user, 'miraiedu_child_data_json', json_encode($new_kids, JSON_PRETTY_PRINT));
      // Login user
      wp_set_current_user($login_user);
      wp_set_auth_cookie($login_user, true);
      return;
    }


    $ajax_handler->add_error_message(null);
    $ajax_handler->set_success(false);
    return;

  }

  /**
   * Register Settings Section
   *
   * Registers the Action controls
   *
   * @access public
   * @param \Elementor\Widget_Base $widget
   */
  public function register_settings_section($widget)
  {
    $widget->start_controls_section(
      'section_' . $this->get_name(),
      [
        'label' => $this->get_label(),
        'condition' => [
          'submit_actions' => $this->get_name(),
        ],
      ],
    );

    $widget->add_control(
      $this->get_name() . "_url_success",
      [
        'label' => "Success URL",
        'type' => \Elementor\Controls_Manager::TEXT,
        'separator' => 'before',
        'description' => "The url where the customer will be redirect if he is logged in with success",
      ],
    );

    $widget->add_control(
      $this->get_name() . "_name",
      [
        'label' => "FIELD: Name",
        'type' => \Elementor\Controls_Manager::TEXT,
        'separator' => 'before',
        'description' => "The form field ID containing the name of customer",
      ],
    );

    $widget->add_control(
      $this->get_name() . "_email",
      [
        'label' => "FIELD: Email",
        'type' => \Elementor\Controls_Manager::TEXT,
        'separator' => 'before',
        'description' => "The form field ID containing the email of customer",
      ],
    );

    $widget->add_control(
      $this->get_name() . "_password",
      [
        'label' => "FIELD: Password",
        'type' => \Elementor\Controls_Manager::TEXT,
        'separator' => 'none',
        'description' => "The form field ID containing the password of customer",
      ],
    );

    // $widget->add_control(
    //   $this->get_name() . "_child_gender",
    //   [
    //     'label' => "FIELD: Child Gender",
    //     'type' => \Elementor\Controls_Manager::TEXT,
    //     'separator' => 'before',
    //     'description' => "The form field ID containing the first gender field ID (it should end by '__0')",
    //   ],
    // );

    // $widget->add_control(
    //   $this->get_name() . "_child_name",
    //   [
    //     'label' => "FIELD: Child Name",
    //     'type' => \Elementor\Controls_Manager::TEXT,
    //     'separator' => 'before',
    //     'description' => "The form field ID containing the first name field ID (it should end by '__0')",
    //   ],
    // );

    // $widget->add_control(
    //   $this->get_name() . "_child_birthdate",
    //   [
    //     'label' => "FIELD: Child Birthdate",
    //     'type' => \Elementor\Controls_Manager::TEXT,
    //     'separator' => 'before',
    //     'description' => "The form field ID containing the first birthdate field ID (it should end by '__0')",
    //   ],
    // );

    // $widget->add_control(
    //   $this->get_name() . "_role",
    //   [
    //     'label' => "FIELD: Role",
    //     'type' => \Elementor\Controls_Manager::TEXT,
    //     'separator' => 'before',
    //     'description' => "The form field ID containing the first role field ID (it should end by '__0')",
    //   ],
    // );

    $widget->add_control(
      $this->get_name() . "_child_data_json",
      [
        'label' => "FIELD: Child data JSON",
        'type' => \Elementor\Controls_Manager::TEXT,
        'separator' => 'before',
        'description' => "The form field ID containing the JSON of the child data",
      ],
    );



    $widget->end_controls_section();

  }

  /**
   * On Export
   *
   * Clears form settings on export
   * @access Public
   * @param array $element
   */
  public function on_export($element)
  {
  }


}