<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}

/**
 * Read per-field copy settings through SuiteCRM's Administration settings API.
 */
function smarty_function_sqs_copy_option($params, &$smarty)
{
    $module = isset($params['module']) ? $params['module'] : '';
    $mode = isset($params['mode']) ? $params['mode'] : 'checked';
    if (!is_string($module) || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $module)) {
        return $mode === 'json' ? '[]' : '';
    }
    if ($module === 'Employees') {
        $module = 'Users';
    }

    static $settings = null;
    if ($settings === null) {
        require_once 'modules/Administration/Administration.php';
        $administration = new Administration();
        $administration->retrieveSettings('sqs_copy_text');
        $settings = $administration->settings;
    }

    $prefix = 'sqs_copy_text_' . $module . ':';
    if ($mode === 'json') {
        $fields = array();
        foreach ($settings as $key => $value) {
            if (strpos($key, $prefix) === 0 && (string) $value === '1') {
                $field = substr($key, strlen($prefix));
                if (preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $field)) {
                    $fields[] = $field;
                }
            }
        }
        $encoded = json_encode($fields, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        return $encoded === false ? '[]' : $encoded;
    }

    $field = isset($params['field']) ? $params['field'] : '';
    if ($mode !== 'checked' || !is_string($field)
        || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $field)) {
        return '';
    }
    return isset($settings[$prefix . $field]) && (string) $settings[$prefix . $field] === '1'
        ? 'checked="checked"' : '';
}
