<?php

namespace YandexMarket\Handlers;

use Iterator;
use msProduct;
use PDO;
use PDOStatement;
use ReflectionClass;
use xPDO\xPDO;
use xPDO\om\xPDOObject;
use xPDO\om\xPDOQuery;
use YandexMarket\Service;

class xPDOLazyIterator implements Iterator
{
    /** @var xPDO|\xPDO */
    private $xpdo    = null;
    private $index   = 0;
    private $current = null;
    /** @var null|PDOStatement */
    private $stmt  = null;
    private $class = null;
    private $alias = null;
    /** @var null|int|array|xPDOQuery|\xPDOQuery */
    private $criteria     = null;
    private $criteriaType = 'xPDOQuery';
    private $cacheFlag    = false;

    protected $hasMS2          = false;
    protected $msProductFields = [];

    /**
     * Construct a new xPDOIterator instance (do not call directly).
     *
     * @param  xPDO|\xPDO &$xpdo  A reference to a valid xPDO instance.
     * @param  array  $options  An array of options for the iterator.
     *
     * @see xPDO::getIterator()
     */
    function __construct(&$xpdo, array $options = [])
    {
        $this->xpdo =& $xpdo;
        if (isset($options['class'])) {
            $this->class = $this->xpdo->loadClass($options['class']);
        }
        if (isset($options['alias'])) {
            $this->alias = $options['alias'];
        } else {
            $this->alias = $this->class;
        }
        if (isset($options['cacheFlag'])) {
            $this->cacheFlag = $options['cacheFlag'];
        }
        if (array_key_exists('criteria', $options) && is_object($options['criteria'])) {
            $this->criteria = $options['criteria'];
        } elseif (!empty($this->class)) {
            $criteria = array_key_exists('criteria', $options) ? $options['criteria'] : null;
            $this->criteria = $this->xpdo->getCriteria($this->class, $criteria, $this->cacheFlag);
        }
        if (!empty($this->criteria)) {
            $this->criteriaType = $this->xpdo->getCriteriaType($this->criteria);
            if ($this->criteriaType === 'xPDOQuery') {
                $this->class = $this->criteria->getClass();
                $this->alias = $this->criteria->getAlias();
            }
        }
        if ($this->hasMS2 = Service::hasMiniShop2()) {
            $this->msProductFields = array_merge($this->xpdo->getFieldMeta('msProductData'),
                $this->xpdo->getFieldMeta('msProduct'));
        }
    }

    public function rewind()
    {
        $this->index = 0;
        if (!empty($this->stmt)) {
            $this->stmt->closeCursor();
        }
        $this->stmt = $this->criteria->prepare();
        $tstart = microtime(true);
        if ($this->stmt && $this->stmt->execute()) {
            $this->xpdo->queryTime += microtime(true) - $tstart;
            $this->xpdo->executedQueries++;
            $this->fetch();
        } elseif ($this->stmt) {
            $this->xpdo->queryTime += microtime(true) - $tstart;
            $this->xpdo->executedQueries++;
        }
    }

    public function current()
    {
        return $this->current;
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        $this->fetch();
        if (!$this->valid()) {
            $this->index = null;
        } else {
            $this->index++;
        }
        return $this->current();
    }

    public function valid()
    {
        return ($this->current !== null);
    }

    /**
     * Fetch the next row from the result set and set it as current.
     * Calls the _loadInstance() method for the specified class, so it properly
     * inherits behavior from xPDOObject derivatives.
     */
    protected function fetch()
    {
        $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($row) && !empty($row)) {
            $instance = $this->loadXPDOInstance($row);

            if ($instance === null) {
                $this->fetch();
            } else {
                $this->current = $instance;
            }
        } else {
            $this->current = null;
        }
    }

    /**
     * @param  array  $row
     *
     * @return xPDOObject|\xPDOObject
     * @throws \ReflectionException
     */
    protected function loadXPDOInstance(array $row = [])
    {
        $rowPrefix = '';

        $instance = $this->xpdo->newObject($this->class);
        if ($instance instanceof xPDOObject || $instance instanceof \xPDOObject) {
            $pk = $this->xpdo->getPK($this->class);
            if ($pk) {
                if (is_array($pk)) {
                    $pk = reset($pk);
                }
                if (isset($row["{$this->alias}_{$pk}"])) {
                    $rowPrefix = $this->alias.'_';
                } elseif (isset($row["{$this->class}_{$pk}"])) {
                    $rowPrefix = $this->class.'_';
                }
            } elseif (stripos(key($row), $this->alias.'_') === 0) {
                $rowPrefix = $this->alias.'_';
            } elseif (stripos(key($row), $this->class.'_') === 0) {
                $rowPrefix = $this->class.'_';
            }

            if ($this->hasMS2 && $this->class === 'msProduct') {
                $reflectionClass = new ReflectionClass('msProduct');

                /** @var \msProductData $msProductData */
                $msProductData = $this->xpdo->newObject('msProductData');
                $msProductData->fromArray($row, 'data.', true, true);

                $dataProperty = $reflectionClass->getProperty('Data');
                $dataProperty->setAccessible(true);
                $dataProperty->setValue($instance, $msProductData);
                $instance->_relatedObjects['Data'] = $msProductData;

                $row = array_filter($row, function (string $key) {
                    return mb_strpos($key, 'data.') !== 0;
                }, ARRAY_FILTER_USE_KEY);

                /** @var \msVendor $msVendor */
                $msVendor = $this->xpdo->newObject('msVendor');
                if (array_key_exists('vendor.id', $row)) {
                    $msVendor->fromArray($row, 'vendor.', true, true);
                    $row = array_filter($row, function (string $key) {
                        return mb_strpos($key, 'vendor.') !== 0;
                    }, ARRAY_FILTER_USE_KEY);
                }
                $vendorProperty = $reflectionClass->getProperty('Vendor');
                $vendorProperty->setAccessible(true);
                $vendorProperty->setValue($instance, $msVendor);
                $instance->_relatedObjects['Vendor'] = $msVendor;

                //чтобы значения приджойненных полей сохранились в полях ресурса, а не товара ms2
                if ($joinedFields = array_diff_key($row, $this->msProductFields)) {
                    $originalFieldsProperty = $reflectionClass->getProperty('_originalFieldMeta');
                    $originalFieldsProperty->setAccessible(true);
                    $originalFieldMeta = $originalFieldsProperty->getValue($instance);
                    foreach ($joinedFields as $key => $ignore) {
                        $originalFieldMeta[$key] = [
                            'dbtype'  => 'text',
                            'phptype' => 'string',
                            'null'    => true,
                            'default' => '',
                        ];
                    }
                    $originalFieldsProperty->setValue($instance, $originalFieldMeta);
                }
            }

            $instance->_lazy = array_keys($instance->_fieldMeta);
            $instance->fromArray($row, $rowPrefix, true, true);
            $instance->_dirty = [];
            $instance->_new = false;
        }
        return $instance;
    }
}
