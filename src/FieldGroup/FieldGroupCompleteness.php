<?php

namespace Devidw\ACF\Helper\FieldGroup;

use Devidw\ACF\Helper\FieldGroup\FieldGroup;
use Devidw\ACF\Helper\Field\Field;
use Devidw\ACF\Helper\Field\FieldVisibility;

/**
 * Completeness class
 */
class FieldGroupCompleteness extends FieldGroup
{
    /**
     * Get the completness of a ACF field group.
     * 
     * @param string $fieldGroupKey The field group key.
     * 
     * @return int The completness of the field group.
     */
    public static function getCompleteness()
    {
        $fields = static::getVisibleFieldKeys();

        $userValues = [];

        foreach ($fields as $fieldKey) {

            $field = Field::setKey($fieldKey)::setContextId(static::$contextId);

            // Filter out fields that are just layout fields and not actual fields to include in the completness calculation.
            if (in_array($field::get('type'), ['tab', 'group'])) {
                continue;
            }

            $fieldValue = $field::getValue();

            $userValues[$fieldKey] = $fieldValue;
        }

        $nonEmptyFields = array_filter($userValues, function ($value) {
            return $value !== '';
        });

        return count($nonEmptyFields) / count($userValues);
    }
}
