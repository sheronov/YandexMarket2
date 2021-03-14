<?php

/** @noinspection PhpIncludeInspection */

use YandexMarket\Service;

require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymListValuesProcessor extends modProcessor
{

    /** @var Service */
    protected $service;

    public function initialize(): bool
    {
        $this->service = new Service($this->modx);
        return parent::initialize();
    }

    public function process(): string
    {
        return $this->outputArray($this->service->getValues($this->getProperty('column')));
    }
}

return ymListValuesProcessor::class;