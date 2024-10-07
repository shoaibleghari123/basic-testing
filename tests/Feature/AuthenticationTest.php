<?php

//test('unauthenticated user cannot access products', function () {
//    $this->get('/products')->assertRedirect('/login');
//});

//shorter version of the above test
test('unauthenticated user cannot access products')
    ->get('/products')->assertRedirect('/login');
