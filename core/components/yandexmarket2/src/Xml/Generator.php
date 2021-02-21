<?php

namespace YandexMarket\Xml;

use YandexMarket\Models\Pricelist;
use YandexMarket\Service;

class Generator
{
    protected $writer;
    protected $pricelist;
    protected $service;
    protected $log = [];
    protected $start;

    public function __construct(Pricelist $pricelist)
    {
        $this->pricelist = $pricelist;
        $this->writer = new PricelistWriter();
        $this->service = new Service($pricelist->modX());
        $this->start = microtime(true);
    }

    public function makeXml(): bool
    {
        return true;
    }

    /**
     * @param  bool  $asString
     *
     * @return string|array
     */
    public function getLog(bool $asString = true)
    {
        return $asString ? implode(PHP_EOL, $this->log) : $this->log;
    }

    protected function log(string $message, bool $withTime = true): void
    {
        if ($withTime) {
            $message = sprintf("%2.4f s: %s", (microtime(true) - $this->start), $message);
        }
        $this->log[] = $message;
    }
}