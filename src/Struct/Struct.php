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

require_once(__dir__ . '/Traits/MagicSetGetTrait.php');
require_once(__dir__ . '/Traits/LoaderDumperTrait.php');

use Neitanod\Struct\Traits\MagicSetGetTrait;
use Neitanod\Struct\Traits\LoaderDumperTrait;

interface TraversableStruct extends \Traversable
{

}

class Struct implements \ArrayAccess, \IteratorAggregate, TraversableStruct
{

    use MagicSetGetTrait;
    use LoaderDumperTrait;

    public const CREATE = '__struct__create__aslkhsdafkhfsd__khadsfhusdfajhu___';
    public const DELETE = '__struct__delete__aslkhsdafkhfsd__khadsfhusdfajhu___';

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }




    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->data[$offset]) ? $this->get($offset) : $this->access($offset);
    }
}
