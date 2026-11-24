<?php
class ModuleInstaller
{
    public static function abort($errors)
    {
        throw new RuntimeException(implode('; ', $errors));
    }
}
