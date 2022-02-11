<?php

namespace Devidw\ACF\Field;

use Devidw\ACF\Field\Field;

/**
 * Field Visibility class
 */
class FieldVisibility extends Field
{
    /**
     * Get the isolated field visibility.
     * 
     * @param array $field
     * 
     * @return bool
     */
    public static function getIsolatedVisibility(array $field): bool
    {
        if (empty($field['conditional_logic'])) {
            return true;
        }

        $conditionalLogic = $field['conditional_logic'];

        // dump($conditionalLogic);

        $appliedLogic = [];

        foreach ($conditionalLogic as $orKey => $orValue) {
            foreach ($orValue as $andKey => $andValue) {
                // dump("{$andValue['field']} {$andValue['operator']} {$andValue['value']}");

                // $fieldValue = acf_maybe_get_field($andValue['field'], 'user_' . $userId);

                // dump("{$fieldValue} {$andValue['operator']} {$andValue['value']}");

                $fieldValue = static::setKey($andValue['field'])::setContextId(static::$contextId)::getValue();

                switch ($andValue['operator']) {
                    case '==':
                        $appliedLogic[$orKey][$andKey] = $fieldValue == $andValue['value'];
                        break;

                    case '!=':
                        $appliedLogic[$orKey][$andKey] = $fieldValue != $andValue['value'];
                        break;

                    default:
                        $appliedLogic[$orKey][$andKey] = false;
                        break;
                }
            }
        }

        // Reduce the or groups (1. level) and their and groups (2. level) to a single boolean value
        $isVisible = array_reduce($appliedLogic, function ($carry, $item) {
            return $carry || array_reduce($item, function ($carry, $item) {

                if ($carry === null) {
                    return $item;
                }

                return $carry && $item;
            });
        }, false);

        // dump($appliedLogic);

        return $isVisible;
    }

    /**
     * Determine if a given field is visble based on its conditional logic.
     * 
     * @return bool
     */
    public static function isVisible(): bool
    {
        $parents = array_merge([static::get()], static::getParents());

        foreach ($parents as $parent) {
            if (!static::getIsolatedVisibility($parent)) {
                return false;
            }
        }

        return true;
    }
}
