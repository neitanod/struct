Struct
======

PHP Class to facilitate querying and manipulation of nested arrays

Usage:
======

    use \Neitanod\Struct\Struct;

    $s = new Struct();
    $something = $s->getSomething('default');

    $s
      ->setSomething('some val')
      ->setSomethingElse('some other val')
      ->setYetSomethingElse('some other val')
    ;

    $as_array = $s->toArray();

    echo $s->toJson();

    Will print: 

    { 
      "something" => "some val",
      "something_else" => "some other val",
      "yet_something_else" => "some other val",
    }

Exact keys:
-----------

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

Creating and querying nested arrays:
------------------------------------

    Struct::CREATE is a special value that tells the struct to return a new 
    struct as the default value to return, but instead of just returning it, it 
    also sets it, so the full path gets built and you can assign values to the
    created struct.
    If there's already a struct there, it will use it instead of creating an
    empty one.

    $s
      ->getSomething(Struct::CREATE)
      ->get('SomethingElse', Struct::CREATE)
      ->set('Yet_Something else', 'some assigned val')
    ;

    // The above instruction creates the tree and assigns the value

    $obtained  = $s
      ->getSomething(Struct::CREATE)
      ->get('SomethingElse', Struct::CREATE)
      ->get('Yet_Something else', 'some default val')
    ;

    // The above instruction creates the tree and reads the value.
    // As it already exists it wont use the default.

    echo $val;

    Will print: 
    
    some assigned val

    echo $s->toJson();

    Will print: 

    { 
      "something" => {
        "SomethingElse" => {
          "Yet_Something else" => "some assigned val",
        }
      }
    }

