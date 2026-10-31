<?php

namespace Tests\Unit\Search;

use App\Support\Search\PersianSearchQuery;
use PHPUnit\Framework\TestCase;

class PersianSearchQueryTest extends TestCase
{
    public function test_expands_first_letter_neighbors_for_katia_typo(): void
    {
        $variants = (new PersianSearchQuery)->variants('گتیا');

        $this->assertContains('گتیا', $variants);
        $this->assertContains('کتیا', $variants);
    }

    public function test_normalizes_arabic_kaf_and_yeh(): void
    {
        $variants = (new PersianSearchQuery)->variants('كتيا');

        $this->assertContains('کتیا', $variants);
    }
}
