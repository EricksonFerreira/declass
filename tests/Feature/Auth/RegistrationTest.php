<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users can register and then log in', function () {
    $email = 'reg-login@example.com';
    $password = 'password';

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $this->post('/logout');
    $this->assertGuest();

    $loginResponse = $this->post('/login', [
        'email' => $email,
        'password' => $password,
    ]);

    $this->assertAuthenticated();
    $loginResponse->assertRedirect(route('dashboard', absolute: false));
});
