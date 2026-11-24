<?php
class UpgradeHistory
{
    public function retrieve($id)
    {
        return (object) array(
            'id' => $id,
            'id_name' => 'SQS_Copy_Text',
            'version' => '1.0.5',
            'status' => isset($GLOBALS['test_history_status']) ? $GLOBALS['test_history_status'] : 'installed',
            'type' => 'module',
        );
    }
}
