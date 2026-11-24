<?php
// Verify the application hook renders only configured fields on Detail View.
define('sugarEntry', true);
set_include_path(__DIR__ . '/../src' . PATH_SEPARATOR . __DIR__ . '/fixtures'
    . PATH_SEPARATOR . get_include_path());
require __DIR__ . '/../src/custom/include/SQS_CopyText/Hook.php';
require __DIR__ . '/../src/custom/include/Smarty/plugins/function.sqs_copy_option.php';

$GLOBALS['app'] = (object) array('controller' => (object) array(
    'action' => 'DetailView',
    'module' => 'Accounts',
));
$hook = new SQS_CopyText_Hook();
ob_start();
$hook->render('after_ui_frame', null);
$output = ob_get_clean();
if (strpos($output, 'id="sqs-copy-config">["reference_c"]</script>') === false
    || strpos($output, 'custom/include/SQS_CopyText/copy.js') === false
    || strpos($output, 'secret_c') !== false
    || strpos($output, 'other_c') !== false) {
    throw new RuntimeException('Detail View configuration is incorrect');
}
$smarty = null;
if (smarty_function_sqs_copy_option(array('module' => 'Accounts', 'field' => 'reference_c'), $smarty)
    !== 'checked="checked"') {
    throw new RuntimeException('Studio checkbox did not read its setting');
}

$GLOBALS['app']->controller->action = 'EditView';
ob_start();
$hook->render('after_ui_frame', null);
if (ob_get_clean() !== '') {
    throw new RuntimeException('Hook affected a non-Detail View');
}
