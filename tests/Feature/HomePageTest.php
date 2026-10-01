<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_home_page_loads(): void
    {
        $this->get('/')->assertOk();
    }
}
