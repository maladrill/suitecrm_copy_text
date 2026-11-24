<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'modules/ModuleBuilder/controller.php';

class CustomModuleBuilderController extends ModuleBuilderController
{
    private const ALLOWED_TYPES = array(
        'varchar', 'text', 'phone', 'email', 'url', 'enum', 'multienum',
        'int', 'float', 'decimal', 'date', 'datetime', 'datetimecombo',
        'currency', 'relate',
    );

    public function action_SaveField()
    {
        $input = $this->copyInput();
        parent::action_SaveField();
        $this->saveCopyOption($input);
    }

    public function action_saveSugarField()
    {
        $input = $this->copyInput();
        parent::action_saveSugarField();
        $this->saveCopyOption($input);
    }

    public function action_DeleteField()
    {
        $input = $this->copyInput();
        parent::action_DeleteField();
        if ($input !== null) {
            $input['enabled'] = false;
            $this->saveCopyOption($input, true);
        }
    }

    private function copyInput()
    {
        $package = isset($_REQUEST['view_package']) ? $_REQUEST['view_package'] : 'studio';
        if ($package !== 'studio' || !isset($_REQUEST['sqs_copyable'])) {
            return null;
        }
        $module = isset($_REQUEST['view_module']) ? $_REQUEST['view_module'] : null;
        $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
        $enabled = $_REQUEST['sqs_copyable'];
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
        if (!is_string($module) || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/D', $module)
            || !is_string($name) || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/D', $name)
            || !is_string($type) || !in_array($enabled, array('0', '1'), true)
            || ($enabled === '1' && !in_array($type, self::ALLOWED_TYPES, true))) {
            throw new InvalidArgumentException('Invalid copy option or field');
        }
        return array('module' => $module, 'name' => $name, 'enabled' => $enabled === '1');
    }

    private function saveCopyOption($input, $deleted = false)
    {
        if ($input === null) {
            return;
        }
        $module = $input['module'] === 'Employees' ? 'Users' : $input['module'];
        if (!isset($GLOBALS['beanList'][$module])) {
            throw new InvalidArgumentException('Unknown module');
        }
        $bean = BeanFactory::getBean($module);
        if (!$bean) {
            throw new RuntimeException('Cannot load module');
        }
        $name = $input['name'];
        if (!$deleted && !isset($bean->field_defs[$name]) && isset($bean->field_defs[$name . '_c'])) {
            $name .= '_c';
        }
        if (!$deleted && !isset($bean->field_defs[$name])) {
            throw new RuntimeException('Field was not saved');
        }
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/D', $name)
            || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/D', $bean->object_name)) {
            throw new InvalidArgumentException('Invalid field definition');
        }

        $dir = 'custom/Extension/modules/' . $module . '/Ext/Vardefs';
        $file = $dir . '/_override_sqs_copytext_' . $name . '.php';
        if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
            throw new RuntimeException('Cannot create extension directory');
        }
        if ($input['enabled'] && !$deleted) {
            $code = "<?php\n// SQS Copy Text: managed by Studio.\n"
                . '$dictionary[' . var_export($bean->object_name, true) . "]['fields']["
                . var_export($name, true) . "]['sqs_copyable'] = true;\n";
            if (file_exists($file) && strpos((string) file_get_contents($file), 'SQS Copy Text:') === false) {
                throw new RuntimeException('Copy extension filename is already in use');
            }
            $temp = tempnam($dir, '.sqs_copy_');
            if ($temp === false || file_put_contents($temp, $code, LOCK_EX) === false
                || !chmod($temp, 0640) || !rename($temp, $file)) {
                if ($temp !== false && file_exists($temp)) {
                    unlink($temp);
                }
                throw new RuntimeException('Cannot save copy option');
            }
        } elseif (file_exists($file)) {
            if (strpos((string) file_get_contents($file), 'SQS Copy Text:') === false) {
                throw new RuntimeException('Copy extension filename is already in use');
            }
            if (!unlink($file)) {
                throw new RuntimeException('Cannot remove copy option');
            }
        } else {
            return;
        }

        require_once 'ModuleInstall/ModuleInstaller.php';
        $installer = new ModuleInstaller();
        $installer->silent = true;
        $installer->rebuild_extensions();
        require_once 'modules/Administration/QuickRepairAndRebuild.php';
        $repair = new RepairAndClear();
        $repair->repairAndClearAll(
            array('clearVardefs', 'clearTpls'),
            array($GLOBALS['beanList'][$module]),
            true,
            false
        );
    }
}
