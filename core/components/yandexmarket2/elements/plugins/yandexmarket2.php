<?php
/** @var modResource $resource */

/** @var modX $modx */

switch ($modx->event->name) {
    case 'OnDocFormSave':
        $corePath = $modx->getOption('yandexmarket2_core_path', null,
            $modx->getOption('core_path').'components/yandexmarket2/');
        $modx->addPackage('yandexmarket2', $corePath.'model/');

        $q = $modx->newQuery('ymPricelist');
        $q->where([
            'active'            => 1,
            'class'             => $resource->class_key,
            'generate_mode:!=' => 0
        ]);
        if ($modx->getCount('ymPricelist', $q)) {
            /** @noinspection PhpIncludeInspection */
            require_once $corePath.'vendor/autoload.php';

            foreach ($modx->getIterator('ymPricelist', $q) as $ymPricelist) {
                $pricelist = new \YandexMarket\Models\Pricelist($modx, $ymPricelist);
                if (!$pricelist->isOfferFits($resource->id)) {
                    continue;
                }

                if ($pricelist->generate_mode === \YandexMarket\Models\Pricelist::GENERATE_MODE_AFTER_SAVE) {
                    $generator = new \YandexMarket\Xml\Generate($pricelist, $modx);
                    try {
                        $generator->makeFile();
                    } catch (Exception $e) {
                        $modx->log(modX::LOG_LEVEL_ERROR, '[YandexMarket2] '.$e->getMessage());
                        $pricelist->need_generate = true;
                        $pricelist->save();
                    }
                } else {
                    $pricelist->need_generate = true;
                    $pricelist->save();
                }
            }
        }
        break;
}