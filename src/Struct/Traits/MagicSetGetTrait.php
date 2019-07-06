<?php

namespace Neitanod\Struct\Traits;

use Neitanod\Struct\Struct;

/**
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
trait MagicSetGetTrait
{

    protected $data = [];

    public function __call($name, $args)
    {
        if (substr($name, 0, 6) === "access") {
            if (substr($name, 0, 7) === "access_") { // assume snake_case
                $propname = substr($name, 7);
            } else { // assume camelCase
                $propname = self::fromCamelCase(substr($name, 6));
            }
            return $this->set($propname, Struct::CREATE);  // treats it as a struct
        }

        if (substr($name, 0, 3) === "set") {
            if (substr($name, 0, 4) === "set_") { // assume snake_case
                $propname = substr($name, 4);
            } else { // assume camelCase
                $propname = self::fromCamelCase(substr($name, 3));
            }
            $value = (array_key_exists(0, $args) ? $args[0] : Struct::DELETE);
            return $this->set($propname, $value);  // defaults to first argument
        }

        if (substr($name, 0, 3) === "get") { // always snake_case internally
            $propname = self::fromCamelCase(substr($name, 3));
            // echo "Propname: ".$propname."\n";
            return $this->get($propname, (isset($args[0]) ? $args[0] : null));  // defaults to first argument
        }
    }

    public function set($propname, $value = Struct::DELETE)
    {
        if ($value === Struct::CREATE) { // chaining of empty setters allow us to set deep nested struct values
            if (!array_key_exists($propname, $this->data) || !( $this->data[$propname] instanceof static )) {
                // echo "Defining: ". $propname . "\n";
                $this->data[$propname] = new static();

                // setters with no arguments do not chain, they nest.  Return new struct.
            }
            return $this->data[$propname];
        } elseif ($value === Struct::DELETE) {
            if (array_key_exists($propname, $this->data)) {
                unset($this->data[$propname]);
            }
        } else {
            // echo $propname.": ".$value ."\n";
            $this->data[$propname] = $value;
        }

        // regular setters do not nest, they chain.  Return this object.
        return $this;
    }

    public function access($propname)
    {
        if (!array_key_exists($propname, $this->data) || !( $this->data[$propname] instanceof static )) {
            // echo "Defining: ". $propname . "\n";
            $this->data[$propname] = new static();
        }

        // getters do not chain, they nest.  Return value or new struct.
        return $this->data[$propname];
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

    private static function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}
