<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}

if (!function_exists('pre_install')) {
function pre_install()
{
    static $completed = false;
    if ($completed) {
        return;
    }
    global $suitecrm_version;
    require_once 'suitecrm_version.php';
    if (version_compare($suitecrm_version, '7.14.3', '<')
        || version_compare($suitecrm_version, '7.15.2', '>')) {
        throw new RuntimeException('SQS Copy Text requires SuiteCRM 7.14.3 through 7.15.2');
    }
    if (PHP_VERSION_ID < 70400 || PHP_VERSION_ID >= 80500) {
        throw new RuntimeException('SQS Copy Text requires PHP 7.4 through 8.4');
    }
    if (version_compare($suitecrm_version, '7.15.0', '<') && PHP_VERSION_ID >= 80300) {
        throw new RuntimeException('SuiteCRM 7.14.x does not support PHP 8.3 or 8.4');
    }
    $targets = array(
    'include/SQS_CopyText/copy.js',
    'include/SQS_CopyText/copy.css',
    'include/SQS_CopyText/Settings.php',
    'include/SQS_CopyText/Hook.php',
    'include/Smarty/plugins/function.sqs_copy_option.php',
    'Extension/application/Ext/LogicHooks/sqs_copy_text.php',
    'Extension/application/Ext/Language/en_us.sqs_copy_text.php',
    'Extension/application/Ext/Language/pl_PL.sqs_copy_text.php',
    'modules/ModuleBuilder/controller.php',
    'modules/ModuleBuilder/tpls/MBModule/field.tpl',
    'Extension/modules/ModuleBuilder/Ext/Language/en_us.sqs_copy_text.php',
    'Extension/modules/ModuleBuilder/Ext/Language/pl_PL.sqs_copy_text.php',
    );
    foreach ($targets as $target) {
        if (get_custom_file_if_exists($target) === 'custom/' . $target) {
            throw new RuntimeException('Existing customization would be overwritten: custom/' . $target);
        }
    }
    $completed = true;
}
}

// Support both ModuleInstaller hooks and UpgradeWizard's named hook call.
pre_install();
