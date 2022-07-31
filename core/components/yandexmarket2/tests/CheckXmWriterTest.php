<?php

namespace YandexMarket\Tests;

class CheckXmWriterTest extends MODxTestCase {

    public function testLexicon() {
        $name = 'YandexMarket2';

        self::assertEquals($name, $this->modx->lexicon('yandexmarket2'));
    }

}
