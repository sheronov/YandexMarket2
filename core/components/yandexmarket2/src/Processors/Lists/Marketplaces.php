<?php

namespace YandexMarket\Processors\Lists;

use MODX\Revolution\Processors\Processor;
use YandexMarket\Service;

if (!Service::isMODX3()) {
    abstract class AMarketplacesProcessor extends \modProcessor { }
} else {
    abstract class AMarketplacesProcessor extends Processor { }
}

class Marketplaces extends AMarketplacesProcessor
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
