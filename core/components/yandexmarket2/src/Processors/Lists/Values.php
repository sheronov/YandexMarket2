<?php

namespace YandexMarket\Processors\Lists;

use MODX\Revolution\Processors\Processor;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AValuesProcessor extends \modProcessor { }
} else {
    abstract class AValuesProcessor extends Processor { }
}

class Values extends AValuesProcessor
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
