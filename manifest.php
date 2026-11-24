<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$manifest = array(
    'name' => 'SQS Copy Text',
    'description' => 'Studio field option and copy button in SuiteCRM Detail View',
    'version' => '1.0.0',
    'author' => 'SQS Polska',
    'published_date' => '2026-11-24',
    'type' => 'module',
    'is_uninstallable' => true,
    'remove_tables' => false,
    'key' => 'SQS_COPY_TEXT',
    'acceptable_sugar_flavors' => array('CE'),
);
$installdefs = array(
    'id' => 'SQS_Copy_Text',
    'pre_execute' => array('<basepath>/scripts/pre_install.php'),
    'copy' => array(
        array('from' => '<basepath>/src/custom/include/DetailView/DetailView.tpl',
              'to' => 'custom/include/DetailView/DetailView.tpl'),
        array('from' => '<basepath>/src/custom/include/SQS_CopyText/copy.js',
              'to' => 'custom/include/SQS_CopyText/copy.js'),
        array('from' => '<basepath>/src/custom/include/SQS_CopyText/copy.css',
              'to' => 'custom/include/SQS_CopyText/copy.css'),
        array('from' => '<basepath>/src/custom/modules/ModuleBuilder/controller.php',
              'to' => 'custom/modules/ModuleBuilder/controller.php'),
        array('from' => '<basepath>/src/custom/modules/ModuleBuilder/tpls/MBModule/field.tpl',
              'to' => 'custom/modules/ModuleBuilder/tpls/MBModule/field.tpl'),
        array('from' => '<basepath>/src/custom/Extension/modules/ModuleBuilder/Ext/Language/en_us.sqs_copy_text.php',
              'to' => 'custom/Extension/modules/ModuleBuilder/Ext/Language/en_us.sqs_copy_text.php'),
        array('from' => '<basepath>/src/custom/Extension/modules/ModuleBuilder/Ext/Language/pl_PL.sqs_copy_text.php',
              'to' => 'custom/Extension/modules/ModuleBuilder/Ext/Language/pl_PL.sqs_copy_text.php'),
    ),
    'post_execute' => array('<basepath>/scripts/post_install.php'),
    'post_uninstall' => array('<basepath>/scripts/post_uninstall.php'),
);
