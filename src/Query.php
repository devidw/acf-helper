<?php

namespace Devidw\ACF\Helper;

/**
 * Class Query
 * 
 * @since 1.0.0
 */
class Query
{
    /**
     * @since 1.0.0
     * 
     * @param string $metaKey
     * @param string $metaKey
     * @return void
     */
    public static function filterUserQuery(string $metaKeyDollar, string $metaKeyRegexp)
    {
        add_filter('pre_user_query', function (\WP_User_Query $query) use ($metaKeyDollar, $metaKeyRegexp): \WP_User_Query {
            $query->query_where = str_replace(
                "meta_key = '{$metaKeyDollar}'",
                "meta_key REGEXP '{$metaKeyRegexp}'",
                $query->query_where
            );
            return $query;
        });
    }

    /**
     * Get users with a given flexible content custom field value and layout.
     * 
     * @author Levi Cole (https://wordpress.stackexchange.com/users/37896/levi-cole)
     * 
     * @since 1.0.0
     * 
     * @see https://wordpress.stackexchange.com/a/402148/218274
     * 
     * @param string $field The flexible content field name.
     * @param string $layout The flexible layout name.
     * @param string $subfield The name of the layouts sub field.
     * @param string $subfield_value The value of the layouts sub field.
     *
     * @return array
     */
    public static function getUsersWithFlexibleContentValue(
        string $field,
        string $layout,
        string $subfield,
        string $subfield_value
    ): array {
        global $wpdb;

        /**
         * @var array|int[] $users The array we will push matching user IDs to.
         */
        $users = [];

        /**
         * @var string $map_sql Query for finding all users using the `$layout`
         */
        $map_sql = <<<MAPQUERY
            SELECT `user_id`, `meta_value` FROM $wpdb->usermeta 
            WHERE `meta_key` = '$field'
            AND `meta_value` LIKE '%$layout%'
            GROUP BY `user_id`
        MAPQUERY;

        /**
         * @var array|object[] $map_results Returns array of users and their flexible field mappings.
         */
        $map_results = $wpdb->get_results($map_sql);

        // dd($map_results);

        foreach ($map_results as $map_result) {

            $user_id = (int)$map_result->user_id;

            /**
             * @var mixed|string[] $map An array layouts orders.
             */
            $map = maybe_unserialize($map_result->meta_value);

            // dd($map);

            if (is_array($map)) {

                /**
                 * @var false|int $layout_index The position/index of the desired layout.
                 */
                $layout_index = array_search($layout, $map);

                // dd($layout_index);

                if ($layout_index !== false) {

                    /**
                     * @var string $meta_key Build the specific layout meta key e.g. `a_flexible_content_field_1_status`
                     */
                    $meta_key = $field . '_' . $layout_index . '_' . $subfield;

                    // dd($meta_key);

                    /**
                     * @var string $user_sql Query if user has any layout subfield matching desired value.
                     */
                    $user_sql = <<<USERQUERY
                        SELECT COUNT(*) FROM $wpdb->usermeta 
                        WHERE `meta_key` = '$meta_key'
                        AND `meta_value` = '$subfield_value'
                        AND `user_id` = $user_id
                    USERQUERY;

                    $user_result = $wpdb->get_var($user_sql);

                    // dd($user_result);

                    // User has matching values, add them to array.
                    if ($user_result > 0) {
                        $users[] = $user_id;
                    }
                }
            }
        }

        // if (!empty($users)) {
        //     $users = get_users(['include' => $users]);
        //     return $users;
        //     // dd($users);
        // }

        return $users;
    }

    /**
     * Get repeater rows by a subfield value.
     * 
     * @since 1.1.0
     * 
     * @param string $field The repeater field name.
     * @param string $subfield The repeater sub field name.
     * @param string $subfieldValue The repeater sub field value.
     * 
     * @return array
     */
    public static function getRepeaterRowsBySubfieldValue(
        string $field,
        string $subfield,
        string $subfieldValue
    ): array {
        global $wpdb;

        $metaKey = "^{$field}_([0-9]+)_{$subfield}$";

        // dd($metaKey);

        $sql = <<<SQL
            SELECT `user_id` FROM {$wpdb->usermeta} 
            WHERE `meta_key` REGEXP %s
            AND `meta_value` = %s
        SQL;

        $sql = $wpdb->prepare($sql, $metaKey, $subfieldValue);

        $users = $wpdb->get_results($sql);

        // dd($users);

        $repeaters = [];

        foreach ($users as $user) {
            $repeaters[$user->user_id] = get_field($field, 'user_' . $user->user_id);
        }

        // dd($repeaters);

        $rows = [];

        foreach ($repeaters as $userId => $repeater) {
            foreach ($repeater as $row) {
                // dump($row[$subfield]);

                if (
                    isset($row[$subfield]) &&
                    $row[$subfield] === $subfieldValue
                ) {
                    $rows[$userId] = $row;
                }
            }
        }

        return $rows;
    }
}
