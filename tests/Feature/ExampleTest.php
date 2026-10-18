<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La raíz redirige a los invitados al login.
     */
    public function test_guests_are_redirected_to_login_from_root(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
