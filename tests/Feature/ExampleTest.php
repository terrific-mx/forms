<?php

use function Pest\Laravel\get;

it('returns a redirect to the dashboard', function () {
    get('/')->assertRedirect('dashboard');
});
