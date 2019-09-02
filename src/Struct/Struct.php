<?php
/*
Copyright (c) 2008 Sebastián Grignoli
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.
3. Neither the name of copyright holders nor the names of its
   contributors may be used to endorse or promote products derived
   from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED
TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL COPYRIGHT HOLDERS OR CONTRIBUTORS
BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/

/**
 * @author   "Sebastián Grignoli" <grignoli@gmail.com>
 * @package  Neitanod\Struct
 * @version  1.0
 * @link     https://github.com/neitanod/struct
 * @example  https://github.com/neitanod/struct
 * @license  Revised BSD
  */

namespace Neitanod\Struct;

interface TraversableStruct extends \Traversable
{

}

class Struct implements \ArrayAccess, \IteratorAggregate, TraversableStruct, \Countable
{

    protected $data = [];
    protected $parent = null;
    protected $parent_key = null;

    public const CREATE = '__struct__create__aslkhsdafkhfsd__khadsfhusdfajhu___';
    public const DELETE = '__struct__delete__aslkhsdafkhfsd__khadsfhusdfajhu___';

    public function __construct($initial = null)
    {
        if (is_null($initial)) {
            return $this;
        } elseif (is_array($initial)) {
            $this->fromArray($initial);
        } elseif (is_object($initial)) {
            $this->fromObject($initial);
        } elseif (is_string($initial)) {
            $this->fromJson($initial);
        }
        return $this;
    }

    public function __call($name, $args)
    {
        if (substr($name, 0, 6) === "access") {
            if (substr($name, 0, 7) === "access_") { // assume snake_case
                $propname = substr($name, 7);
            } else { // assume camelCase
                $propname = static::fromCamelCase(substr($name, 6));
            }
            return $this->access($propname);  // treats it as a struct
            //return $this->set($propname, Struct::CREATE);  // treats it as a struct
        }

        if (substr($name, 0, 3) === "set") {
            if (substr($name, 0, 4) === "set_") { // assume snake_case
                $propname = substr($name, 4);
            } else { // assume camelCase
                $propname = static::fromCamelCase(substr($name, 3));
            }
            $value = (array_key_exists(0, $args) ? $args[0] : Struct::DELETE);
            return $this->set($propname, $value);  // defaults to first argument
        }

        if (substr($name, 0, 3) === "get") { // always snake_case internally
            $propname = static::fromCamelCase(substr($name, 3));
            // echo "Propname: ".$propname."\n";
            return $this->get($propname, (isset($args[0]) ? $args[0] : null));  // defaults to first argument
        }
    }

    public function set($propname, $value = Struct::DELETE)
    {
        // if ($value === Struct::CREATE) { // chaining of empty setters allow us to set deep nested struct values
        //     if (!array_key_exists($propname, $this->data) || !( $this->data[$propname] instanceof static )) {
        //         // echo "Defining: ". $propname . "\n";
        //         $this->data[$propname] = new static();
        //
        //         // setters with no arguments do not chain, they nest.  Return new struct.
        //     }
        //     return $this->data[$propname];
        // } else
        if ($value === Struct::DELETE) {
            if (array_key_exists($propname, $this->data)) {
                unset($this->data[$propname]);
            }
        } else {
            // echo $propname.": ".$value ."\n";
            if ($propname == "") {
                $this->data[] = $value;
            } else {
                $this->data[$propname] = $value;
            }
        }

        $this->installInParent(); // if this instance was created on the fly, then make permanent in parent's struct

        // regular setters do not nest, they chain.  Return this object.
        return $this;
    }

    public function get($propname, $defaultValue = null)
    {
        if ($defaultValue === Struct::CREATE) { // chaining of empty setters allow us to set deep nested struct values
            if (!array_key_exists($propname, $this->data)) {
                // echo "Defining: ". $propname . "\n";
                $this->data[$propname] = new static();
            }
        }
        $useValue = $defaultValue === Struct::CREATE ? null : $defaultValue;

        // getters do not chain, they nest.  Return value or new struct.
        return array_key_exists($propname, $this->data) ? $this->data[$propname] : $useValue;
    }

    public function access($propname)
    {
        if (!array_key_exists($propname, $this->data) || !( $this->data[$propname] instanceof static )) {
            // echo "Defining: ". $propname . "\n";
            // $this->data[$propname] = new static();
            $new_struct = new static();
            $new_struct->registerParent($this, $propname);
            return $new_struct;
        }

        // getters do not chain, they nest.  Return value or new struct.
        return $this->data[$propname];
    }

    protected function registerParent($parent, $key)
    {
        // Internal function used to build nested paths only when assigning
        // values to the last struct.  This function creates the path but does
        // not assign it to the base struct yet.
        $this->parent = $parent;
        $this->parent_key = $key;
    }

    protected function installInParent()
    {
        // Internal function used to build nested paths only when assigning
        // values to the last struct.  This function gets called when something is assigned
        // to the leaf node, and makes the path permanent in the struct.
        if ($this->parent instanceof static && $this->parent_key) {
            $this->parent->set($this->parent_key, $this);
        }
    }

    private static function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    public function fromJson(string $json)
    {
        return $this->fromArray(json_decode($json, 1));
    }

    public function fromObject($object)
    {
        return $this->fromArray(static::toArrayDeep($object));
    }

    public function fromArray(array $array)
    {
        $this->data = [];
        foreach ($array as $k => $v) {
            $this->data[$k] = is_array($v) ? static::fromArrayDeep($v) : $v;
        }
        return $this;
    }

    public function toArray()
    {
        if (isset($this->data)) {
            return static::toArrayDeep($this->data);
        }
        return [];
    }

    public function toJson()
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    public function __toString()
    {
        return $this->toJson();
    }

    private static function fromArrayDeep(array $array)
    {
        if (static::faIsAssoc($array)) {
            $o = (new static())->fromArray($array);
            return $o;
        }
        $o = [];
        foreach ($array as $v) {
            $o[] = is_array($v) ? static::fromArrayDeep($v) : $v ;
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

    private static function toArrayDeep($var)
    {
        if (is_object($var)) {
            return static::objectToArray($var);
        } elseif (is_array($var)) {
            $o = [];
            foreach ($var as $k => $v) {
                $o[$k] = static::toArrayDeep($v);
            }
            return $o;
        }
        return $var;
    }

    private static function objectToArray($obj)
    {
        return method_exists($obj, "toArray") ?
            $obj->toArray() :
            static::toArrayDeep((array)$obj);
    }

    public function __isSet($offset)
    {
        return isset($this->data[$offset]);
    }

    // IteratorAggregate interface
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    // ArrayAccess interface
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->get($offset) : $this->access($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    // Countable interface
    public function count()
    {
        return count($this->data);
    }
}
