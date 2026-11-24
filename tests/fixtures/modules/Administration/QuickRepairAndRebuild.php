<?php
class RepairAndClear
{
    public static $calls = 0;

    public function repairAndClearAll($commands, $modules, $clearVardefs, $showOutput)
    {
        self::$calls++;
    }
}
