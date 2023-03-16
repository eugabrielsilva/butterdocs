<?php

/*
    External links identifier, made by kjdion84 and improved by Gabriel Silva
    source: https://stackoverflow.com/questions/47145213/add-target-blank-to-external-link-parsedown-php
*/

if (APP_CONFIG['md_extra'] ?? true) {
    class_alias(ParsedownExtra::class, 'DynamicParsedownClass');
} else {
    class_alias(Parsedown::class, 'DynamicParsedownClass');
}

class ParsedownExtended extends DynamicParsedownClass
{
    protected function element(array $Element)
    {
        if ($this->safeMode) {
            $Element = $this->sanitiseElement($Element);
        }

        $markup = '<' . $Element['name'];

        if (isset($Element['name']) && $Element['name'] == 'a') {
            $server_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
            $href_host = isset($Element['attributes']['href']) ? parse_url($Element['attributes']['href'], PHP_URL_HOST) : null;

            if (!is_null($href_host) && $server_host != $href_host) {
                $Element['attributes']['target'] = '_blank';
            }
        }

        if (isset($Element['attributes'])) {
            foreach ($Element['attributes'] as $name => $value) {
                if ($value === null) {
                    continue;
                }

                $markup .= ' ' . $name . '="' . self::escape($value) . '"';
            }
        }

        if (isset($Element['text'])) {
            $markup .= '>';

            if (!isset($Element['nonNestables'])) {
                $Element['nonNestables'] = array();
            }

            if (isset($Element['handler'])) {
                $markup .= $this->{$Element['handler']}($Element['text'], $Element['nonNestables']);
            } else {
                $markup .= self::escape($Element['text'], true);
            }

            $markup .= '</' . $Element['name'] . '>';
        } else {
            $markup .= ' />';
        }

        return $markup;
    }
}
