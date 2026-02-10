<?php

test('homepage redirects to reservation page', function () {
    $response = $this->get('/');

    $response->assertRedirect('/reservation');
});
