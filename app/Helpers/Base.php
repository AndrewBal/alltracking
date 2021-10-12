<?php

use App\Models\Components\Journal;
use App\Models\Components\Variable;
use App\Models\Components\File;
use App\Models\Seo\UrlAlias;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

if (!function_exists('wrap')) {
    function wrap()
    {
        return app('wrap');
    }
}

if (!function_exists('device')) {
    function device()
    {
        return app('device');
    }
}

if (!function_exists('array_undot')) {
    function array_undot($dotNotationArray)
    {
        $array = [];
        foreach ($dotNotationArray as $key => $value) {
            Arr::set($array, $key, $value);
        }

        return $array;
    }
}

if (!function_exists('array_merge_recursive_distinct')) {
    function array_merge_recursive_distinct(&$array1, &$array2)
    {
        $_merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($_merged[$key]) && is_array($_merged[$key])) {
                $_merged[$key] = array_merge_recursive_distinct($_merged[$key], $value);
            } else {
                $_merged[$key] = $value;
            }
        }

        return $_merged;
    }
}

if (!function_exists('is_assoc_array')) {
    function is_assoc_array($array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }
}

if (!function_exists('is_whole_int')) {
    function is_whole_int($value)
    {
        return is_numeric($value) && floor($value) == $value && $value > 0;
    }
}

if (!function_exists('is_json')) {
    function is_json($string)
    {
        json_decode($string);

        return (json_last_error() == 0);
    }
}

if (!function_exists('number_format_short')) {
    function number_format_short($number, $precision = 1)
    {
        if ($number > 0 && $number < 1000000) {
            $number = (float)($number / 1000);
            $_format = is_whole_int($number) ? $number : number_format($number, $precision);
            $_suffix = 'K';
        } else {
            if ($number >= 1000000 && $number < 1000000000) {
                $_format = number_format($number / 1000000, $precision);
                $_suffix = 'M';
            } else {
                if ($number >= 1000000000 && $number < 1000000000000) {
                    $_format = number_format($number / 1000000000, $precision);
                    $_suffix = 'B';
                } else {
                    if ($number >= 1000000000000) {
                        $_format = number_format($number / 1000000000000, $precision);
                        $_suffix = 'T';
                    }
                }
            }
        }

        return !empty($_format . $_suffix) ? $_format . $_suffix : 0;
    }
}

if (!function_exists('var_export_short_array')) {
    function var_export_short_array($expression, $return = FALSE)
    {
        $_export = var_export($expression, TRUE);
        $_export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $_export);
        $_array = preg_split("/\r\n|\n|\r/", $_export);
        $_array = preg_replace([
            "/\s*array\s\($/",
            "/\)(,)?$/",
            "/\s=>\s$/"
        ], [
            NULL,
            ']$1',
            ' => ['
        ], $_array);
        $_export = join(PHP_EOL, array_filter(["["] + $_array));
        if ((bool)$return) return $_export; else echo $_export;
    }
}

if (!function_exists('clear_html')) {
    function clear_html($html)
    {
        $replace = array(
            '/\>[^\S ]+/s'                                                    => '>',
            '/[^\S ]+\</s'                                                    => '<',
            '/([\t ])+/s'                                                     => ' ',
            '/^([\t ])+/m'                                                    => '',
            '/([\t ])+$/m'                                                    => '',
            '~//[a-zA-Z0-9 ]+$~m'                                             => '',
            '/[\r\n]+([\t ]?[\r\n]+)+/s'                                      => "\n",
            '/\>[\r\n\t ]+\</s'                                               => '><',
            '/}[\r\n\t ]+/s'                                                  => '}',
            '/}[\r\n\t ]+,[\r\n\t ]+/s'                                       => '},',
            '/\)[\r\n\t ]?{[\r\n\t ]+/s'                                      => '){',
            '/,[\r\n\t ]?{[\r\n\t ]+/s'                                       => ',{',
            '/\),[\r\n\t ]+/s'                                                => '),',
            '~([\r\n\t ])?([a-zA-Z0-9]+)="([a-zA-Z0-9_/\\-]+)"([\r\n\t ])?([\r\n ])+~s' => '$1$2=$3$4 ',
        );
        $html = preg_replace(array_keys($replace), array_values($replace), $html);
        $remove = array(
            '</option>',
            '</li>',
            '</dt>',
            '</dd>',
            '</tr>',
            '</th>',
            '</td>'
        );

        return str_ireplace($remove, '', $html);
    }
}

if (!function_exists('clear_html_attribute')) {
    function clear_html_attribute($html, $attribute = 'style')
    {
        return preg_replace('/' . $attribute . '=\\"[^\\"]*\\"/', '', $html);
    }
}

if (!function_exists('transcription_string')) {
    function transcription_string($string)
    {
        $string = trim(strip_tags($string));
        $_transcription = [
            'й' => 'q',
            'ц' => 'w',
            'у' => 'e',
            'к' => 'r',
            'е' => 't',
            'н' => 'y',
            'г' => 'u',
            'ш' => 'i',
            'щ' => 'o',
            'з' => 'p',
            'х' => '[',
            'ъ' => ']',
            'ф' => 'a',
            'ы' => 's',
            'в' => 'd',
            'а' => 'f',
            'п' => 'g',
            'р' => 'h',
            'о' => 'j',
            'л' => 'k',
            'д' => 'l',
            'ж' => ';',
            'э' => '\'',
            'я' => 'z',
            'ч' => 'x',
            'с' => 'c',
            'м' => 'v',
            'и' => 'b',
            'т' => 'n',
            'ь' => 'm',
            'б' => ',',
            'ю' => '.',
            '.' => '/',
            'Й' => 'Q',
            'Ц' => 'W',
            'У' => 'E',
            'К' => 'R',
            'Е' => 'T',
            'Н' => 'Y',
            'Г' => 'U',
            'Ш' => 'I',
            'Щ' => 'O',
            'З' => 'P',
            'Х' => '{',
            'Ъ' => '}',
            'Ф' => 'A',
            'Ы' => 'S',
            'В' => 'D',
            'А' => 'F',
            'П' => 'G',
            'Р' => 'H',
            'О' => 'J',
            'Л' => 'K',
            'Д' => 'L',
            'Ж' => ':',
            'Э' => '"',
            'Я' => 'Z',
            'Ч' => 'X',
            'С' => 'C',
            'М' => 'V',
            'И' => 'B',
            'Т' => 'N',
            'Ь' => 'M',
            'Б' => '<',
            'Ю' => '>',
            ',' => '?'
        ];
        if (preg_match('/[A-z]+/i', $string)) $_transcription = array_flip($_transcription);

        return strtr($string, $_transcription);
    }
}

if (!function_exists('similar_split_letters')) {
    function similar_split_letters($string)
    {
        $_letters = [
            ['е' => 'и'],
            ['о' => 'а'],
            ['и' => 'а'],
            ['в' => 'ф'],
            ['м' => 'л'],
            ['н' => 'л'],
            ['п' => 'н'],
            ['б' => 'п'],
            ['к' => 'п'],
            ['б' => 'в'],
            ['д' => 'т'],
            ['п' => 'л'],
            ['х' => 'к'],
            ['н' => 'м'],
            ['и' => 'е'],
            ['а' => 'о'],
            ['а' => 'и'],
            ['ф' => 'в'],
            ['л' => 'м'],
            ['л' => 'н'],
            ['н' => 'п'],
            ['п' => 'б'],
            ['п' => 'к'],
            ['в' => 'б'],
            ['т' => 'д'],
            ['л' => 'п'],
            ['к' => 'х'],
            ['м' => 'н']
        ];
        $_response = [];
        foreach ($_letters as $_letter) {
            $_replaced = str_replace(array_keys($_letter), array_values($_letter), $string);
            if ($string != $_replaced) $_response[] = $_replaced;
        }

        return $_response;
    }
}

if (!function_exists('plural_string')) {
    function plural_string($n, $string, $tag = NULL)
    {
        $_plural = explode('|', $string);
        foreach ($_plural as &$_p) if ($_p) $_p = trans($_p);
        if (!$n) return $_plural[0];
        if ($n % 10 == 1 && $n % 100 != 11) {
            return $tag ? "{$n}&nbsp;<{$tag}>{$_plural[1]}</{$tag}>" : "{$n}&nbsp;{$_plural[1]}";
        } elseif ($n % 10 >= 2 && $n % 10 <= 4 && ($n % 100 < 10 || $n % 100 >= 20)) {
            return $tag ? "{$n}&nbsp;<{$tag}>{$_plural[2]}</{$tag}>" : "{$n}&nbsp;{$_plural[2]}";
        } else {
            return $tag ? "{$n}&nbsp;<{$tag}>{$_plural[3]}</{$tag}>" : "{$n}&nbsp;{$_plural[3]}";
        }
    }
}

if (!function_exists('robots')) {
    function robots($save = FALSE)
    {
        if ($save) {
            $robots = request()->input('robots_txt');
            if ($robots) {
                Storage::disk('base')
                    ->put('robots.txt', $robots);
            }

            return TRUE;
        }
        $robots = NULL;
        if (Storage::disk('base')->exists('robots.txt')) {
            $robots = Storage::disk('base')
                ->get('robots.txt');
        }

        return $robots;
    }
}

if (!function_exists('truncate_string')) {
    function truncate_string($string, $options = [])
    {
        $_response = strip_tags(clear_html($string));
        $_options = array_merge([
            'count_word' => 300,
            'bound_word' => TRUE,
            'dotted'     => TRUE,
        ], $options);
        if (iconv_strlen($_response, 'UTF-8') > $_options['count_word']) {
            $_response = mb_substr($_response, 0, $_options['count_word']);
            $_response = rtrim($_response, '!,.-');
            if ($_options['bound_word']) $_response = mb_substr($_response, 0, mb_strrpos($_response, ' '));
            if ($_options['dotted']) $_response .= '...';
        }

        return $_response;
    }
}

if (!function_exists('replace_spaces')) {
    function replace_spaces($string_html)
    {
        $string_html = preg_replace('/>[\s]+</', '><', trim(trim($string_html), '&nbsp;'));
        for ($i = 0, $_tag_open = FALSE, $_tag_close = FALSE; $i < strlen($string_html); $i++) {
            if (($string_html[$i] == ' ') && $_tag_close) {
                $string_html = substr_replace($string_html, '&nbsp;', $i, 1);
            } elseif ($i > 0 && ($string_html[$i - 2] == ' ') && $_tag_open) {
                $string_html = substr_replace($string_html, '&nbsp;', $i - 2, 1);
            } else {
                if ($string_html[$i] == '<') {
                    $_tag_open = TRUE;
                    $_tag_close = FALSE;
                } elseif ($string_html[$i] == '>') {
                    $_tag_close = TRUE;
                    $_tag_open = FALSE;
                } else {
                    $_tag_close = FALSE;
                    $_tag_open = FALSE;
                }
            }
        }

        return $string_html;
    }
}

if (!function_exists('replace_unicode_to_win')) {
    function replace_unicode_to_win($string)
    {
        $_transcription = [
            'А' => '&#1040;',
            'Б' => '&#1041;',
            'В' => '&#1042;',
            'Г' => '&#1043;',
            'Д' => '&#1044;',
            'Е' => '&#1045;',
            'Ж' => '&#1046;',
            'З' => '&#1047;',
            'И' => '&#1048;',
            'Й' => '&#1049;',
            'К' => '&#1050;',
            'Л' => '&#1051;',
            'М' => '&#1052;',
            'Н' => '&#1053;',
            'О' => '&#1054;',
            'П' => '&#1055;',
            'Р' => '&#1056;',
            'С' => '&#1057;',
            'Т' => '&#1058;',
            'У' => '&#1059;',
            'Ф' => '&#1060;',
            'Х' => '&#1061;',
            'Ц' => '&#1062;',
            'Ч' => '&#1063;',
            'Ш' => '&#1064;',
            'Щ' => '&#1065;',
            'Ъ' => '&#1066;',
            'Ы' => '&#1067;',
            'Ь' => '&#1068;',
            'Э' => '&#1069;',
            'Ю' => '&#1070;',
            'Я' => '&#1071;',
            'а' => '&#1072;',
            'б' => '&#1073;',
            'в' => '&#1074;',
            'г' => '&#1075;',
            'д' => '&#1076;',
            'е' => '&#1077;',
            'ж' => '&#1078;',
            'з' => '&#1079;',
            'и' => '&#1080;',
            'й' => '&#1081;',
            'к' => '&#1082;',
            'л' => '&#1083;',
            'м' => '&#1084;',
            'н' => '&#1085;',
            'о' => '&#1086;',
            'п' => '&#1087;',
            'р' => '&#1088;',
            'с' => '&#1089;',
            'т' => '&#1090;',
            'у' => '&#1091;',
            'ф' => '&#1092;',
            'х' => '&#1093;',
            'ц' => '&#1094;',
            'ч' => '&#1095;',
            'ш' => '&#1096;',
            'щ' => '&#1097;',
            'ъ' => '&#1098;',
            'ы' => '&#1099;',
            'ь' => '&#1100;',
            'э' => '&#1101;',
            'ю' => '&#1102;',
            'я' => '&#1103;',
            'Ё' => '&#1025;',
            'ё' => '&#1025;',
        ];

        return strtr($string, array_flip($_transcription));
    }
}

if (!function_exists('data_encrypt')) {
    function data_encrypt($data)
    {
        $_key = '6LcJ4M8UAAAAAKyDU_EA-M7eh3AXNdwm';
        $_encrypt = serialize($data);
        $_ivLen = openssl_cipher_iv_length($_cipher = "AES-128-CBC");
        $_iv = openssl_random_pseudo_bytes($_ivLen);
        $_cipherTextRaw = openssl_encrypt($_encrypt, $_cipher, $_key, $_options = OPENSSL_RAW_DATA, $_iv);
        $_mac = hash_hmac('sha256', $_cipherTextRaw, $_key, $_as_binary = TRUE);

        return base64_encode($_iv . $_mac . $_cipherTextRaw);
    }
}

if (!function_exists('data_decrypt')) {
    function data_decrypt($string)
    {
        $_key = '6LcJ4M8UAAAAAKyDU_EA-M7eh3AXNdwm';
        $_cipherText = base64_decode($string);
        $_ivLen = openssl_cipher_iv_length($_cipher = "AES-128-CBC");
        $_iv = substr($_cipherText, 0, $_ivLen);
        $_mac = substr($_cipherText, $_ivLen, $_sha2Len = 32);
        $_cipherTextRaw = substr($_cipherText, $_ivLen + $_sha2Len);
        $_plainText = openssl_decrypt($_cipherTextRaw, $_cipher, $_key, $_options = OPENSSL_RAW_DATA, $_iv);
        $_calcMac = hash_hmac('sha256', $_cipherTextRaw, $_key, $as_binary = TRUE);
        if (hash_equals($_mac, $_calcMac)) return unserialize($_plainText);

        return FALSE;
    }
}

if (!function_exists('dd_code')) {
    function dd_code($data = NULL, $key = 'code')
    {
        if (request()->has($key)) dd($data);
    }
}

if (!function_exists('file_get')) {
    function file_get($fid = NULL)
    {
        $_response = NULL;
        $_disk = Storage::disk('public');
        if (is_numeric($fid)) {
            $_file = File::where('id', $fid)
                ->first();
            if ($_file && $_disk->exists($_file->file_name)) $_response = $_file;
        } elseif (is_array($fid)) {
            foreach ($fid as $_file_fid) {
                $_file = File::where('id', $_file_fid)
                    ->first();
                if ($_file && $_disk->exists($_file->file_name)) $_response[] = $_file;
            }
            if ($_response) $_response = collect($_response);
        }

        return $_response;
    }
}

if (!function_exists('file_save')) {
    function file_save($file, $request = [])
    {
        if (count($request)) {
            if (isset($request['title'])) {
                $file->title = $request['title'];
            }
            if (isset($request['alt'])) {
                $file->alt = $request['alt'];
            }
            if ($file->isDirty()) $file->save();
        }

        return $file;
    }
}

if (!function_exists('config_file_save')) {
    function config_file_save($config, $data, $rebuild = FALSE)
    {
        $_data = $rebuild ? [] : config($config);
        foreach ($data as $_key => $_value) {
            $_parts = explode('.', $_key);
            $_element = &$_data;
            foreach ($_parts as $_part) $_element = &$_element[$_part];
            if (is_bool($_value)) {
                $_element = (bool)$_value;
            } else {
                $_element = (string)$_value;
            }
        }
        $_code = '<?php return ' . var_export_short_array($_data, TRUE) . ';';
        Storage::disk('config')->put("{$config}.php", $_code);

        return $_data;
    }
}

if (!function_exists('update_last_modified_timestamp')) {
    function update_last_modified_timestamp()
    {
        config_file_save('seo', ['last_modified_timestamp' => time()]);
    }
}

if (!function_exists('cache_key')) {
    function cache_key($type, $entity, $locale = NULL, $user_role = NULL, $device = NULL)
    {
        global $wrap;
        $_render[] = $type;
        $_render[] = is_object($entity) ? ($entity->id ?? 0) : (is_string($entity) || is_numeric($entity) ? $entity : 'entity');
        $_render[] = $device ? $device : ($wrap['device']['type'] ?? 'pc');
        $_render[] = $locale ? $locale : ($wrap['locale'] ?? DEFAULT_LOCALE);
        $_render[] = $user_role ? $user_role : ($wrap['user_role'] ?? 'anonymous');

        return strtolower(implode('_', $_render));
    }
}

if (!function_exists('file_size_number_format_short')) {
    function file_size_number_format_short($number, $precision = 1)
    {
        if ($number > 0 && $number < 1000000) {
            $number = (float)($number / 1000);
            $_format = is_whole_int($number) ? $number : number_format($number, $precision);
            $_suffix = 'K';
        } else {
            if ($number >= 1000000 && $number < 1000000000) {
                $_format = number_format($number / 1000000, $precision);
                $_suffix = 'M';
            } else {
                if ($number >= 1000000000 && $number < 1000000000000) {
                    $_format = number_format($number / 1000000000, $precision);
                    $_suffix = 'B';
                } else {
                    if ($number >= 1000000000000) {
                        $_format = number_format($number / 1000000000000, $precision);
                        $_suffix = 'T';
                    }
                }
            }
        }

        return !empty($_format . $_suffix) ? $_format . $_suffix : 0;
    }
}

if (!function_exists('format_phone_number')) {
    function format_phone_number($phone, $delimiter = '-')
    {
        $_codes = [
            '067' => 'k-star',
            '091' => 'k-star',
            '096' => 'k-star',
            '097' => 'k-star',
            '098' => 'k-star',
            '063' => 'life',
            '093' => 'life',
            '073' => 'life',
            '050' => 'mts',
            '095' => 'mts',
            '099' => 'mts',
        ];
        $_has_38 = Str::is("+38*", $phone);
        $_phone = htmlspecialchars(clear_phone_number($phone));
        $_phone = str_replace('&nbsp;', '', html_entity_decode($_phone));
        $_phone = str_replace(' ', '', $_phone);
        preg_match('/\((.*?)\)/', $phone, $_phone_number_code_matches);
        $_code = $_phone_number_code_matches[1] ?? NULL;
        $_code_class = $_codes[$_code] ?? NULL;
        if (is_null($_code)) {
            foreach (array_keys($_codes) as $_code_phone) {
                if (Str::is("{$_code_phone}*", $_phone)) {
                    $_code = $_code_phone;
                    $_code_class = $_codes[$_code] ?? NULL;
                    break;
                }
            }
        }
        $_phone = str_replace($_code, '', $_phone);
        preg_match('/^(\d{3})(\d{2})(\d{2})$/', $_phone, $_phone_number_matches);
        if (!count($_phone_number_matches)) preg_match('/^(\d{3})(\d{4})$/', $_phone, $_phone_number_matches);
        if (!count($_phone_number_matches)) preg_match('/^(\d{3})(\d{2})$/', $_phone, $_phone_number_matches);
        unset($_phone_number_matches[0]);
        if (!$_phone_number_matches) {
            $_phone_number_matches = [
                substr($_phone, 0, 3),
                substr($_phone, 3, 5),
            ];
        }
        $_phone_number = implode($delimiter, $_phone_number_matches);

        return [
            'original' => $phone,
            'code'     => $_code,
            'class'    => $_code_class,
            'format'   => [
                'lite' => "({$_code})&nbsp;{$_phone_number}",
                'full' => ($_has_38 ? '+38&nbsp;' : NULL) . "({$_code})&nbsp;{$_phone_number}",
                'href' => ($_has_38 ? 'tel:+38' : 'tel:') . clear_phone_number($phone),
            ]
        ];
    }
}

if (!function_exists('clear_phone_number')) {
    function clear_phone_number($phone)
    {
        return preg_replace('/^\+38|^38|\D/m', '', $phone);
    }
}

//if (!function_exists('spy')) {
//    function spy($message, $type = 'info', $alert = FALSE, $error = NULL)
//    {
//        Journal::create([
//            'type'    => $type,
//            'message' => $message,
//            'error'   => $error,
//        ]);
//        // todo: в дальнейшем дописать функцию оповещения о конкретных событиях (например ошибках)
//    }
//}


