<?php

namespace YandexMarket\Xml;

use RuntimeException;
use YandexMarket\Models\Field;
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
        $this->writer = new PricelistWriter($pricelist);
        $this->service = new Service($pricelist->modX());
        $this->start = microtime(true);

        $filesPath = $this->service->getConfig()['filesPath'];
        if (!is_dir($filesPath) && !mkdir($filesPath, 0755, true) && !is_dir($filesPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $filesPath));
        }

        $pricelistFilePath = $filesPath.$this->pricelist->file;
        if (file_exists($pricelistFilePath)) {
            $this->log('Файл '.$pricelistFilePath.' уже существует и будет перезаписан');
        } else {
            $this->log('Запущен процесс записи в файл '.$pricelistFilePath);
        }
        if (!$this->writer->openFile($pricelistFilePath)) {
            throw new RuntimeException('Can not create file '.$pricelistFilePath);
        }
        $this->writer->writeHeader();
    }

    public function makeFile(): bool
    {
        if (!$field = $this->pricelist->getFieldByType(Field::TYPE_ROOT)) {
            $this->log('Не найден корневой элемент для прайс-листа');
            return false;
        }

        $this->writer->writeField($field);

        $this->writer->closeDocument();
        $this->log('Файл успешно записан. Техническая информация: '.print_r(Service::debugInfo($this->pricelist->modX()),
                true));
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