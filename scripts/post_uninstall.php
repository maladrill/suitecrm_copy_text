<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}
// Delete only settings owned by this package.
$GLOBALS['db']->query("DELETE FROM config WHERE category = 'sqs_copy_text'");
sugar_cache_clear('admin_settings_cache');
require_once 'modules/Administration/QuickRepairAndRebuild.php';
$repair = new RepairAndClear();
$repair->repairAndClearAll(
    array('rebuildExtensions', 'clearVardefs', 'clearTpls'),
    array('ModuleBuilder'),
    true,
    false
);
