<?php

if ( !class_exists( 'ACF_Login' ) ) {

    class ACF_Login
    {
        private static $initiated = false;

        public static function init()
        {
            if (!self::$initiated) {
                self::init_hooks();
            }
        }

        /**
         * Initializes WordPress hooks
         */
        private static function init_hooks()
        {
            self::$initiated = true;

            $login_field = get_option('acf_login_field');
            $custom_login_field_data = acf_login_get_custom_login_field_data();
            if ( !empty($login_field) && !empty($custom_login_field_data->label) ) {
                add_filter('authenticate', array('ACF_Login', 'custom_field_login'), 20, 3);
                add_filter('gettext', array('ACF_Login', 'change_login_field_label'), 20);
            }
        }

        public static function get_custom_login_field_label() {
            $label = null;
            $custom_login_field_data = acf_login_get_custom_login_field_data();
            if ( !empty($custom_login_field_data->label) ) {
                $label = esc_attr($custom_login_field_data->label);
            }
            return $label;
        }

        public static function get_custom_login_field_name() {
            $field_name = null;
            $custom_login_field_data = acf_login_get_custom_login_field_data();
            if ( !empty($custom_login_field_data->field_name) ) {
                $field_name = esc_attr($custom_login_field_data->field_name);
            }
            return $field_name;
        }

        public static function change_login_field_label( $text ) {
            // @TODO: Make sure it will work even if the pagenow value is changed to another name.
            if ( in_array($GLOBALS['pagenow'], array('wp-login.php')) ) {
                if (('Username' == $text) || ('Username or Email Address' == $text)) {
                    $field_label = self::get_custom_login_field_label();
                    $acf_login_field_only = get_option('acf_login_field_only');
                    if ( $acf_login_field_only == 'yes' ) {
                        $text = (!empty($field_label) ? $field_label : $text);
                    }
                    else {
                        $text .= ' ' . __('Or') . ' ' . (!empty($field_label) ? $field_label : $text);
                    }
                }
            }
            return $text;
        }

        public static function custom_field_login( $user, $custom_field_value, $password ) {

            if ( empty($custom_field_value) || empty($password) ) {
                $error = new WP_Error();

                if ( empty($custom_field_value) ) {
                    $field_label = self::get_custom_login_field_label();
                    $message = sprintf( __( '<strong>ERROR</strong>: %s field is empty.' ), $field_label );
                    $error->add( 'empty_username', $message );
                }

                if( empty($password) ) {
                    $error->add('empty_password', __('<strong>ERROR</strong>: Password field is empty.'));
                }

                return $error;
            }

            // Getting user object for the user who will match the custom field value and the password.
            $login_field = self::get_custom_login_field_name();
            $user = acf_login_get_user_by_meta( $login_field, $custom_field_value, $password );

            // If does not enforce login by custom field we will use username for getting user data.
            $acf_login_field_only = get_option('acf_login_field_only');
            if ( !$user && ($acf_login_field_only !== 'yes') ) {
                $user = wp_authenticate_username_password( $user, $custom_field_value, $password );
            }
            if ( !$user ) {
                $error = new WP_Error();
                $error->add('invalid', __('<strong>ERROR</strong>: The credentials you entered is invalid.'));
                return $error;
            }
            else {
                if ( !wp_check_password($password, $user->user_pass, $user->ID) ) {
                    $error = new WP_Error();
                    $error->add('invalid', __('<strong>ERROR</strong>: The credentials you entered is invalid.'));
                    return $error;
                }
                else {
                    return $user;
                }
            }
        }
    }

}
