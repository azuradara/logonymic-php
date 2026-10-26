<?php

namespace Logonymic;

final class Canonical
{
    public static function encode(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => $value ? 'true' : 'false',
            is_int($value) => (string) $value,
            is_float($value) => self::number($value),
            is_string($value) => json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS | JSON_THROW_ON_ERROR),
            is_array($value) && array_is_list($value) => '['.implode(',', array_map(self::encode(...), $value)).']',
            is_array($value) => self::object($value),
            is_object($value) => self::object(get_object_vars($value)),
            default => 'null',
        };
    }

    /**
     * @param  array<array-key, mixed>  $fields
     */
    private static function object(array $fields): string
    {
        $keys = array_map('strval', array_keys($fields));
        sort($keys, SORT_STRING);

        return '{'.implode(',', array_map(fn (string $key) => self::encode($key).':'.self::encode($fields[$key]), $keys)).'}';
    }

    private static function number(float $value): string
    {
        if ($value == 0) {
            return '0';
        }
        [$mantissa, $exponent] = array_pad(explode('e', strtolower((string) json_encode(abs($value)))), 2, '0');
        $point = strpos($mantissa, '.');
        $digits = str_replace('.', '', $mantissa);
        $trimmed = ltrim($digits, '0');
        $n = ($point === false ? strlen($mantissa) : $point) + (int) $exponent - strlen($digits) + strlen($trimmed);
        $digits = rtrim($trimmed, '0');
        $k = strlen($digits);
        $sign = $value < 0 ? '-' : '';

        if ($k <= $n && $n <= 21) {
            return $sign.$digits.str_repeat('0', $n - $k);
        }
        if ($n > 0 && $n <= 21) {
            return $sign.substr($digits, 0, $n).'.'.substr($digits, $n);
        }
        if ($n > -6 && $n <= 0) {
            return $sign.'0.'.str_repeat('0', -$n).$digits;
        }

        return $sign.$digits[0].($k > 1 ? '.'.substr($digits, 1) : '').'e'.($n - 1 < 0 ? '-' : '+').abs($n - 1);
    }
}
