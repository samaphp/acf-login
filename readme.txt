=== Advanced Custom Fields: Login ===
Contributors: samaphp
Tags: acf, advanced custom fields, authentication, login
Requires at least: 5.0
Tested up to: 6.0
Stable tag: 1.0.0
License: GPLv2 or later

== Description ==

This plugin help you to choose a custom user field to allow users to login by this custom field instead of username or email on login page. This is helpful for your if you have a custom user field for Mobile number for example. or ID number for the citizens.

Major features in ACF Login include:

* Choosing a custom field to be used instead of Username/Email for login page.


== Installation ==

1. Download the required plugin: [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) plugin.
1. Add field group and show it in User form.
1. Add field and make it required for all users.
1. You need to make sure to pick a field that always have a unique value. Otherwise, if there were users with the same value of the selected custom field they should not have a matched password at least or only one of them will work.


== Uninstall ==

If in any case you need to rollback and remove the custom field login, you can just delete the selected custom field login by executing this command: `wp option update acf_login_field`
Or just simply deactivate the whole plugin: `wp plugin deactivate acf-login`
