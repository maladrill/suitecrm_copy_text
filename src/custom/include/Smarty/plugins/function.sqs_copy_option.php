<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}

require_once 'custom/include/SQS_CopyText/Settings.php';

function smarty_function_sqs_copy_option($params, &$smarty)
{
    $module = isset($params['module']) ? $params['module'] : '';
    $field = isset($params['field']) ? $params['field'] : '';
    return SQS_CopyText_Settings::isEnabled($module, $field) ? 'checked="checked"' : '';
}
