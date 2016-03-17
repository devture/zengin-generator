<?php
namespace Devture\ZenginGenerator;

class StringUtil {

        static public function sprintfItemGroupsToString(array $items) {
                $formatParts = array_map(function ($item) {
                        return $item[0];
                }, $items);

                $dataParts = array_map(function ($item) {
                        return $item[1];
                }, $items);

                $format = implode('', $formatParts);

                return call_user_func_array('sprintf', array_merge(array($format), $dataParts));
        }

        static public function convertFullWidthToHalfWidthKana($string) {
                //Converts full-width katakana to half-width katakana, with the `k` flag.
                //Converts full-width space (`　`) to half-width space (` `), with the `s` flag.
                $string = \mb_convert_kana($string, 'ks');

                //Convert things like ﾄｳｷｮｳﾄﾐﾝ to ﾄｳｷﾖｳﾄﾐﾝ (ｮ -> ﾖ).
                //That is, converts the "voice" characters (half-width) to real characters (half-width).
                $string = self::convertHalfWidthKatakanaToUpercase($string);

                return $string;
        }

        static public function stringPadRight($string, $targetLength, $padChar) {
                $stringLength = static::strlen($string);
                if ($stringLength > $targetLength) {
                        throw new \InvalidArgumentException(sprintf(
                                'String `%s` is %d characters long. Cannot pad to %d.',
                                $string,
                                $stringLength,
                                $targetLength
                        ));
                }
                return $string . str_repeat($padChar, ($targetLength - $stringLength));
        }

        static public function isStringKatakanaAndAlphanumeric($string) {
                $stringFullWidthKatakana = \mb_convert_kana($string, 'K');
                if ($stringFullWidthKatakana !== $string) {
                        //\p{Katakana} below matches all sorts of Katakana.
                        //Thus, we should reject half-width early.
                        return false;
                }
		return (bool) preg_match('/^[[:ascii:] \p{Katakana}ー\.]*$/u', $string);
        }

        static public function strlen($string) {
                return mb_strlen($string, mb_detect_encoding($string));
        }

        static private function convertHalfWidthKatakanaToUpercase($string) {
                $map = array(
                        'ｧ' => 'ｱ',
                        'ｨ' => 'ｲ',
                        'ｩ' => 'ｳ',
                        'ｪ' => 'ｴ',
                        'ｫ' => 'ｵ',
                        'ｯ' => 'ﾂ',
                        'ｬ' => 'ﾔ',
                        'ｭ' => 'ﾕ',
                        'ｮ' => 'ﾖ',
                );
                return str_replace(array_keys($map), array_values($map), $string);
        }

}
