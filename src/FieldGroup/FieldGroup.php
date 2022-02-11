<?php

namespace Devidw\ACF\Helper\FieldGroup;

use Devidw\ACF\Helper\Meta;
use Devidw\ACF\Helper\Field\FieldVisibility;

/**
 * Field Group class
 */
class FieldGroup extends Meta
{
    /**
     * Get the field group.
     * 
     * @return mixed
     */
    public static function get($key = null)
    {
        $fieldGroup = acf_get_field_group(static::$key);

        if ($key && isset($fieldGroup[$key])) {
            return $fieldGroup[$key];
        }

        return $fieldGroup;
    }

    /**
     * Get Fields
     * 
     * @return array
     */
    public static function getFields(): array
    {
        return acf_get_fields(static::$key);
    }

    /**
     * Get all field keys of a field group
     * 
     * @see
     * 
     * @return array
     */
    public static function getFieldKeys(): array
    {
        $keys = [];

        $fields = static::getFields();

        array_walk_recursive($fields, function ($v, $k) use (&$keys) {
            if ($k === 'key') {
                $keys[] = $v;
            }
        });

        return $keys;
    }

    /**
     * Get the visible fields of the field group.
     * 
     * @return array
     */
    public static function getVisibleFieldKeys(): array
    {
        $fields = static::getFieldKeys();

        $visibleFields = [];

        foreach ($fields as $fieldKey) {
            $field = FieldVisibility::setKey($fieldKey)::setContextId(static::$contextId)::isVisible();

            if ($field) {
                $visibleFields[] = $fieldKey;
            }
        }

        return $visibleFields;
    }
}
