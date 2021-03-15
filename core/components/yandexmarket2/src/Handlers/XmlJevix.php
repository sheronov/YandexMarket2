<?php

namespace YandexMarket\Handlers;

use Exception;
use Jevix;

class XmlJevix extends Jevix
{
    /**
     * XmlJevix constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        //<obr> it's an original <br>
        $this->cfgAllowTags(['h3', 'ul', 'ol', 'li', 'p', 'br', 'obr']);
        $this->cfgSetTagShort(['br', 'obr']);
        $this->cfgSetTagCutWithContent(['script', 'object', 'iframe', 'style']);
        $this->cfgSetTagChilds('ul', 'li', true, false);
        $this->cfgSetTagChilds('ol', 'li', true, false);
        $this->cfgSetTagNoAutoBr(['ul', 'ol']);
        $this->cfgSetAutoBrMode(true);
        // $this->cfgSetTagChilds('yandexmarket', ['h3', 'ul', 'ol', 'li', 'p', 'br'], true, false);
    }

    public function parse($text, &$errors)
    {
        return preg_replace('/([\r\n]{2,})/m', "\n", //убирание множественных переносов
            str_replace(["<br/>", "<obr/>"], ["\n", "<br/>"], //возвращение оригинальных br из obr
                parent::parse(preg_replace('/<br\/?>/', "<obr/>", $text), $errors)
            )
        );
    }
}