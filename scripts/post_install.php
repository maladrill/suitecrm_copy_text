<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}

if (!function_exists('post_install')) {
function post_install()
{
    static $completed = false;
    if ($completed) {
        return;
    }
    require_once 'modules/Administration/QuickRepairAndRebuild.php';
    $repair = new RepairAndClear();
    $repair->repairAndClearAll(
        array('rebuildExtensions', 'clearVardefs', 'clearTpls'),
        array('ModuleBuilder'),
        true,
        false
    );
    $completed = true;
}
}

post_install();
