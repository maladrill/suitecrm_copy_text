<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
// Remove only extension files created by this package.
$files = glob('custom/Extension/modules/*/Ext/Vardefs/_override_sqs_copytext_*.php');
foreach ($files ?: array() as $file) {
    if (is_file($file) && strpos((string) file_get_contents($file), 'SQS Copy Text:') !== false) {
        unlink($file);
    }
}
require_once 'modules/Administration/QuickRepairAndRebuild.php';
$repair = new RepairAndClear();
$repair->repairAndClearAll(
    array('rebuildExtensions', 'clearVardefs', 'clearTpls'),
    array('ModuleBuilder'),
    true,
    false
);
