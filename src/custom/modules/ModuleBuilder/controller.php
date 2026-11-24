<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}

require_once 'modules/ModuleBuilder/controller.php';
require_once 'modules/Administration/Administration.php';

class CustomModuleBuilderController extends ModuleBuilderController
{
    private const ALLOWED_TYPES = array(
        'name', 'varchar', 'text', 'phone', 'email', 'url', 'enum', 'multienum',
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
            $this->saveCopyOption($input);
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
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
        $enabled = $_REQUEST['sqs_copyable'];
        if (!is_string($module) || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $module)
            || !is_string($name) || !preg_match('/^[A-Za-z][A-Za-z0-9_]*$/D', $name)
            || !is_string($type) || !in_array($enabled, array('0', '1'), true)
            || ($enabled === '1' && !in_array($type, self::ALLOWED_TYPES, true))) {
            throw new InvalidArgumentException('Invalid field copy option');
        }
        $newField = isset($_REQUEST['is_update']) && $_REQUEST['is_update'] === 'false';
        if ($newField) {
            $name .= '_c';
        }
        return array('module' => $module, 'name' => $name, 'enabled' => $enabled === '1');
    }

    private function saveCopyOption($input)
    {
        if ($input === null) {
            return;
        }
        $module = $input['module'] === 'Employees' ? 'Users' : $input['module'];
        if (!isset($GLOBALS['beanList'][$module])) {
            throw new InvalidArgumentException('Unknown module');
        }
        $administration = new Administration();
        $administration->saveSetting(
            'sqs_copy_text',
            $module . ':' . $input['name'],
            $input['enabled'] ? '1' : '0'
        );
    }
}
