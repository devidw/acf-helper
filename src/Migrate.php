<?php

namespace Devidw\ACF;

use Devidw\ACF\Query;


/**
 * Class to migrate ACF field data.
 * 
 * @since 1.0.0
 */
class Migrate
{
    /**
     * Add layout names of flexible content fields to their meta key names.
     * 
     * Add the layout names of flexible content entries to the ACF field name stored as `meta_key` in the database.
     * 
     * @since 1.0.0
     * 
     * @param string $parentMetaKey
     * @param string $childMetaKey
     * 
     * @return bool
     */
    public function renameWithFlexbileContentLayout(string $parentMetaKey, string $childMetaKey)
    {
        $metaKeyDollar = "{$parentMetaKey}_\$_{$childMetaKey}";
        $metaKeyRegexp = "^{$parentMetaKey}_([0-9]+)_{$childMetaKey}$";

        Query::filterUserQuery($metaKeyDollar, $metaKeyRegexp);

        $userQuery = new \WP_User_Query([
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => $parentMetaKey,
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => $metaKeyDollar,
                    'compare' => 'EXISTS',
                ],
            ],
        ]);

        $users = $userQuery->get_results();

        if (empty($users)) {
            throw new \Exception('No matching users found');
            return false;
        }

        // return $users;

        foreach ($users as $user) {
            // dd($user->ID, 0);

            $flexibleContentLayoutMapping = get_user_meta($user->ID, $parentMetaKey, true);
            // dd($flexibleContentLayoutMapping, 0);

            if (!$flexibleContentLayoutMapping) {
                continue;
            }

            global $wpdb;

            $sql = <<<SQL
                SELECT * 
                FROM {$wpdb->prefix}usermeta
                WHERE meta_key REGEXP %s
                AND user_id = %d
            SQL;

            $sql = $wpdb->prepare($sql, $metaKeyRegexp, $user->ID);
            $metaEntries = $wpdb->get_results($sql);

            // dd($metaEntries);

            foreach ($metaEntries as $metaEntry) {
                $oldMetaKey = $metaEntry->meta_key;

                $indexFound = preg_match("/^{$parentMetaKey}_([0-9]+)_{$childMetaKey}$/", $oldMetaKey, $matches);

                if (!$indexFound) {
                    throw new \Exception('No index found for meta key: ' . $oldMetaKey);
                    continue;
                }

                // dd($matches[1], 0);
                $layoutIndex = (int) $matches[1];

                $layoutName = $flexibleContentLayoutMapping[$layoutIndex];

                $validLayoutName = preg_match('/^[a-zA-Z0-9_]+$/', $layoutName);
                if (!$validLayoutName) {
                    throw new \Exception("Invalid layout name: {$layoutName}");
                    return false;
                }
                // dd($layoutName);

                $newMetaKey = "{$parentMetaKey}_{$layoutIndex}_{$layoutName}_{$childMetaKey}";
                // dd($newMetaKey);

                $success = $wpdb->update(
                    $wpdb->prefix . 'usermeta',
                    [
                        'meta_key' => $newMetaKey,
                    ],
                    [
                        'meta_key' => $oldMetaKey,
                        'user_id' => $user->ID,
                    ]
                );
                // dd("Update {$oldMetaKey} to {$newMetaKey} for user {$user->ID}: {$success}", 0);

                $wpdb->update(
                    $wpdb->prefix . 'usermeta',
                    [
                        'meta_key' => "_$newMetaKey",
                    ],
                    [
                        'meta_key' => "_$oldMetaKey",
                        'user_id' => $user->ID,
                    ]
                );
                // dd("Update _{$oldMetaKey} to _{$newMetaKey} for user {$user->ID}: {$success}", 0);
            }
        }
    }
}
