<?php
namespace App\Helpers;

class DBArray
{
    public static function toString(array $array = [])
    {
        return '{' . \implode(',', $array) . '}';
    }

    public static function toArray($str)
    {
        $amenities = explode(",", str_replace("{", "", \str_replace("}", "", $str)));
        if ($amenities[0] == "") {
            return [];
        }
        return $amenities;
    }

    public static function toObject(array $array = [])
    {
        return '(' . \implode(',', $array) . ')';
    }

    public static function toPoint($str)
    {
        return str_replace("(", "", \str_replace(")", "", $str));
    }

    public static function fromObject($keys = [], $str = [])
    {
        $values = explode(",", str_replace("(", "", \str_replace(")", "", $str)));
        $obj = [];
        if ($values[0] == "") {
            return (object) $obj;
        }

        foreach ($keys as $index => $key) {
            $obj[$key] = $values[$index];
        }

        return (object) $obj;
    }
}
