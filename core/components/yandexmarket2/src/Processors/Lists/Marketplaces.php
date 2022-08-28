<?php

namespace YandexMarket\Processors\Lists;

use MODX\Revolution\Processors\Processor;
use YandexMarket\Service;

class Marketplaces extends Processor
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
        return $this->outputArray($this->service->getMarketplaces());
    }

}
