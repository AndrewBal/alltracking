<?php

namespace App\Validators;

use App\Models\Seo\UrlAlias;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Validations
{
    public function uniqueInArray($attribute, $value, $parameters, $validator)
    {
        if ($parameters) {
            if (!in_array($value, $parameters)) return TRUE;
        } else {
            return TRUE;
        }

        return FALSE;
    }

    public function reCaptcha($attribute, $value, $parameters, $validator)
    {
        if (is_null($value) || $value == "null") return TRUE;
        if ($value && $value != "null") {
            $_decode = data_decrypt($value);
            if (isset($_decode->success) && $_decode->success) return TRUE;
        }

        return FALSE;
    }

    //    public function validatePhoneNumber($attribute, $value, $parameters, $validator)
    //    {
    //        if (!empty($value)) {
    //            preg_match('/^\+38\(\d{3}\) \d{3}-\d{4}$/', $value, $matches);
    //            if ($matches) return TRUE;
    //        }
    //
    //        return FALSE;
    //    }
    //
    //    public function validatePhoneOperatorCode($attribute, $value, $parameters, $validator)
    //    {
    //        if (!empty($value)) {
    //            $_codes = [
    //                '066',
    //                '067',
    //                '068',
    //                '091',
    //                '096',
    //                '097',
    //                '098',
    //                '063',
    //                '093',
    //                '073',
    //                '050',
    //                '095',
    //                '099'
    //            ];
    //            $_phone = preg_replace('/^\+38|^38|\D/m', '', $value);
    //            $_code_exist = FALSE;
    //            foreach ($_codes as $_code_phone) if (Str::is("{$_code_phone}*", $_phone)) $_code_exist = TRUE;
    //            if ($_code_exist) return TRUE;
    //        }
    //
    //        return FALSE;
    //    }
    //
    //    public function validateExistsDataInTable($attribute, $value, $parameters, $validator)
    //    {
    //        if (!empty($value)) {
    //            $_table = $parameters[0] ?? NULL;
    //            if ($_table && Schema::hasTable($_table)) {
    //                $_cell = $parameters[1] ?? NULL;
    //                if ($_cell && Schema::hasColumn($_table, $_cell)) {
    //                    $_value = $parameters[2] ?? NULL;
    //                    if ($_value) {
    //                        $exists = DB::table($_table)
    //                            ->where($_cell, $_value)
    //                            ->count();
    //
    //                        return $exists ? TRUE : FALSE;
    //                    }
    //                }
    //            }
    //        }
    //
    //        return FALSE;
    //    }
    //
    //    public function validateMultiRequiredIf($attribute, $value, $parameters)
    //    {
    //        $_field_value = request()->get($parameters[0]);
    //        if ($_field_value && $parameters[1] && in_array($parameters[1], $_field_value)) {
    //            if (is_null($value)) return FALSE;
    //
    //            return TRUE;
    //        }
    //
    //        return TRUE;
    //    }
    //
    //    public function validateUniqueURL($attribute, $value, $parameters)
    //    {
    //        $_exclude = $parameters[0];
    //        $_locale = $parameters[1] ?? DEFAULT_LOCALE;
    //        $_exists = UrlAlias::where('alias', $value)
    //            ->where('locale', $_locale)
    //            ->where('id', '<>', $_exclude)
    //            ->first();
    //        if ($_exists) return FALSE;
    //
    //        return TRUE;
    //    }
}
