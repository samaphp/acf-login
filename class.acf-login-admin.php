<?php

class ACF_Login_Admin {

    private static $initiated = false;

    public static function init() {
        if ( ! self::$initiated ) {
            self::init_hooks();
        }
    }

    public static function init_hooks() {

        self::$initiated = true;

        add_action( 'admin_init', array( 'ACF_Login_Admin', 'admin_init' ) );
        add_action( 'admin_menu', array( 'ACF_Login_Admin', 'admin_menu' ) );
        add_filter( 'plugin_action_links_' . ACF_LOGIN__PLUGIN_BASENAME, array( 'ACF_Login_Admin', 'settings_link' ), 10, 4 );

    }

    public static function admin_init() {

        register_setting(
            'acf-login-page',
            'acf_login_field',
            array(
                'type' => 'string',
                'sanitize_callback' => [ 'ACF_Login_Admin', 'acf_login_field_validate' ],
                'default' => ''
            )
        );

        add_settings_section(
            'acf_login_section',
            __('Custom login field settings'),
            '',
            'acf-login-page'
        );

        add_settings_field(
            'acf_login_field',
            __('Custom login field'),
            array('ACF_Login_Admin', 'acf_login_field_callback'),
            'acf-login-page',
            'acf_login_section'
        );


        register_setting(
            'acf-login-page',
            'acf_login_field_only',
            array(
                'type' => 'string',
                'sanitize_callback' => [ 'ACF_Login_Admin', 'acf_login_field_only_validate' ],
                'default' => ''
            )
        );

        add_settings_field(
            'acf_login_field_only',
            __('Login by custom login field only'),
            array('ACF_Login_Admin', 'acf_login_field_only_callback'),
            'acf-login-page',
            'acf_login_section'
        );
    }

    public static function acf_login_field_callback() {
        $field_value = get_option('acf_login_field');
        $field_options = self::get_options();
        ?>
        <select name="acf_login_field" id="acf-login-field-select">
            <option value=""><?php print __('- Select field -') ?></option>
            <?php foreach ($field_options as $value => $label) { ?>
            <?php $selected = (($field_value == $value) ? ' selected' : ''); ?>
            <option value="<?php print esc_attr($value) ?>"<?php print $selected?>><?php print esc_attr($label) ?></option>
            <?php } ?>
        </select>
        <?php
        if ( count($field_options) < 1 ) {
        ?>
          <div class="error"><p><?php print __('You need to <a href="edit.php?post_type=acf-field-group">add field group</a> and show this field group in the user form to be able to pick a field from this field group to be used as a custom login field') ?></p></div>
        <?php
        }
    }

    public static function acf_login_field_only_callback() {
        $field_value = get_option('acf_login_field_only');
        $field_options = ['yes' => __('Only custom field'), 'no' => __('Either username or this selected custom field')];
        ?>
        <select name="acf_login_field_only" id="acf-login-field-select">
            <option value=""><?php print __('- Select -') ?></option>
            <?php foreach ($field_options as $value => $label) { ?>
            <?php $selected = (($field_value == $value) ? ' selected' : ''); ?>
            <option value="<?php print esc_attr($value) ?>"<?php print $selected?>><?php print esc_attr($label) ?></option>
            <?php } ?>
        </select>
        <?php
        if ( count($field_options) < 1 ) {
        ?>
          <div class="error"><p><?php print __('You need to <a href="edit.php?post_type=acf-field-group">add field group</a> and show this field group in the user form to be able to pick a field from this field group to be used as a custom login field') ?></p></div>
        <?php
        }
    }

    public static function settings_link( $links ) {
        $settings_link = '<a href="edit.php?post_type=acf-field-group&page=acf-login-page">Settings</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public static function admin_menu() {
        $slug = 'edit.php?post_type=acf-field-group';
        if ( function_exists('acf_get_setting') ) {
            $cap  = acf_get_setting( 'capability' );
            add_submenu_page( $slug, __( 'Custom login field', 'acf_login' ), __( 'Custom login field', 'acf_login' ), $cap, 'acf-login-page', array( 'ACF_Login_Admin', 'admin_index' ), 10 );
        }
    }

    public static function admin_index() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <?php settings_errors(); ?>

            <form action="options.php" method="post">
                <?php
                settings_fields('acf-login-page');
                do_settings_sections('acf-login-page');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get plugin options.
     *
     * @return array Options.
     */
    public static function get_options() {
        // The fields that user can pick as a custom user login field.
        $field_options = [];

        $field_groups = get_posts([
            'post_type' => 'acf-field-group',
            'post_status' => 'publish',
        ]);

        if ( !empty($field_groups) ) {
            foreach ($field_groups as $field_group) {
                $field_group_settings = maybe_unserialize($field_group->post_content);
                if ( !empty($field_group_settings) &&
                    !empty($field_group_settings['location']) &&
                    !empty($field_group_settings['location'][0]) &&
                    !empty($field_group_settings['location'][0][0]) &&
                    !empty($field_group_settings['location'][0][0]['param']) &&
                    ($field_group_settings['location'][0][0]['param'] == 'user_form') ) {

                    $user_fields = get_posts([
                        'post_type'   => 'acf-field',
                        'post_parent' => $field_group->ID,
                        'post_status' => 'publish',
                    ]);

                    if ( !empty($user_fields) ) {
                        foreach ($user_fields as $user_field) {
                            $field_label = $user_field->post_title;
                            $field_id = $user_field->post_name;
                            $field_options[$field_id] = $field_label;
                        }
                    }

                }

            }
        }

        return $field_options;
    }

    /**
     * Validate options.
     */
    public static function acf_login_field_validate( $value ) {
        $options = self::get_options();
        if ( !in_array( $value, array_keys($options) ) ) {
            return '';
        }

        return $value;
    }

    /**
     * Validate options.
     */
    public static function acf_login_field_only_validate( $value ) {
        $options = ['yes', 'no'];
        if ( !in_array( $value, array_keys($options) ) ) {
            return '';
        }

        return $value;
    }

}
