<?php

namespace Neitanod\Struct\Traits;

/**
 * @ SuppressWarnings(PHPMD.ElseExpression)
 */
trait LoaderDumperTrait
{

    protected $data = [];

    public function __construct($initial = null)
    {
        if (is_null($initial)) {
            return $this;
        } elseif (is_array($initial)) {
            return static::fromArray($initial);
        } elseif (is_object($initial)) {
            return static::fromObject($initial);
        } elseif (is_string($initial)) {
            return static::fromJson($initial);
        }
    }

    public static function fromJson(string $json)
    {
        return static::fromArray(json_decode($json, 1));
    }

    public static function fromObject(object $object)
    {
        return static::fromArray(json_decode(json_encode($object), 1));
    }

    public static function fromArray(array $array)
    {
        $o = new static();
        foreach ($array as $k => $v) {
            $o->data[$k] = sis_array($v) ? static::fromArrayOrArray($v) : $v;
        }
        return $o;
    }

    public function toArray()
    {
        if (isset($this->data)) {
            return static::castToArray($this->data);
        }
        return [];
    }

    public function toJson()
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    private static function fromArrayOrArray(array $array)
    {
        if (static::faIsAssoc($array)) {
             return static::fromArray($array);
        }
        $o = [];
        foreach ($array as $v) {
            $o[] = is_array($v) ? static::fromArrayOrArray($v) : $v ;
        }
        return $o;
    }

    private static function faIsAssoc(array $arr)
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private static function castToArray($var)
    {
        if (is_object($var)) {
            return static::objectToArray($var);
        } elseif (is_array($var)) {
            $o = [];
            foreach ($var as $k => $v) {
                $o[$k] = static::castToArray($v);
            }
            return $o;
        }
        return $var;
    }

    private static function objectToArray($obj)
    {
        return method_exists($obj, "toArray") ?
            $obj->toArray() :
            static::castToArray((array)$obj);
    }
}
