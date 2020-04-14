<?php
require_once(dirname(__FILE__)."/Test.class.php");
require_once(dirname(dirname(__FILE__))."/src/Struct/Struct.php");

use \Neitanod\Struct\Struct;

// Test the testing class itself.
Test::is("'yes' is true", 'yes', true);
Test::not("1 is not false", 1, false);
Test::identical("true is identical to true", true, true);
Test::true("1 is true", 1);

// Struct tests.


Test::is("new Struct(array) gets correctly loaded and toArray works too", (new Struct(["a" => 1, "b" => 2, "c" => [1, 2, 3]]))->toArray(), ["a" => 1, "b" => 2, "c" => [1, 2, 3]]);
Test::is("new Struct(array) is alias of (new Struct())->fromArray()", new Struct(["a" => 1, "b" => 2, "c" => [1, 2, 3]]), (new Struct())->fromArray(["a" => 1, "b" => 2, "c" => [1, 2, 3]]));
Test::is("toJson works correctly", (new Struct(["a" => 1, "b" => 2, "c" => [1, 2, 3]]))->toJson(),
'{
    "a": 1,
    "b": 2,
    "c": [
        1,
        2,
        3
    ]
}');

function create_from_object()
{

    $obj = new stdClass();
    $obj2 = new stdClass();

    $obj2->x = "a";
    $obj2->y = "b";
    $obj2->z = 1000;

    $obj->a = 1;
    $obj->b = 2;
    $obj->c = [1, 2, 3];
    $obj->d = $obj2;

    $s = new Struct($obj);

    return $s;
}

function test_create_from_object()
{

    $s = create_from_object();

    $json =
    '{
    "a": 1,
    "b": 2,
    "c": [
        1,
        2,
        3
    ],
    "d": {
        "x": "a",
        "y": "b",
        "z": 1000
    }
}';

    return $json === $s->toJson();
}

Test::true("new Struct(object) gets correctly loaded", test_create_from_object());

function test_read_access_method()
{
    $s = create_from_object();
    $value = $s->access('d')->getZ('default');
    return $value === 1000;
}

Test::true("test read", test_read_access_method());

function test_read_magic_method()
{
    $s = create_from_object();
    $value = $s->accessD()->get('y', 'default');
    return $value === 'b';
}

Test::true("test read", test_read_magic_method());

function test_delete_method()
{
    $s = create_from_object();
    $b = $s->getB('default');
    $s->setB();
    $null = $s->getB();
    $default = $s->getB('default');
    $s->set('b', null);
    $must_be_null = $s->getB('not_null');
    $s->setA(null);
    $must_be_null_too = $s->getA('not_null');
    return
        $b === 2 &&
        is_null($null) &&
        is_null($must_be_null) &&
        is_null($must_be_null_too) &&
        $default === 'default';
}

Test::true("test delete", test_delete_method());

function test_default_read()
{
    $s = new Struct();
    $default = $s->getSomething('default');

    return $default == 'default';
}

function test_nodefault_read()
{
    $s = new Struct();
    $default = $s->getSomething();

    return is_null($default);
}

function test_access_read()
{
    $s = new Struct();
    $newstruct = $s->accessSomething();

    return ($newstruct instanceof Struct && $newstruct != $s);
}

function test_arrayaccess_read()
{
    $s = new Struct();
    $newstruct = $s["something"];

    return ($newstruct instanceof Struct && $newstruct != $s);
}

function test_deep_read()
{
    $s = new Struct();
    $default = $s->accessSomething()->getSomethingElse('default');
    $s->accessSomething()->setSomethingElse('assigned');
    $assigned = $s->accessSomething()->getSomethingElse('default');

    return $assigned == 'assigned';
}

function test_deep_read_default()
{
    $s = new Struct();
    $default = $s->accessSomething()->getSomethingElse('default');
    $s->accessSomething()->setSomethingElse('assigned');
    $assigned = $s->accessSomething()->getSomethingElse('default');

    return $default == 'default';
}

function test_deep_read_with_set()
{
    $s = new Struct();
    $default = $s->setSomething()->getSomethingElse('default');

    return $default == 'default';
}

Test::true("Obtain default value from argument", test_default_read());
Test::true("Obtain null when no default value given", test_nodefault_read());
Test::true("Obtain struct when using access", test_access_read());
Test::true("Obtain struct when using array access on undefined", test_arrayaccess_read());
Test::true("Deep path query, obtain assigned", test_deep_read());
Test::true("Deep path query, obtain default", test_deep_read_default());
Test::true("Deep path query with SET, obtain default", test_deep_read_with_set());

function test_default_assign()
{
    $s = new Struct();
    $s->setSomething('assigned');

    $read = $s->getSomething('default');

    return $read == 'assigned';
}

function test_default_assign_exact_key()
{
    $s = new Struct();
    $s->set('__something_with_EXACT_key', 'assigned');

    $read = $s->get('__something_with_EXACT_key', 'default');

    return $read == 'assigned';
}

function test_deep_assign()
{
    $s = new Struct();
    $s->accessSomething()->setSomethingElse('assigned');
    $read = $s->accessSomething()->getSomethingElse('default');

    return $read == 'assigned';
}

function test_deep_assign_with_set()
{
    $s = new Struct();
    $s->setSomething('xx');
    $s->setSomething();

    $s->accessSomething()->setSomethingElse('assigned');
    $read = $s->accessSomething()->getSomethingElse('default');

    return $read == 'assigned';
}

Test::true("Assign and read simple value", test_default_assign());
Test::true("Assign and read simple value with exact key", test_default_assign_exact_key());
Test::true("Deep path assign", test_deep_assign());
Test::true("Deep path assign with SET", test_deep_assign_with_set());

function test_chained_assign()
{
    $s = new Struct();
    $s
        ->setSomething('something assigned')
        ->setSomethingElse('something else assigned');
    $json =
    '{
    "something": "something assigned",
    "something_else": "something else assigned"
}';

    return $json === $s->toJson();
}

Test::true("Chained assign", test_chained_assign());

function test_chained_assign_exact_key()
{
    $s = new Struct();
    $s
        ->set('__somethingXX', 'something assigned')
        ->set('SomethingElse', 'something else assigned');
    $json =
    '{
    "__somethingXX": "something assigned",
    "SomethingElse": "something else assigned"
}';

    return $json === $s->toJson();
}

Test::true("Chained assign with exact key", test_chained_assign_exact_key());

function test_arrayaccess_assign()
{
    $s = new Struct();
    $s['a']['b']['c'] = "ABC";
    $s['a']['b']['c2'] = "ABC2";
    $s['a']['b2']['c'] = "AB2C";
    $json =
    '{
    "a": {
        "b": {
            "c": "ABC",
            "c2": "ABC2"
        },
        "b2": {
            "c": "AB2C"
        }
    }
}';
    $s['a']['b2']->get('d', "default");

    return $json === $s->toJson();
}

Test::true("Arrayaccess assign", test_arrayaccess_assign());

function test_arrayaccess_get()
{
    $s = new Struct();
    $s['a']['b']['c'] = "ABC";
    $s['a']['b']['c2'] = "ABC2";
    $s['a']['b2']['c'] = "AB2C";
    $json =
    '{
    "a": {
        "b": {
            "c": "ABC",
            "c2": "ABC2"
        },
        "b2": {
            "c": "AB2C"
        }
    }
}';
    $default = $s['a']['b2']->get('d', "default");

    return $json === $s->toJson() && $default == "default";
}

Test::true("Arrayaccess get", test_arrayaccess_get());

function test_chained_unset()
{
    $s = new Struct();
    $s['a']['b']['c'] = "ABC";
    $s['a']['b']['c2'] = "ABC2";
    $s['a']['b2']['c'] = "AB2C";
    $json =
    '{
    "a": {
        "b": {
            "c2": "ABC2"
        }
    }
}';
    $default = $s['a']->unset('b2')['b']->unset('c');

    return $json === $s->toJson();
}

Test::true("Chained unset", test_chained_unset());

function test_clone_and_unset()
{
    $s = new Struct();
    $s['a']['b']['c'] = "ABC";
    $s['a']['b']['c2'] = "ABC2";
    $s['b']['b2']['c'] = "AB2C";
    $jsona = '{
    "a": {
        "b": {
            "c": "ABC",
            "c2": "ABC2"
        }
    }
}';

    $jsonb = '{
    "b": {
        "b2": {
            "c": "AB2C"
        }
    }
}';
    $sa = $s->clone()->unset('b');
    $sb = $s->clone()->unset('a');

    return
        $jsona === $sa->toJson() &&
        $jsonb === $sb->toJson();
}

Test::true("Clone and unset", test_clone_and_unset());

function test_not_empty_is_not_empty()
{
    $s = new Struct();
    $s['a']['b']['c'] = "Hello";
    return !empty($s['a']['b']['c']);
}

Test::true("Not empty element returns not empty", test_not_empty_is_not_empty());

function test_not_empty_path_is_not_empty()
{
    $s = new Struct();
    $s['a']['b']['c'] = "Hello";
    return !empty($s['a']['b']);
}

Test::true("Not empty path returns not empty", test_not_empty_path_is_not_empty());

function test_empty_path_is_empty()
{
    $s = new Struct();
    $s['a']['b']['c'] = "Hello";
    return empty($s['a']['b']['d']);
}

Test::true("Empty path returns empty", test_empty_path_is_empty());


Test::totals();
