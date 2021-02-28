<?php

namespace YandexMarket\Xml;

use DateTimeImmutable;
use Exception;
use modX;
use RuntimeException;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

class Generate extends PricelistWriter
{

    public function __construct(Pricelist $pricelist, modX $modX)
    {
        parent::__construct($pricelist, $modX);

        $this->openFile();
        $this->writeHeader();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function makeFile(): bool
    {
        if (!$field = $this->pricelist->getFieldByType(Field::TYPE_ROOT)) {
            $this->log('Не найден корневой элемент для прайс-листа');
            return false;
        }

        $this->pricelist->need_generate = false;
        $this->pricelist->save(); //lock для долгого экспорта
        $this->pricelist->generated_on = new DateTimeImmutable();

        $this->writeField($field);

        $this->xml->endDocument();
        $this->xml->flush();

        $this->log('Файл успешно записан');

        return $this->pricelist->save();
    }

    protected function openFile(): void
    {
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
        if (!$this->xml->openUri($pricelistFilePath)) {
            throw new RuntimeException('Can not create file '.$pricelistFilePath);
        }
    }


}