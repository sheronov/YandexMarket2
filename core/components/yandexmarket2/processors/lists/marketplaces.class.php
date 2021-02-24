<?php

use YandexMarket\Service;

/** @noinspection PhpIncludeInspection */
require_once(dirname(__FILE__, 3).'/vendor/autoload.php');

class ymMarketplacesProcessor extends modProcessor
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

return ymMarketplacesProcessor::class;