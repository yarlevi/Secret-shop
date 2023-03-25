<?php

/**
 * Plugin Name:       Custom Registration Field
 * Description:       Custom field for registration form
 * Version:           1.0
 * Author:			  Yariv
 */

// To add front end of custom field
add_action('woocommerce_register_form_start', 'front_end_field');

function front_end_field()
{
    //TODO
?>
    <p class="form-row form-row-first">
        <label for="billing_first_name"><?php _e('שם', 'text_domain'); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_first_name" id="billing_first_name" value="<?php if (!empty($_POST['billing_first_name'])) esc_attr_e($_POST['billing_first_name']); ?>" />
    </p>
    <p class="form-row form-row-last">
        <label for="billing_last_name"><?php _e('שם משפחה', 'text_domain'); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_last_name" id="billing_last_name" value="<?php if (!empty($_POST['billing_last_name'])) esc_attr_e($_POST['billing_last_name']); ?>" />
    </p>
    <div class="clear"></div>
<?php
}

//Validating the input from field
add_action('woocommerce_register_post', 'validate_input', 3, 10);

function validate_input($username, $email, $validation_errors)
{
    if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])) {
        $validation_errors->add('billing_first_name_error', __('<strong>Error</strong>: First name is required!', 'text_domain'));
    }

    if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])) {
        $validation_errors->add('billing_last_name_error', __('<strong>Error</strong>: Last name is required!.', 'text_domain'));
    }

    return $validation_errors;
}

//save data
add_action('woocommerce_created_customer', 'save_data', 1);

function save_data($customer_id)
{
    //First name field
    if (isset($_POST['billing_first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
        update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
    }

    //First name field
    if (isset($_POST['billing_last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
        update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
    }
}
