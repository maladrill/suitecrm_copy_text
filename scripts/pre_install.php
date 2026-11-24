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

    // Accept an installed package's unchanged files during a verified upgrade.
    $common = array(
        'Extension/application/Ext/LogicHooks/sqs_copy_text.php' => 'e438c66502184b3af93f7dca680b9d57f1218cdd2e22c813c92603b25362fd1a',
        'Extension/modules/ModuleBuilder/Ext/Language/en_us.sqs_copy_text.php' => 'be63b91cc57b97ec9f4098410dbf8b9ceacfa3e6013e294b187728ff41e64d98',
        'Extension/modules/ModuleBuilder/Ext/Language/pl_PL.sqs_copy_text.php' => '210f19759c77e843cc3c8bc6b694166f5216f92a074f5d2b3f3e08a64d9ba3dc',
        'include/SQS_CopyText/Settings.php' => '313953b5cf194fcb1ae1131bb603f3322f66492a47e6833c068f1a447b9f71e1',
        'include/Smarty/plugins/function.sqs_copy_option.php' => 'd5ee64bec567a1cc5f89e30bdf36d409f4683f7c874bc70d9126fc482416cfdd',
        'modules/ModuleBuilder/controller.php' => '9e0cc2b14130ae83f687c9b5c98f38de292f5a571445d0f0ce260ef3446142a4',
        'modules/ModuleBuilder/tpls/MBModule/field.tpl' => '3c32f3034caa00753bb43d8f86ed43edbf4483c90b60253824507670d6c8ecff',
    );
    $legacy = array(
        'include/SQS_CopyText/Hook.php' => '42c7248bcb85410bf67cbf999f23e7e39554b0fbe513536a6d406c99c2a9607b',
        'include/SQS_CopyText/copy.css' => '7c60e77d66a6426ef7f1b0f87282feccd0b38a01788dc771094c8a0ed3279c5f',
    );
    $knownVersions = array(
        '1.0.3' => $common + $legacy + array(
            'include/SQS_CopyText/copy.js' => 'effe5fc3d11b82ad829c3106a071cda0afc72f9c5e6ac08ec9cdc3d13a791d1e',
        ),
        '1.0.4' => $common + $legacy + array(
            'include/SQS_CopyText/copy.js' => '1e39118c7efd47529bfbf160478b9f1592bd31e4f04b0914154f96f527f568d0',
        ),
        '1.0.5' => $common + array(
            'Extension/application/Ext/Language/en_us.sqs_copy_text.php' => 'e591b8bb0a7bfafa78196365d6e5bfdb2c2a8e34e8c9783492ab1c185455b0a3',
            'Extension/application/Ext/Language/pl_PL.sqs_copy_text.php' => 'fce186f54a69cc90258ea8c0b4436e004e7d91be64b7ed3e76fa13d270c871f3',
            'include/SQS_CopyText/Hook.php' => 'b74ada8ae7c1804f4b63fef0fb43a5bb228875ac0c828ae6029bb32a33a4f2bd',
            'include/SQS_CopyText/copy.css' => '83a83000d5b6253175967197e617472e2d8304c188ead6fdb889aba19349c7f8',
            'include/SQS_CopyText/copy.js' => '8c42cc525190edce3ef3f18391dce530095b2b3144e4a36598eb00694c261006',
        ),
    );
    $previousVersion = isset($_REQUEST['previous_version']) ? $_REQUEST['previous_version'] : '';
    $previousId = isset($_REQUEST['previous_id']) ? $_REQUEST['previous_id'] : '';
    $ownedFiles = array();
    if (is_string($previousVersion) && isset($knownVersions[$previousVersion])
        && is_string($previousId) && preg_match('/^[a-f0-9-]{36}$/Di', $previousId)) {
        require_once 'modules/Administration/UpgradeHistory.php';
        $history = new UpgradeHistory();
        $installed = $history->retrieve($previousId);
        if ($installed && $installed->id_name === 'SQS_Copy_Text'
            && $installed->version === $previousVersion
            && $installed->status === 'installed' && $installed->type === 'module') {
            $ownedFiles = $knownVersions[$previousVersion];
        }
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
            $digest = hash_file('sha256', 'custom/' . $target);
            if (!isset($ownedFiles[$target]) || $digest === false
                || !hash_equals($ownedFiles[$target], $digest)) {
                require_once 'ModuleInstall/ModuleInstaller.php';
                ModuleInstaller::abort(array('Existing customization would be overwritten: custom/' . $target));
            }
        }
    }
    $completed = true;
}
}

// Support both ModuleInstaller hooks and UpgradeWizard's named hook call.
pre_install();
