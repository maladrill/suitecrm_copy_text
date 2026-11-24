<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}

class SQS_CopyText_Settings
{
    private static $settings = null;

    private static function validName($name)
    {
        return is_string($name) && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $name);
    }

    private static function prefix($module)
    {
        if (!self::validName($module)) {
            return null;
        }
        return 'sqs_copy_text_' . ($module === 'Employees' ? 'Users' : $module) . ':';
    }

    private static function settings()
    {
        if (self::$settings === null) {
            require_once 'modules/Administration/Administration.php';
            $administration = new Administration();
            $administration->retrieveSettings('sqs_copy_text');
            self::$settings = is_array($administration->settings)
                ? $administration->settings : array();
        }
        return self::$settings;
    }

    public static function isEnabled($module, $field)
    {
        $prefix = self::prefix($module);
        if ($prefix === null || !self::validName($field)) {
            return false;
        }
        $settings = self::settings();
        return isset($settings[$prefix . $field])
            && (string) $settings[$prefix . $field] === '1';
    }

    public static function enabledFields($module)
    {
        $prefix = self::prefix($module);
        if ($prefix === null) {
            return array();
        }
        $fields = array();
        foreach (self::settings() as $key => $value) {
            if (strpos($key, $prefix) === 0 && (string) $value === '1') {
                $field = substr($key, strlen($prefix));
                if (self::validName($field)) {
                    $fields[] = $field;
                }
            }
        }
        return $fields;
    }
}
