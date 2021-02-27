<?php

namespace YandexMarket\Xml;

use DateTimeImmutable;
use modX;
use RuntimeException;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

class FileGenerator
{
    protected $writer;
    protected $pricelist;
    protected $service;
    protected $log = [];
    protected $start;

    public function __construct(Pricelist $pricelist, modX $modX)
    {
        $this->pricelist = $pricelist;
        $this->writer = new PricelistWriter($pricelist, $modX);
        $this->start = microtime(true);

        $filesPath = $this->pricelist->getFilePath(false);
        if (!is_dir($filesPath) && !mkdir($filesPath, 0755, true) && !is_dir($filesPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $filesPath));
        }

        $pricelistFilePath = $this->pricelist->getFilePath(true);
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

        $this->pricelist->need_generate = false;
        $this->pricelist->save(); //lock для долгого экспорта
        $this->pricelist->generated_on = new DateTimeImmutable();

        $this->writer->writeField($field);

        $this->writer->closeDocument();
        $this->log('Файл успешно записан. Техническая информация: '.$this->writer->debugInfo(true));

        return $this->pricelist->save();
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