<?php

namespace YandexMarket\Model;

trait PricelistTouch
{
    public function save($cacheFlag = null)
    {
        $saved = parent::save($cacheFlag);

        if ($saved && $pricelist = $this->getOne('Pricelist')) {
            /** @var ymPricelist $pricelist */
            $pricelist->touch();
        }

        return $saved;
    }

    public function remove(array $ancestors = [])
    {
        /** @var ymPricelist $pricelist */
        $pricelist = $this->getOne('Pricelist');
        $removed = parent::remove($ancestors);

        if ($removed && $pricelist) {
            $pricelist->touch();
        }

        return $removed;
    }
}
