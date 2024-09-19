<?php

/**
 * Gets a configuration variable.
 * @var string $key Configuration key to get.
 * @param mixed $default (Optional) Default value to return if not found.
 * @return mixed Returns the configuration variable or the default value if not found.
 */
function get_config(string $key, $default = null)
{
    if (!defined('APP_CONFIG')) return $default;
    return APP_CONFIG[$key] ?? $default;
}

/**
 * Gets a language string.
 * @param string $key Language key to get.
 * @param string $default (Optional) Default string to return if not found.
 * @return string Returns the language string or the default if not found.
 */
function get_lang(string $key, string $default = '')
{
    static $__langStrings = null;
    if (is_null($__langStrings)) {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
        $filename = "src/lang/{$lang}.php";
        $__langStrings = is_file($filename) ? require_once($filename) : [];
    }
    return $__langStrings[$key] ?? $default;
}
