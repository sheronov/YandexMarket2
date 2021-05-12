<?php
/** @var modX $modx */

/** @var modResource $resource */
/** @var array $scriptProperties */

switch ($modx->event->name) {
    case 'OnDocFormSave':
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');
        $modx->addPackage('yandexmarket2', $corePath.'model/');

        $q = $modx->newQuery('ymPricelist');
        $q->where([
            'class'            => $resource->class_key,
            'active'           => 1,
            'generate_mode:!=' => 0
        ]);
        if ($modx->getCount('ymPricelist', $q)) {
            /** @noinspection PhpIncludeInspection */
            require_once $corePath.'vendor/autoload.php';

            foreach ($modx->getIterator('ymPricelist', $q) as $ymPricelist) {
                (new \YandexMarket\Models\Pricelist($modx, $ymPricelist))->handleResourceChanges($resource);
            }
        }
        break;
}