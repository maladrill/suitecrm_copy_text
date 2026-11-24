<?php
// Exercise the exact previous package bytes and reject tampered or unowned files.
$mode = isset($argv[1]) ? $argv[1] : 'valid';
$version = isset($argv[2]) ? $argv[2] : '1.0.6';
define('sugarEntry', true);
set_include_path(__DIR__ . '/fixtures' . PATH_SEPARATOR . get_include_path());
$root = __DIR__ . '/..';
$temp = sys_get_temp_dir() . '/sqs-copy-upgrade-' . bin2hex(random_bytes(6));
$relative = 'custom/include/SQS_CopyText/copy.js';
mkdir($temp . '/custom/include/SQS_CopyText', 0700, true);
$fixtureVersion = in_array($version, array('1.0.7', '1.0.8'), true) ? $version : '1.0.6';
copy(__DIR__ . '/fixtures/previous/copy-' . $fixtureVersion . '.js', $temp . '/' . $relative);
if ($mode === 'modified') {
    file_put_contents($temp . '/' . $relative, 'local edit', FILE_APPEND);
}
chdir($temp);
$GLOBALS['test_history_status'] = $mode === 'uninstalled' ? 'uninstalled' : 'installed';
$_REQUEST['previous_version'] = $version;
$_REQUEST['previous_id'] = '11111111-1111-1111-1111-111111111111';
function get_custom_file_if_exists($file)
{
    return file_exists('custom/' . $file) ? 'custom/' . $file : $file;
}

$caught = null;
try {
    require $root . '/scripts/pre_install.php';
} catch (RuntimeException $error) {
    $caught = $error;
}
if (($mode === 'valid' && $caught !== null)
    || ($mode !== 'valid' && ($caught === null
        || strpos($caught->getMessage(), $relative) === false))) {
    throw new RuntimeException('Upgrade ownership verification failed: ' . $mode);
}
if ($mode === 'valid') {
    require $root . '/manifest.php';
    if ($upgrade_manifest['upgrade_paths'][$version]['id'] !== 'SQS_Copy_Text') {
        throw new RuntimeException('Missing Module Loader upgrade path');
    }
}
