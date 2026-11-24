<?php
class UpgradeHistory
{
    public function retrieve($id)
    {
        return (object) array(
            'id' => $id,
            'id_name' => 'SQS_Copy_Text',
            'version' => $_REQUEST['previous_version'],
            'status' => isset($GLOBALS['test_history_status']) ? $GLOBALS['test_history_status'] : 'installed',
            'type' => 'module',
        );
    }
}
