<?php
class Administration
{
    public $settings = array();

    public function retrieveSettings($category)
    {
        $this->settings = array(
            'sqs_copy_text_Accounts:reference_c' => '1',
            'sqs_copy_text_Accounts:other_c' => '0',
            'sqs_copy_text_Contacts:secret_c' => '1',
        );
    }

    public function saveSetting($category, $key, $value)
    {
        $GLOBALS['saved_setting'] = array($category, $key, $value);
    }
}
