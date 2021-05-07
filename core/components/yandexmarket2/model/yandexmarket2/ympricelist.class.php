<?php

class ymPricelist extends xPDOSimpleObject
{
    public function touch()
    {
        // если прайслист активен и хоть раз был сгенерирован - то ставим флаг
        if ($this->get('active') && $this->get('generated_on')) {
            $this->set('need_generate', true);
            $this->save();
        }
    }
}