<?php

it('redirects to dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('dashboard'));
});
