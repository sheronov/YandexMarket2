<?php

namespace YandexMarket\Handlers;

use Jevix;

class XmlJevix extends Jevix
{
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

    //это более строгий вырезающий текст без тегов
    public function parseVariant($text, &$errors)
    {
        return str_replace(["<yandexmarket>", "</yandexmarket>"], '',
            parent::parse("<yandexmarket>{$text}</yandexmarket>", $errors));
    }
}