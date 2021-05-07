<?php

/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH.'model/modx/processors/system/console.class.php';

class ymXmlLogProcessor extends modConsoleProcessor
{
    public function initialize()
    {
        $this->setDefaultProperties([
            'register'      => 'yandexmarket2',
            'show_filename' => 0,
            'format'        => 'html_log',
            'clear'         => false
        ]);
        return parent::initialize();
    }
}

return ymXmlLogProcessor::class;