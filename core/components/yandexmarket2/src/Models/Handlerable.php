<?php

namespace YandexMarket\Models;

trait Handlerable
{

    /**
     * @param array|string $value
     *
     * @return Handlerable
     */
    public function setDefaultValue($value): self
    {
        if (is_array($value) && array_values($value) !== $value) {
            if (isset($value['handler'])) {
                $this->handler = $value['handler'];
            }
            if (isset($value['value'])) {
                $this->value = is_array($value['value']) ? json_encode($value['value']) : $value['value'];
            }
        } else {
            $this->value = is_array($value) ? json_encode($value) : $value;
        }
        return $this;
    }
}