<?php

class ymFieldAttribute extends xPDOSimpleObject
{
    public function save($cacheFlag = null)
    {
        if (($saved = parent::save($cacheFlag)) && ($field = $this->getOne('Field'))) {
            $field->save(); //for pricelist touch
        }
        return $saved;
    }

    public function remove(array $ancestors = [])
    {
        $field = $this->getOne('Field');
        $removed = parent::remove($ancestors);

        if ($field && $removed) {
            $field->save(); //for pricelist touch
        }
        return $removed;
    }
}