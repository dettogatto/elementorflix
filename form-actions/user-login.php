<?php

class Elementor_Miraiedu_Login extends \ElementorPro\Modules\Forms\Classes\Action_Base {
  /**
  * Get Name
  *
  * Return the action name
  *
  * @access public
  * @return string
  */
  public function get_name() {
    return 'miraiedu_login';
  }
  
  /**
  * Get Label
  *
  * Returns the action label
  *
  * @access public
  * @return string
  */
  public function get_label() {
    return 'User Login';
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
  public function run( $record, $ajax_handler ) {
    
    $settings = $record->get( 'form_settings' );
    $emailField = $settings[$this->get_name() . "_email"];
    $passField = $settings[$this->get_name() . "_password"];
    $rememberField = $settings[$this->get_name() . "_remember"];
    
    // Get submitted Form data
    $rawFields = (array) $record->get( 'fields' );
    $creds = array();
    $creds['user_login'] = $rawFields[$emailField]["value"];
    $creds['user_password'] = $rawFields[$passField]["value"];
    $creds['remember'] = ($rawFields[$rememberField]["value"] !== "");
    
    $login_user = wp_signon( $creds, is_ssl() );

    if(!is_wp_error($login_user)){
      $redirect_to = $settings[$this->get_name() . "_url_success" ];
      $redirect_to = $record->replace_setting_shortcodes( $redirect_to, true );
      if ( ! empty( $redirect_to ) ) {
        $ajax_handler->add_response_data( 'redirect_url', $redirect_to );
      }
      return;
    }


    $ajax_handler->add_error(null, "I dati non corrispondono!");
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
  public function register_settings_section( $widget ) {
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
    
    $widget->add_control(
      $this->get_name() . "_remember",
      [
        'label' => "FIELD: Remember",
        'type' => \Elementor\Controls_Manager::TEXT,
        'separator' => 'none',
        'description' => "The form field ID containing the remember-me checkbox",
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
  public function on_export( $element ) {
  }
  
  
}
