<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
require_once 'modules/Administration/QuickRepairAndRebuild.php';
$repair = new RepairAndClear();
$repair->repairAndClearAll(
    array('rebuildExtensions', 'clearVardefs', 'clearTpls'),
    array('ModuleBuilder'),
    true,
    false
);
