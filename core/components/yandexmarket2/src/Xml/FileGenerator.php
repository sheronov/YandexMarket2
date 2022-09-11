<?php

namespace YandexMarket\Xml;

use Exception;
use RuntimeException;
use YandexMarket\Models\Field;
use YandexMarket\QueryService;

class FileGenerator extends Writer
{

    protected $tmpSuffix = '.tmp';
    protected $pricelistPath;

    public function __construct(QueryService $pricelistService)
    {
        parent::__construct($pricelistService);

        $this->pricelistPath = $this->getPricelistPath();
        if (!$this->xml->openUri($this->pricelistPath.$this->tmpSuffix)) {
            throw new RuntimeException("Could not open file {$this->pricelistPath} for writing");
        }
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

        $this->modx->invokeEvent('ym2OnBeforePricelistGenerate', [
            'pricelist' => &$this->pricelist
        ]);

        $this->pricelist->need_generate = false;
        $this->pricelist->generated_on = date('Y-m-d H:i:s');
        $this->pricelist->save(); //lock для долгого экспорта

        $this->writeField($field); // это будет писать вглубь всё

        return $this->closeFile();
    }

    protected function getPricelistPath(): string
    {
        $filesPath = $this->pricelist->getFilePath(false);
        if (!is_dir($filesPath) && !mkdir($filesPath, 0755, true) && !is_dir($filesPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $filesPath));
        }

        $pricelistFilePath = $this->pricelist->getFilePath(true);
        if (file_exists($pricelistFilePath)) {
            $this->log(sprintf($this->lexicon('ym2_xml_file_existed'), basename($pricelistFilePath)));
        } else {
            $this->log($this->lexicon('ym2_xml_file_writing') .' '.$pricelistFilePath);
        }

        return $pricelistFilePath;
    }

    protected function closeFile(): bool
    {
        $this->xml->endDocument();
        $this->xml->flush();

        if (rename($this->pricelistPath.$this->tmpSuffix, $this->pricelistPath)) {
            $this->log(sprintf($this->lexicon('ym2_xml_file_written'), basename($this->pricelistPath)));
        } else {
            $this->errorLog(sprintf('Could not rename file %s to %s', $this->pricelistPath.$this->tmpSuffix,
                $this->pricelistPath));
            // if it's the Windows os - the file must be writable!
        }

        $saved = $this->pricelist->save();

        $this->modx->invokeEvent('ym2OnAfterPricelistGenerate', [
            'pricelist' => &$this->pricelist
        ]);

        return $saved;
    }

}
