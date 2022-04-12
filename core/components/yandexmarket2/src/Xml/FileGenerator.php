<?php

namespace YandexMarket\Xml;

use Exception;
use RuntimeException;
use YandexMarket\Models\Field;
use YandexMarket\QueryService;

class FileGenerator extends Writer
{

    public function __construct(QueryService $pricelistService)
    {
        parent::__construct($pricelistService);

        $this->openFile();
        $this->writeHeader();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function makeFile(): bool
    {
        if (!$field = $this->pricelistService->getFieldByType(Field::TYPE_ROOT)) {
            throw new RuntimeException('Could not find the ROOT element (type='.Field::TYPE_ROOT.')');
        }

        $this->modx->invokeEvent('ym2OnBeforePricelistGenerate',[
           'pricelist' => &$this->pricelist
        ]);

        $this->pricelist->need_generate = false;
        $this->pricelist->generated_on = date('Y-m-d H:i:s');
        $this->pricelist->save(); //lock для долгого экспорта

        $this->writeField($field);

        $this->xml->endDocument();
        $this->xml->flush();

        $this->log('Файл успешно сформирован');

        $saved =  $this->pricelist->save();

        $this->modx->invokeEvent('ym2OnAfterPricelistGenerate',[
            'pricelist' => &$this->pricelist
        ]);

        return $saved;
    }

    /**
     * @return void
     */
    protected function openFile()
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
