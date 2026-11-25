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
        // Older installations may not have the compiled language extension.
        $isPolish = strcasecmp($GLOBALS['current_language'] ?? '', 'pl_PL') === 0;
        $translations = array(
            'copy' => array('LBL_SQS_COPY_TEXT_COPY', $isPolish ? 'Kopiuj wartość pola' : 'Copy field value'),
            'done' => array('LBL_SQS_COPY_TEXT_COPIED', $isPolish ? 'Skopiowano' : 'Copied'),
            'failed' => array('LBL_SQS_COPY_TEXT_FAILED', $isPolish ? 'Nie udało się skopiować' : 'Copy failed'),
        );
        $labels = array();
        foreach ($translations as $name => $translation) {
            $key = $translation[0];
            $labels[$name] = isset($GLOBALS['app_strings'][$key])
                && is_string($GLOBALS['app_strings'][$key])
                ? $GLOBALS['app_strings'][$key] : $translation[1];
        }
        $json = json_encode(
            array('fields' => $fields, 'labels' => $labels),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
        );
        if ($json === false) {
            return;
        }
        print '<script type="application/json" id="sqs-copy-config">' . $json . '</script>'
            . '<link rel="stylesheet" href="custom/include/SQS_CopyText/copy.css?v=' . (int) filemtime(__DIR__ . '/copy.css') . '">'
            . '<script src="custom/include/SQS_CopyText/copy.js?v=' . (int) filemtime(__DIR__ . '/copy.js') . '"></script>';
    }
}
