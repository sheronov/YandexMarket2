<?php

namespace YandexMarket;

use modResource;
use modX;
use msProduct;
use xPDO;
use xPDOQuery;
use YandexMarket\Models\Category;
use YandexMarket\Models\Field;
use YandexMarket\Models\Pricelist;

class Service
{
    protected $modx;
    protected $config       = [];
    public    $hasMS2       = false;
    public    $pricePlugins = false;

    public function __construct(modX $modx, array $config = [])
    {
        $this->modx = $modx;
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');
        $assetsUrl = $modx->getOption('yandexmarket2_assets_url', null,
            $modx->getOption('assets_url').'components/yandexmarket2/');

        $this->config = array_merge([
            'corePath'       => $corePath,
            'modelPath'      => $corePath.'model/',
            'processorsPath' => $corePath.'processors/',
            'assetsUrl'      => $assetsUrl,
            'mgrAssetsUrl'   => $assetsUrl.'mgr/',
        ], $config);

        $this->modx->addPackage('yandexmarket2', $this->config['modelPath']);
        $this->modx->lexicon->load('yandexmarket2:default');

        if ($this->hasMS2 = self::hasMiniShop2()) {
            $c = $modx->newQuery('modPluginEvent', ['event:IN' => ['msOnGetProductPrice', 'msOnGetProductWeight']]);
            $c->innerJoin('modPlugin', 'modPlugin', 'modPlugin.id = modPluginEvent.pluginid');
            $c->where('modPlugin.disabled = 0');
            $this->pricePlugins = $modx->getOption('ms2_price_snippet', null, false, true)
                || $modx->getCount('modPluginEvent', $c);
            // $modx->addPackage('minishop2', $modx->getOption('minishop2_core_path', null,
            //         $modx->getOption('core_path').'components/minishop2/').'model/');
        }
    }

    public static function debugInfo(xPDO $xpdo): ?array
    {
        if (!$xpdo->getOption('yandexmarket_debug_mode')) {
            return null;
        }
        return [
            'queries'   => $xpdo->executedQueries,
            'queryTime' => sprintf("%2.4f s", $xpdo->queryTime),
            'totalTime' => sprintf("%2.4f s", (microtime(true) - $xpdo->startTime)),
            'memory'    => number_format(memory_get_usage(true) / 1024, 0, ",", " ").' kb'
        ];
    }

    protected static function hasMiniShop2(): bool
    {
        return file_exists(MODX_CORE_PATH.'components/minishop2/model/minishop2/msproduct.class.php');
    }

    // TODO: тут получить товары со всеми возможными опциями и тв полями
    public function queryForPricelist(Pricelist $pricelist, array $data = [])
    {
        $class = $this->hasMS2 ? msProduct::class : modResource::class;
        $q = $this->modx->newQuery($class);
        $q->select($this->modx->getSelectColumns($class, $class, ''));

        if (!empty($pricelist->getCategories())) {
            $q->where([
                'parent:IN' => array_map(static function (Category $category) {
                    return $category->resource_id;
                }, $pricelist->getCategories())
            ]);
        }

        if ($this->hasMS2) {
            $q->leftJoin('msProductData', 'Data');
            $q->leftJoin('msVendor', 'Vendor', 'Data.vendor=Vendor.id');
            $q->select($this->modx->getSelectColumns('msProductData', 'Data', '', ['id'], true));
            $q->select($this->modx->getSelectColumns('msVendor', 'Vendor', 'vendor.', ['id'], true));
        }

        // TODO: тут изучить все значения (и их хэнделры в выбранных полях)
        $this->joinPricelistFields($q, $pricelist->getFields(true));

        return $q;
    }

    /**
     * @param  xPDOQuery  $q
     * @param  Field[]  $fields
     *
     * @return xPDOQuery
     */
    protected function joinPricelistFields(xPDOQuery $q, array $fields): xPDOQuery
    {
        $classKeys = [];

        foreach ($fields as $field) {
            if (in_array($field->type, [Field::TYPE_OPTION, Field::TYPE_CURRENCIES, Field::TYPE_CATEGORIES], true)) {
                continue;
            }
            if (!empty($field->value) && mb_strpos($field->value, '.') !== false) {
                [$class, $key] = explode('.', $field->value, 2);
                if (!isset($classKeys[$class])) {
                    $classKeys[$class] = [];
                }
                if (!in_array($key, $classKeys[$class], true)) {
                    $classKeys[$class][] = $key;
                }
            }
            // TODO if(!empty($field->handler)) с регулярками найти {$Option.color} или {$TV.size} или [[+tv.size]]
        }

        $this->modx->log(1, 'class keys '.var_export($classKeys, true));

        // TODO: приджойнить другие классы (с моделями что-то придумать нужно)
        foreach ($classKeys as $class => $keys) {
            switch ($class) {
            }
        }

        return $q;
    }

}