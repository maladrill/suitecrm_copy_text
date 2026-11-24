<?php
// Verify Studio can persist the option on a built-in field of type "name".
define('sugarEntry', true);
set_include_path(__DIR__ . '/fixtures' . PATH_SEPARATOR . get_include_path());
$GLOBALS['beanList'] = array('AOS_Products' => 'AOS_Products');
$GLOBALS['base_save_count'] = 0;
$_REQUEST = array(
    'view_module' => 'AOS_Products',
    'name' => 'name',
    'type' => 'name',
    'sqs_copyable' => '1',
    'is_update' => 'true',
);
require __DIR__ . '/../src/custom/modules/ModuleBuilder/controller.php';
$controller = new CustomModuleBuilderController();
$controller->action_saveSugarField();
if ($GLOBALS['base_save_count'] !== 1
    || $GLOBALS['saved_setting'] !== array('sqs_copy_text', 'AOS_Products:name', '1')) {
    throw new RuntimeException('Studio did not save the name field option');
}
$template = file_get_contents(__DIR__ . '/../src/custom/modules/ModuleBuilder/tpls/MBModule/field.tpl');
if (strpos($template, "$" . "vardef.type == 'name'") === false) {
    throw new RuntimeException('Studio does not show the checkbox for name fields');
}
