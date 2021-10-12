<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;

if (!function_exists('config_data_load')) {
    function config_data_load($config, $variable, $locale = NULL, $fallback_locale = NULL)
    {
        $_response = NULL;
        if (is_string($config)) $config = config($config);
        if ($config) {
            if ($locale) {
                $_variable = Str::contains($variable, '*') ? str_replace('*', $locale, $variable) : "{$variable}.{$locale}";
                $_response = Arr::get($config, $_variable);
                if (!$_response) {
                    $_fallback_locale = $fallback_locale ? : config('app.fallback_locale');
                    $_variable = Str::contains($variable, '*') ? str_replace('*', $_fallback_locale, $variable) : "{$variable}.{$_fallback_locale}";
                    $_response = Arr::get($config, $_variable);
                }
            } else {
                $_response = Arr::get($config, $variable);
            }
        }

        return $_response;
    }
}

if (!function_exists('shortcut')) {
    function shortcut($_content, $entity = NULL)
    {
        $_variables = NULL;
        preg_match_all('|\[\:(.*?)\]|xs', $_content, $_shorts);
        if (is_object($entity)) {
            if ($_shorts && count($_shorts[0])) {
                foreach ($_shorts[0] as $_index_short => $_data_short) {
                    $_attribute_name = $_shorts[1][$_index_short];
                    $_variables[] = [
                        'code'    => $_data_short,
                        'replace' => $entity->hasAttribute($_attribute_name) ? $entity->{$_attribute_name} : NULL
                    ];
                }
            }
        } elseif (is_array($entity) && count($entity)) {
            foreach ($_shorts[0] as $_index_short => $_data_short) {
                $_attribute_name = $_shorts[1][$_index_short];
                $_variables[] = [
                    'code'    => $_data_short,
                    'replace' => isset($entity[$_attribute_name]) ? $entity[$_attribute_name] : NULL
                ];
            }
        } elseif (is_string($entity)) {
            foreach ($_shorts[0] as $_index_short => $_data_short) {
                $_variables[] = [
                    'code'    => $_data_short,
                    'replace' => $entity
                ];
            }
        }
        if ($_variables) {
            foreach ($_variables as $_replace_code) {
                $_content = str_replace($_replace_code['code'], $_replace_code['replace'], $_content);
            }
        }

        return $_content;
    }
}

if (!function_exists('contacts_load')) {
    function contacts_load($locale = DEFAULT_LOCALE)
    {
        $_response = config('contacts');
        $_response['address'] = config_data_load($_response, 'address.*', $locale);
        $_response['working_hours'] = config_data_load($_response, 'working_hours.*', $locale);
        $_response['schema'] = config_data_load($_response, 'schema.*', $locale);
        foreach ($_response['phones'] as &$_phone) {
            if ($_phone) {
                $_phone = format_phone_number($_phone, ' ');
            }
        }
        if ($_response['schema']) {
            $_response['schema'] = preg_replace('/[\r\n\t]+/', ' ', $_response['schema']);
            $_response['schema'] = preg_replace('/[\s]+/', ' ', $_response['schema']);
            $_response['schema'] = json_encode(json_decode($_response['schema'], TRUE));
        }

        return $_response;
    }
}
