<?php
/** @var \MODX\Revolution\modX $modx */

/** @var \MODX\Revolution\modResource $resource */
/** @var array $scriptProperties */


switch ($modx->event->name) {
    case 'OnDocFormSave':
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');
        // $modx->addPackage('yandexmarket2', $corePath.'Model/');

        $q = $modx->newQuery(YandexMarket\Model\YmPricelist::class);
        $q->where([
            'class'            => $resource->class_key,
            'active'           => 1,
            'generate_mode:!=' => 0
        ]);
        if ($modx->getCount(YandexMarket\Model\YmPricelist::class, $q)) {
            foreach ($modx->getIterator(YandexMarket\Model\YmPricelist::class, $q) as $ymPricelist) {
                $pricelist = new \YandexMarket\Models\Pricelist($modx, $ymPricelist);
                (new \YandexMarket\QueryService($pricelist, $modx))->handleResourceChanges($resource);
            }
        }
        break;
}
