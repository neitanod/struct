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
        if (substr($name, 0, 3) === "set") {
            if (substr($name, 0, 4) === "set_") { // assume snake_case
                $propname = substr($name, 4);
            } else { // assume camelCase
                $propname = self::fromCamelCase(substr($name, 3));
            }
            return $this->set($propname, (isset($args[0]) ? $args[0] : Struct::CREATE));  // defaults to first argument
        }

        if (substr($name, 0, 3) === "get") { // always snake_case internally
            $propname = self::fromCamelCase(substr($name, 3));
            // echo "Propname: ".$propname."\n";
            return $this->get($propname, (isset($args[0]) ? $args[0] : null));  // defaults to first argument
        }
    }

    public function set($propname, $value = Struct::CREATE)
    {
        if ($value === Struct::CREATE) { // chaining of empty setters allow us to set deep nested struct values
            if (!array_key_exists($propname, $this->data)) {
                // echo "Defining: ". $propname . "\n";
                $this->data[$propname] = new static();

                // setters with no arguments do not chain, they nest.  Return new struct.
            }
            return $this->data[$propname];
        } else {
            $this->data[$propname] = $value;
        }

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
        return isset($this->data[$propname]) ? $this->data[$propname] : $useValue;
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
