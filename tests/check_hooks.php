<?php
// Model both ModuleInstaller's include hook and UpgradeWizard's named hook call.
define('sugarEntry', true);
set_include_path(__DIR__ . '/fixtures' . PATH_SEPARATOR . get_include_path());
$checks = 0;
function get_custom_file_if_exists($path)
{
    global $checks;
    $checks++;
    return $path;
}
function sugar_cache_clear($key)
{
    $GLOBALS['cache_clear_count']++;
}
class TestDatabase
{
    public $queries = array();
    public function query($sql)
    {
        $this->queries[] = $sql;
    }
}
$GLOBALS['db'] = new TestDatabase();
$GLOBALS['cache_clear_count'] = 0;

require __DIR__ . '/../scripts/pre_install.php';
if ($checks !== 8 || !function_exists('pre_install')) {
    throw new RuntimeException('ModuleInstaller pre-install hook did not execute');
}
pre_install();
if ($checks !== 8) {
    throw new RuntimeException('UpgradeWizard ran pre-install twice');
}

require __DIR__ . '/../scripts/post_install.php';
include __DIR__ . '/../scripts/post_install.php';
post_install();
if (RepairAndClear::$calls !== 1) {
    throw new RuntimeException('Post-install hook was not idempotent');
}

require __DIR__ . '/../scripts/post_uninstall.php';
include __DIR__ . '/../scripts/post_uninstall.php';
post_uninstall();
if (RepairAndClear::$calls !== 2 || count($GLOBALS['db']->queries) !== 1
    || $GLOBALS['cache_clear_count'] !== 1) {
    throw new RuntimeException('Post-uninstall hook was not idempotent');
}
