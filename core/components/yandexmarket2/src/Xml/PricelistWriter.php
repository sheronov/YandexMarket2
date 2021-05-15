<?php

namespace YandexMarket\Xml;

use Exception;
use modResource;
use modX;
use msProduct;
use pdoTools;
use XMLWriter;
use YandexMarket\Handlers\XmlJevix;
use YandexMarket\Models\Attribute;
use YandexMarket\Models\Category;
use YandexMarket\Models\Field;
use YandexMarket\Models\Offer;
use YandexMarket\Models\Pricelist;
use YandexMarket\Service;

abstract class PricelistWriter
{
    /** @var XMLWriter */
    protected $xml;
    /** @var XmlJevix */
    protected $jevix;
    /** @var Pricelist */
    protected $pricelist;
    /** @var null|pdoTools */
    protected $pdoTools;
    /** @var modX */
    protected $modx;
    protected $errors  = [];
    protected $start   = 0;
    protected $preview = false;

    protected $logTarget;
    protected $logLevel;
    protected $contextKey;
    protected $prepareArrays = false;
    protected $arraysGlue    = ', ';

    public function __construct(Pricelist $pricelist, modX $modx)
    {
        $this->modx = $modx;
        $this->start = microtime(true);
        if ($modx->getOption('yandexmarket2_debug_mode')) {
            $this->log('Включён режим отладки. Лог будет более подробный', false, modX::LOG_LEVEL_WARN);
        }

        $this->pricelist = $pricelist;
        $this->initializeJevix();
        $this->contextKey = $this->modx->context->key;
        $this->logTarget = $this->modx->getLogTarget();
        $this->logLevel = $this->modx->getLogLevel();
        if (!$this->initializePdoTools()) {
            $this->log('Не найден pdoTools. Fenom-обработчики будут пропущены', false, modX::LOG_LEVEL_WARN);
        }
        $this->xml = new XMLWriter();
        $this->prepareArrays = $this->modx->getOption('yandexmarket2_prepare_arrays', null, false);
        $this->arraysGlue = $this->modx->getOption('yandexmarket2_arrays_glue', null, ', ');
    }

    protected function writeHeader()
    {
        $this->xml->startDocument('1.0', 'UTF-8');
        $this->xml->setIndent(true);
        $this->xml->setIndentString("\t");
    }

    protected function writeComment(string $comment, bool $indentAfter = true)
    {
        $this->xml->writeComment($comment);
        if ($indentAfter) {
            $this->xml->setIndent(true);
        }
    }

    protected function initializeJevix()
    {
        try {
            $this->jevix = new XmlJevix();
        } catch (Exception $exception) {
            $this->errorLog('Jevix: '.$exception->getMessage());
        }
    }

    /**
     * @param  Field  $field
     * @param  array  $pls
     * @param  array  $skipTypes
     *
     * @throws Exception
     */
    protected function writeField(Field $field, array $pls = [], array $skipTypes = [])
    {
        if (!empty($skipTypes) && in_array($field->type, $skipTypes, true)) {
            if ($this->preview) {
                $this->xml->writeElement($field->name);
            }
            return;
        }
        switch ($field->type) {
            case Field::TYPE_TEXT:
            case Field::TYPE_VALUE:
            case Field::TYPE_CDATA_VALUE:
            case Field::TYPE_CATEGORY:
                $this->writeValuableField($field, $pls);
                break;
            case Field::TYPE_CATEGORIES:
                $categoriesCount = $this->writeCategoriesField($field, $pls);
                $this->log(sprintf('Записано категорий: %d', $categoriesCount));
                break;
            case Field::TYPE_OFFERS:
                $offersCount = $this->writeOffersField($field, $pls);
                $this->log(sprintf('Записано товаров: %d', $offersCount));
                break;
            case Field::TYPE_CURRENCIES:
                $this->writeCurrenciesField($field, $pls);
                break;
            case Field::TYPE_PICTURE:
                $this->writePicturesField($field, $pls);
                break;
            case Field::TYPE_ROOT:
            case Field::TYPE_SHOP:
            case Field::TYPE_PARENT:
            case Field::TYPE_OFFER:
                if (!$children = $field->getChildren()) {
                    return;
                }
                $this->xml->startElement($field->name);//
                $this->writeAttributes($field->getAttributes(), $pls);
                foreach ($children as $child) {
                    $this->writeField($child, $pls, $skipTypes);
                }
                $this->xml->endElement();
                break;
            case Field::TYPE_EMPTY:
                if ($attributes = $field->getAttributes()) {
                    $this->xml->startElement($field->name);
                    $this->writeAttributes($attributes, $pls);
                    $this->xml->endElement();
                }
                break;
            default:
                $this->errorLog("Неизвестный тип \"{$field->type}\" для поля \"{$field->name}\" (ID: {$field->id})");
        }
    }

    /**
     * Поле, которое может иметь значение
     *
     * @param  Field  $field
     * @param  array  $pls
     *
     * @throws Exception
     */
    protected function writeValuableField(Field $field, array $pls = [])
    {
        $value = null;

        switch ($field->type) {
            case Field::TYPE_TEXT:
                $value = $field->value;
                break;
            case Field::TYPE_VALUE:
            case Field::TYPE_CATEGORY:
            case Field::TYPE_CDATA_VALUE:
                $value = $this->prepareValue($this->resolveColumn($field->value, $pls), $field->handler, $pls);
                break;
        }
        if ($value === '' || $value === null) {
            //пустые значения пропускаются, даже если у них есть атрибуты
            if ($this->preview && ($field->properties['required'] ?? false)) {
                $this->writeComment("Пустое значение для обязательного элемента {$field->name}");
            }
            return;
        }

        $this->xml->startElement($field->name);

        $this->writeAttributes($field->getAttributes(), $pls);

        if ($field->type === Field::TYPE_CDATA_VALUE) {
            $this->xml->writeCdata($this->jevix ? $this->jevix->parse($value, $this->errors) : $value);
            foreach ($this->errors as $i => $error) {
                $this->errorLog('Jevix: '.$error);
                unset($this->errors[$i]);
            }
        } else {
            $this->xml->text($value);
        }

        $this->xml->endElement();
    }

    /**
     * @param  Attribute[]  $attributes
     * @param  array  $pls
     */
    protected function writeAttributes(array $attributes, array $pls = [])
    {
        foreach ($attributes as $attribute) {
            $this->writeFieldAttribute($attribute, $pls);
        }
    }

    protected function writeFieldAttribute(Attribute $attribute, array $pls = [])
    {
        $value = null;
        switch ($attribute->type) {
            case Attribute::TYPE_TEXT:
                $value = $attribute->value;
                break;
            case Attribute::TYPE_VALUE:
                $value = $this->prepareValue($this->resolveColumn($attribute->value, $pls), $attribute->handler, $pls);
                break;
            default:
                $this->errorLog("Неизвестный тип \"{$attribute->type}\" для атрибута \"{$attribute->name}\" (ID: {$attribute->id})");
        }

        if ($value !== null && $value !== '') {
            $this->xml->writeAttribute($attribute->name, $value);
        }
    }

    protected function resolveColumn(string $column, array $pls = [])
    {
        $value = null;

        if (($offer = $pls['offer'] ?? null) && ($offer instanceof Offer)) {
            //всё перехватываем для оффера
            $offer->setPricelist($this->pricelist);
            $value = $offer->get($column);
        } elseif (mb_strpos($column, '.') !== false) {
            $object = explode('.', $column)[0];
            $field = explode('.', $column, 2)[1] ?? null;
            switch (mb_strtolower($object)) {
                case 'setting':
                    $value = $this->modx->getOption($field); //можно даже в полях указывать Setting.some_setting
                    break;
                case 'pricelist':
                    $value = $this->pricelist->get($field);
                    break;
                case 'category':
                    if (($category = $pls['category'] ?? null) && $category instanceof Category) {
                        $value = $category->get($column);
                    } else {
                        $value = $pls[$column] ?? null;
                    }
                    break;
                default:
                    $value = $pls[$column] ?? null;
                    break;
            }
        } elseif (($resource = $pls['resource'] ?? null) && $resource instanceof modResource) {
            $value = $resource->get($column);
        } elseif (isset($pls[$column])) {
            $value = $pls[$column];
        }

        return $value;
    }

    protected function prepareValue(string $input = null, string $handler = null, array $pls = []): string
    {
        $value = $input;
        if ($this->prepareArrays && mb_strpos($value, '||') !== false) {
            // TODO: в общем PricelistService сделать учёт тех полей, что по типу попадают и джойнятся там же (через запоминающее свойство)
            $value = explode('||', $value);
        }
        if (!empty($handler) && $this->pdoTools) {
            if (mb_stripos(trim($handler), '@INLINE') !== 0) {
                $handler = '@INLINE '.trim($handler);
            }
            foreach ($pls as $key => $data) {
                if (is_object($data)) {
                    if ($data instanceof Offer) {
                        $pls[$key] = $data->toArray();
                        $pls[ucfirst($key)] = &$pls[$key];
                        $resource = $data->getResource();
                        $pls['resource'] = $resource->toArray();
                        $pls['Resource'] = &$pls['resource'];
                        if ($resource instanceof msProduct) {
                            $pls['data'] = $resource->loadData() ? $resource->loadData()->toArray() : null;
                            $pls['vendor'] = $resource->loadVendor() ? $resource->loadVendor()->toArray() : null;
                            $pls['Data'] = &$pls['data'];
                            $pls['Vendor'] = &$pls['vendor'];
                        }
                        $pls['option'] = [];
                        $pls['tv'] = [];
                        $pls['category'] = [
                            'id' => $pls['parent'] ?? 0
                        ];
                        $pls['categoryTV'] = [];
                        foreach ($pls[$key] as $k => $val) {
                            if (mb_strpos($k, 'option.') === 0) {
                                $pls['option'][mb_substr($k, mb_strlen('option.'))] = $val;
                            } elseif (mb_strpos($k, 'tv.') === 0) {
                                $pls['tv'][mb_substr($k, mb_strlen('tv.'))] = $val;
                            } elseif (mb_strpos($k, 'category.') === 0) {
                                $pls['category'][mb_substr($k, mb_strlen('category.'))] = $val;
                            } elseif (mb_strpos($k, 'categorytv.') === 0) {
                                $pls['categoryTV'][mb_substr($k, mb_strlen('categorytv.'))] = $val;
                            }
                        }

                        $pls['Parent'] = &$pls['category'];
                        $pls['Category'] = &$pls['category'];
                        $pls['ParentTV'] = &$pls['categoryTV'];
                        $pls['CategoryTV'] = &$pls['categoryTV'];
                        $pls['categorytv'] = &$pls['categoryTV'];
                        $pls['Option'] = &$pls['option'];
                        $pls['TV'] = &$pls['tv'];
                        $pls['Tv'] = &$pls['tv'];
                    } elseif (method_exists($data, 'toArray')) {
                        $pls[$key] = $data->toArray();
                    } else {
                        unset($pls[$key]);
                    }
                }
            }
            $value = $this->pdoTools->getChunk($handler, array_merge(
                Service::getSitePaths($this->modx), //{site_url} и т.д.
                $pls,
                [
                    'input'     => $value,
                    'pricelist' => $this->pricelist->toArray()
                ]
            ), true);
        }

        return is_array($value) ? implode($this->arraysGlue, $value) : ($value ?? '');
    }

    protected function initializePdoTools(): bool
    {
        $pdoTools = $this->modx->getService('pdoTools');
        if ($pdoTools && $pdoTools instanceof pdoTools) {
            $this->pdoTools = $pdoTools;
            return true;
        }
        return false;
    }

    protected function writeCurrenciesField(Field $field, array $pls = [])
    {
        $currencyElement = $field->getProperties()['child'] ?? 'currency';
        $rateAttr = $field->getProperties()['rate'] ?? 'rate';
        $rateValue = $field->getProperties()['rate_value'] ?? '1';

        if (!empty($field->value)) {
            $currencies = json_decode($field->value, true);

            if (!empty($currencies)) {
                $this->xml->startElement($field->name);
                $this->writeAttributes($field->getAttributes(), $pls);
                foreach ($currencies as $i => $currency) {
                    $this->xml->startElement($currencyElement);
                    $this->xml->writeAttribute('id', $currency);
                    if (!$i) {
                        $this->xml->writeAttribute($rateAttr, $rateValue);
                    }
                    $this->xml->endElement();
                }
                $this->xml->endElement();
            }
        }
    }

    /**
     * @param  Field  $field
     * @param  array  $pls
     *
     * @throws Exception
     */
    protected function writeCategoriesField(Field $field, array $pls = []): int
    {
        $count = 0;
        $this->xml->startElement($field->name);
        $this->writeAttributes($field->getAttributes(), $pls);

        if (($children = $field->getChildren()) && $categoryField = reset($children)) {
            $categories = $this->pricelist->categoriesGenerator();
            foreach ($categories as $category) {
                $count++;
                $pls['category'] = $category;
                $resource = $category->getResource();
                if ($resource && !$resource->parent) {
                    $resource->parent = null;
                }
                $pls['resource'] = $resource;
                $this->writeField($categoryField, $pls);
            }
        } else {
            $this->errorLog("Пустой список категорий в поле \"{$field->name}\" (ID: {$field->id})");
        }

        $this->xml->endElement();

        return $count;
    }

    /**
     * @param  Field  $field
     * @param  array  $pls
     *
     * @throws Exception
     */
    protected function writeOffersField(Field $field, array $pls = []): int
    {
        $count = 0;
        $this->xml->startElement($field->name);
        $this->writeAttributes($field->getAttributes(), $pls);

        if (($children = $field->getChildren()) && $offerField = reset($children)) {
            $offers = $this->pricelist->offersGenerator([
                // TODO: тут в pricelistService более правильную сортировку добавить с учётом query
                'sortBy' => [
                    // "context_key = 'web'" => 'DESC',
                    // 'context_key'         => 'ASC',
                    // 'id'                  => 'ASC',
                ]
            ]);

            $contextKey = null;
            foreach ($offers as $offer) {
                $count++;
                if ($contextKey !== $offer->get('context_key')) {
                    $contextKey = $offer->get('context_key');
                    $this->switchContext($contextKey);
                }
                $pls['offer'] = $offer;
                $this->writeField($offerField, $pls);
            }

            if ($this->modx->context->key !== $this->contextKey) {
                $this->switchContext($this->contextKey);
            }
        } else {
            $this->errorLog("Пустой список товаров в поле \"{$field->name}\" (ID: {$field->id})");
        }

        $this->xml->endElement();
        return $count;
    }

    protected function switchContext(string $contextKey)
    {
        $this->modx->switchContext($contextKey);
        $this->modx->setLogLevel($this->logLevel);
        $this->modx->setLogTarget($this->logTarget);
    }

    /**
     * @param  Field  $field
     * @param  array  $pls
     *
     * @throws Exception
     */
    protected function writePicturesField(Field $field, array $pls = [])
    {
        if (($offer = $pls['offer'] ?? null) && $offer instanceof Offer && $pictures = $offer->get($field->value)) {
            foreach (explode('||', $pictures) as $i => $picture) {
                if (($limit = $field->properties['count'] ?? 0) && $i >= $limit) {
                    break;
                }
                $tmpField = new Field($this->modx);
                $tmpField->name = $field->name;
                $tmpField->value = Service::preparePath($this->modx, '{images_url}/'.$picture, true);
                $tmpField->type = Field::TYPE_TEXT;
                $this->writeField($tmpField, $pls);
            }
        }
    }

    protected function log(string $message, bool $withTime = true, int $level = modX::LOG_LEVEL_INFO)
    {
        if ($withTime) {
            $message = sprintf("%2.4f s: %s", (microtime(true) - $this->start), $message);
        }
        $this->modx->log($level, $message, '', 'YandexMarket2');
    }

    protected function errorLog(string $message)
    {
        $this->log($message, false, modX::LOG_LEVEL_ERROR);
    }

}