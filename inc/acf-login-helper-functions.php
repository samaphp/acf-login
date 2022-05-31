<?php
/**
 * ACF-Login Helper Functions
 *
 * @package ACF_LOGIN
 */

/**
 * Getting a user by a meta value.
 *
 * @return WP_User object.
 */
function acf_login_get_user_by_meta( $meta_key, $meta_value, $password = '' ) {
    $users = get_users(array(
        'meta_key' => $meta_key,
        'meta_value' => $meta_value,
    ));

    $selected_user = null;
    if ( !empty($users) ) {
        require_once ABSPATH . WPINC . '/class-phpass.php';
        $wp_hasher = new PasswordHash(8, TRUE);

        foreach ($users as $userdata) {
            $password_hashed = $userdata->user_pass;
            if($wp_hasher->CheckPassword($password, $password_hashed)) {
                $selected_user = $userdata;
            }
        }
    }

    return $selected_user;
}

/**
 * Getting the selected custom login field data.
 */
function acf_login_get_custom_login_field_data() {
    $data = new \stdClass();
    $login_field = get_option('acf_login_field');
    $user_fields = get_posts([
        'post_type'   => 'acf-field',
        'post_name' => $login_field,
        'post_status' => 'publish',
    ]);
    if ( !empty($user_fields) ) {
        $user_field = reset($user_fields);
        $data->label = $user_field->post_title;
        $data->field_id = $user_field->post_name;
        $data->field_name = $user_field->post_excerpt;
    }
    return $data;
}
