<?php

namespace Devidw\ACF\Field;

use Devidw\ACF\Meta;

/**
 * Field class
 */
class Field extends Meta
{
    /**
     * The actual field.
     * 
     * @var array
     */
    public static array $field;

    /**
     * Get the field.
     * 
     * @return mixed
     */
    public static function get($key = null)
    {
        static::$field = acf_maybe_get_field(static::$key);

        if (!is_null($key)) {
            if (isset(static::$field[$key])) {
                return static::$field[$key];
            } else {
                return null;
            }
        }

        return static::$field;
    }

    /**
     * Get the field value.
     * 
     * @author Adam
     * 
     * @see https://wordpress.stackexchange.com/a/401663/218274
     * 
     * @return string
     */
    public static function getName()
    {
        $field = static::get();

        if (empty($field) || !isset($field['parent'], $field['name'])) {
            return $field;
        }

        $ancestors = array();

        while (!empty($field['parent']) && !in_array($field['name'], $ancestors)) {

            $parent = acf_get_field($field['parent']);

            $ancestors[] = $field['name'];

            $field = $parent;
        }

        $formatted_key = array_reverse($ancestors);
        $formatted_key = implode('_', $formatted_key);

        return $formatted_key;
    }

    /**
     * Get an ACF field by its key or name (regardless if it is a sub field of a group field or not).
     * 
     * @return mixed
     */
    public static function getValue()
    {
        if (static::hasParent()) {

            // dump('has parent');

            $parentField = acf_maybe_get_field(static::get('parent'));

            // When the field's parent is a repeater or flexible content field, we have to get the sub field.
            if (in_array($parentField['type'], ['repeater', 'flexible_content'])) {

                return get_sub_field(static::$key, static::$contextId);
            }
            // Groups suck, you can't get the value of a group field by its key with `get_field` and `get_sub_field`
            elseif (in_array($parentField['type'], ['group'])) {

                // dump($parentField);

                return get_field(static::getName(), static::$contextId);
            }
        } else {

            // dump('no parent');

            return get_field(static::$key, static::$contextId);
        }
    }

    /**
     * Has a field a parent field? (Not a field group)
     * 
     * @return bool
     */
    public static function hasParent(): bool
    {
        return !is_null(static::get('parent')) && str_starts_with(static::get('parent'), 'field_');
        // return !is_null(static::get('parent'));
    }


    /**
     * Is the given field related to another field?
     * 
     * @param string $maybeRelatedFieldKey
     * 
     * @return bool
     */
    public static function isParent(string $maybeRelatedFieldKey): bool
    {
        $found = self::walkUp(function ($field) use ($maybeRelatedFieldKey) {
            // dump($field::get());

            if ($field::get('key') === $maybeRelatedFieldKey) {
                return true;
            }
        });

        return $found ? true : false;
    }

    /**
     * Get all parent fields.
     * 
     * @return array
     */
    public static function getParents(): array
    {
        $parents = [];

        $found = self::walkUp(function ($field) use (&$parents) {
            $parents[] = $field::get();
        });

        return $parents;
    }

    /**
     * Walk up the field tree and chek each parent with a custom callback.
     * 
     * @param  callable $callback
     * 
     * @return mixed
     */
    public static function walkUp(callable $callback)
    {
        $field = new self;

        // When the field has no parent, it also has no related parent, grandparent, etc.
        while ($field::hasParent()) {

            $field = $field::setKey($field::get('parent'));

            // Call the callback with the current field.
            $found = $callback($field);

            // If the callback returns true, we found the field we were looking for.
            if ($found === true) {
                return $field;
            }

            // No callback match, check the next parent.
        }

        return null;
    }
}
