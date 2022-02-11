<?php

namespace Devidw\ACF;

/**
 * Meta class for both fields and field groups.
 */
abstract class Meta
{
    /**
     * The field key.
     * 
     * @var string
     */
    public static string $key;

    /**
     * The post ID.
     * 
     * @var string
     */
    public static int $postId;

    /**
     * The post ID.
     * 
     * @var string
     */
    public static int $userId;

    /**
     * The context ID.
     * 
     * @var string
     */
    public static string $contextId;

    /**
     * Set the field key.
     * 
     * @param string $key The field key.
     * 
     * @return static
     */
    public static function setKey(string $key): static
    {
        static::$key = $key;

        return new static;
    }

    /**
     * Set the post ID.
     * 
     * @param int $postId The post ID.
     * 
     * @return static
     */
    public static function setPostId(int $postId): static
    {
        static::$contextId = static::$postId = $postId;

        return new static;
    }

    /**
     * Set the user ID.
     * 
     * @param int $userId The user ID.
     * 
     * @return static
     */
    public static function setUserId(int $userId): static
    {
        static::$userId = $userId;

        static::$contextId = "user_{$userId}";

        return new static;
    }

    /**
     * Set the context ID.
     * 
     * @param string $contextId The context ID.
     * 
     * @return static
     */
    public static function setContextId(string $contextId): static
    {
        static::$contextId = $contextId;

        return new static;
    }

    /**
     * Get the object.
     * 
     * @param string $key Key to any value stored in the object.
     * 
     * @return mixed
     */
    abstract public static function get($key = null);
}
