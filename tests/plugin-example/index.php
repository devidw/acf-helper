<?php

/**
 * Plugin Name: Example Plugin
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Devidw\ACF\Field\Field;

if (!is_admin()) {

    // $field = Field::setKey('field_6206f2630a9e1')::setPostId(1)::getName();
    // $field = Field::setKey('field_6206f2630a9e1')::setPostId(1)::get();


    // get_field_object('field_6206f2630a9e1', 1)

    // dump($field);

    // $field = Field::setKey('field_6206f4ac5560a')::getName();

    // dump($field);

    // $field = Field::setKey('field_6206f4ac5560a')::setPostId(1)::getValue();
    // $field = Field::setKey('field_6206f4ac5560a')::setPostId(1)::get();
    // $field = Field::setKey('field_6206f4ac5560a')::setPostId(1)::hasParent();

    // dump($field);

    // $value = get_field('test_group_field_test_sub_field', 1);

    // dump($value);


    // dump(
    //     acf_get_field_post(22)
    // );
}
