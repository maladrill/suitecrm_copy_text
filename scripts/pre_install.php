<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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
if (version_compare($suitecrm_version, '7.15.0', '<') && PHP_VERSION_ID < 80100) {
    echo 'Warning: SuiteCRM 7.14.x with PHP 7.4 is outside the official compatibility matrix.' . PHP_EOL;
}
$targets = array(
    'custom/include/DetailView/DetailView.tpl',
    'custom/include/SQS_CopyText/copy.js',
    'custom/include/SQS_CopyText/copy.css',
    'custom/modules/ModuleBuilder/controller.php',
    'custom/modules/ModuleBuilder/tpls/MBModule/field.tpl',
    'custom/Extension/modules/ModuleBuilder/Ext/Language/en_us.sqs_copy_text.php',
    'custom/Extension/modules/ModuleBuilder/Ext/Language/pl_PL.sqs_copy_text.php',
);
foreach ($targets as $target) {
    if (file_exists($target)) {
        throw new RuntimeException('Existing customization would be overwritten: ' . $target);
    }
}
