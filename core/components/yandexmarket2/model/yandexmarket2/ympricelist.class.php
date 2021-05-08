<?php

class ymPricelist extends xPDOSimpleObject
{
    public function touch()
    {
        // если прайслист активен, то ставим флаг на обновление
        if ($this->get('active')) {
            $this->set('need_generate', true);
            $this->save();
        }
    }
}