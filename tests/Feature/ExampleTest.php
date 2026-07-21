<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

test('the application exposes the DevScout brand icon', function () {
    $this->get('/login')
        ->assertSuccessful()
        ->assertSee('/brand/devscout-icon.png', false);

    expect(public_path('brand/devscout-icon.png'))->toBeFile()
        ->and(public_path('brand/devscout-logo.png'))->toBeFile();
});
