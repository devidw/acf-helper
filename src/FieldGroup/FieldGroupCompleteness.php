<?php

namespace Devidw\ACF\FieldGroup;

use Devidw\ACF\FieldGroup\FieldGroup;
use Devidw\ACF\Field\Field;
use Devidw\ACF\Field\FieldVisibility;

/**
 * Completeness class
 */
class FieldGroupCompleteness extends FieldGroup
{
    /**
     * Get the completeness of a ACF field group.
     * 
     * @param string $fieldGroupKey The field group key.
     * 
     * @return int The completeness of the field group.
     */
    public static function getCompleteness()
    {
        $fields = static::getVisibleFieldKeys();

        $userValues = [];

        foreach ($fields as $fieldKey) {

            $field = Field::setKey($fieldKey)::setContextId(static::$contextId);

            // Filter out fields that are just layout fields and not actual fields to include in the completeness calculation.
            if (in_array($field::get('type'), ['tab', 'group'])) {
                continue;
            }

            $fieldValue = $field::getValue();

            $userValues[$fieldKey] = $fieldValue;
        }

        $nonEmptyFields = array_filter($userValues, function ($value) {
            return $value !== '' && $value !== false;
        });

        if (count($nonEmptyFields) === 0 || count($userValues) === 0) {
            return 0;
        }

        return count($nonEmptyFields) / count($userValues);
    }
}
