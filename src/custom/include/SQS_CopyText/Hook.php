<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}

require_once 'custom/include/SQS_CopyText/Settings.php';

class SQS_CopyText_Hook
{
    public function render($event, $arguments)
    {
        $controller = isset($GLOBALS['app']->controller) ? $GLOBALS['app']->controller : null;
        if (!$controller || !isset($controller->action, $controller->module)
            || strtolower((string) $controller->action) !== 'detailview') {
            return;
        }
        $fields = SQS_CopyText_Settings::enabledFields($controller->module);
        if (!$fields) {
            return;
        }
        $json = json_encode($fields, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if ($json === false) {
            return;
        }
        print '<script type="application/json" id="sqs-copy-config">' . $json . '</script>'
            . '<link rel="stylesheet" href="custom/include/SQS_CopyText/copy.css">'
            . '<script src="custom/include/SQS_CopyText/copy.js"></script>';
    }
}
