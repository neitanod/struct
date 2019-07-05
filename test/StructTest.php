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

function test_create_from_object() {

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

function test_default_read()
{
    $s = new Struct();
    $default = $s->getSomething('default');

    return $default == 'default';
}

function test_deep_read()
{
    $s = new Struct();
    $default = $s->getSomething(Struct::CREATE)->getSomethingElse('default');

    return $default == $default;
}

function test_deep_read_with_set()
{
    $s = new Struct();
    $default = $s->setSomething()->getSomethingElse('default');

    return $default == $default;
}

Test::true("Obtain default value from argument", test_default_read());
Test::true("Deep path query, obtain default", test_deep_read());
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
    $s->getSomething(Struct::CREATE)->setSomethingElse('assigned');
    $read = $s->getSomething(Struct::CREATE)->getSomethingElse('default');

    return $read == 'assigned';
}

function test_deep_assign_with_set()
{
    $s = new Struct();
    $s->setSomething()->setSomethingElse('assigned');
    $read = $s->getSomething()->getSomethingElse('default');

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

Test::totals();
