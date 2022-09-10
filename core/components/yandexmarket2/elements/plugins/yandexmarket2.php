<?php
/** @var MODX\Revolution\modX|modX $modx */
/** @var MODX\Revolution\modResource|modResource $resource */
/** @var array $scriptProperties */

switch ($modx->event->name) {
    case 'OnDocFormSave':
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');
        $isMODX3 = class_exists('MODX\Revolution\modX');
        if (!$isMODX3) {
            $modx->addPackage('yandexmarket2', $corePath.'model/');
        }

        $q = $modx->newQuery($isMODX3 ? YandexMarket\Model\YmPricelist::class : YmPricelist::class)
            ->where([
                'class'            => $resource->class_key,
                'active'           => 1,
                'generate_mode:!=' => 0
            ]);
        if ($modx->getCount($q->getClass(), $q)) {
            if (!$isMODX3) {
                require_once $corePath.'vendor/autoload.php';
            }

            foreach ($modx->getIterator($q->getClass(), $q) as $ymPricelist) {
                $pricelist = new \YandexMarket\Models\Pricelist($modx, $ymPricelist);
                (new \YandexMarket\QueryService($pricelist, $modx))->handleResourceChanges($resource);
            }
        }
        break;
}
