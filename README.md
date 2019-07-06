Struct
======

PHP Class to facilitate querying and manipulation of nested arrays

Usage
=====

    use \Neitanod\Struct\Struct;

    $s = new Struct();
    $something = $s->getSomething('default');

Will read $s['something'] and return it's value.  "default" if it does not
exist (as is the case in the above example).

    $s
      ->setSomething('some val')
      ->setSomethingElse('some other val')
      ->setYetSomethingElse('some other val');

We can convert the complete struct to a regular array:

    $as_array = $s->toArray();

Or to JSON directly:

    echo $s->toJson();

The above code will print: 

    { 
      "something" => "some val",
      "something_else" => "some other val",
      "yet_something_else" => "some other val",
    }

Using exact keys
----------------

As we saw in the above example, using the `setSomeVarName('the value')` style
will internally store everything using snake case keys.   It's equivalent to
assigning directly `$arr['some_var_name'] = 'the value';`

But we can also use exact keys:

    $s
      ->set('something!', 'some val')
      ->set('SomethingElse', 'some other val')
      ->set('Yet_Something else', 'some other val')
    ;

    echo $s->toJson();

Will print: 

    { 
      "something!" => "some val",
      "SomethingElse" => "some other val",
      "Yet_Something else" => "some other val",
    }

Creating and querying nested arrays
-----------------------------------

The `access` method tells the struct to return a new struct (or current stored
struct) as the value to return, but instead of just returning it, it also sets
it, so the full path gets built and you can assign values to the created struct.

If there's already a struct there, it will use it instead of creating an
empty one (it will preserve any other value it has).

    $s
      ->accessSomething()
      ->access('SomethingElse')
      ->set('Yet_Something else', 'some assigned val')
    ;

The above instruction creates the tree and assigns the value.

    $obtained  = $s
      ->accessSomething()
      ->access('SomethingElse')
      ->get('Yet_Something else', 'some default val')
    ;

The above instruction creates the tree and reads the value.
As it already exists it wont use the default.

    echo $val;

Will print: 
    
    some assigned val

    $s
      ->accessSomething()
      ->access('SomethingElse')
      ->setMyNewKey('my new value')
    ;

    echo $s->toJson();

Will print: 

    { 
      "something" => {
        "SomethingElse" => {
          "Yet_Something else" => "some assigned val",
          "my_new_key" => "my new value",
        }
      }
    }

Note that the last assignment added the new key but did not replace the struct
it was in, preserving the `Yet_Something else` key defined before.


