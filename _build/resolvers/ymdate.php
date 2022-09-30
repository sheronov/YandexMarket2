<?php

/** @var xPDO\Transport\xPDOTransport|xPDOTransport $transport */

/** @var array $options */
/** @var modX|\MODX\Revolution\modX $modx */

if (!defined('MODX3')) {
    define('MODX3', class_exists('MODX\Revolution\modX'));
}

if ($transport->xpdo) {
    $modx = $transport->xpdo;
    switch ($options[MODX3 ? \xPDO\Transport\xPDOTransport::PACKAGE_ACTION : xPDOTransport::PACKAGE_ACTION]) {
        case MODX3 ? \xPDO\Transport\xPDOTransport::ACTION_UPGRADE : xPDOTransport::ACTION_UPGRADE:
            if (MODX3) {
                $modx->addPackage('YandexMarket\Model', MODX_CORE_PATH.'components/yandexmarket2/src/', null,
                    'YandexMarket\\');
            } else {
                $modx->addPackage('yandexmarket2', MODX_CORE_PATH.'components/yandexmarket2/model/');
            }

            $aq = $modx->newQuery(MODX3 ? \YandexMarket\Model\YmFieldAttribute::class : YmFieldAttribute::class)
                ->where([
                    'name'       => 'date',
                    'value'      => 'Pricelist.generated_on',
                    'type'       => 1,
                    'handler:IS' => null
                ]);
            $attributes = $modx->getIterator($aq->getClass(), $aq);
            foreach ($attributes as $attribute) {
                $attribute->set('handler', '{$input | date: "Y-m-d\TH:i:sP"}');
                $attribute->save();
            }

            break;
    }
}
return true;
