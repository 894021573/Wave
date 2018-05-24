<?php return array (
  'test-index' => 
  array (
    0 => 
    array (
      'no' => 1,
      'methods' => 
      array (
        0 => 'GET',
      ),
      'route' => 'test/index',
      'handler' => 'TestController@index',
    ),
  ),
  'user' => 
  array (
    0 => 
    array (
      'no' => 2,
      'methods' => 
      array (
        0 => 'GET',
        1 => 'POST',
      ),
      'route' => 'user/{id:\\d+}',
      'handler' => 'UserController@aaa',
    ),
  ),
  'bbb' => 
  array (
    0 => 
    array (
      'no' => 3,
      'methods' => 
      array (
        0 => 'GET',
      ),
      'route' => 'bbb/{id:\\d+}',
      'handler' => 'UserController@bbb',
    ),
  ),
  'ccc' => 
  array (
    0 => 
    array (
      'no' => 4,
      'methods' => 
      array (
        0 => 'GET',
      ),
      'route' => 'ccc',
      'handler' => 'UserController@ccc',
    ),
  ),
  'eee' => 
  array (
    0 => 
    array (
      'no' => 5,
      'methods' => 
      array (
        0 => 'GET',
      ),
      'route' => 'eee',
      'handler' => 'UserController@eee',
    ),
  ),
);