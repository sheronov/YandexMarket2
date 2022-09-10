<?php

namespace YandexMarket\Model;

trait PricelistTouch
{
    public function save($cacheFlag = null)
    {
        $saved = parent::save($cacheFlag);

        if ($saved && ($pricelist = $this->getOne('Pricelist')) && $pricelist->get('active')) {
            /** @var YmPricelist|\YmPricelist $pricelist */
            $pricelist->set('need_generate', true);
            $pricelist->save();
        }

        return $saved;
    }

    public function remove(array $ancestors = [])
    {
        $removed = parent::remove($ancestors);

        if ($removed && ($pricelist = $this->getOne('Pricelist')) && $pricelist->get('active')) {
            /** @var YmPricelist|\YmPricelist $pricelist */
            $pricelist->set('need_generate', true);
            $pricelist->save();
        }

        return $removed;
    }
}
