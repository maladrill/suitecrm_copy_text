<?php
if (!defined('sugarEntry') || !sugarEntry) {
    return;
}

$hook_array['after_ui_frame'][] = array(
    100,
    'Render enabled copy buttons in Detail View',
    'custom/include/SQS_CopyText/Hook.php',
    'SQS_CopyText_Hook',
    'render'
);
