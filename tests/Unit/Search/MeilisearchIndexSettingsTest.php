<?php

namespace Tests\Unit\Search;

use Tests\TestCase;

class MeilisearchIndexSettingsTest extends TestCase
{
    public function test_site_indexes_enable_typo_tolerance_for_short_persian_words(): void
    {
        $settings = config('scout.meilisearch.index-settings');

        foreach (['courses', 'products', 'brands', 'categories', 'posts'] as $index) {
            $this->assertArrayHasKey($index, $settings);
            $this->assertTrue($settings[$index]['typoTolerance']['enabled']);
            $this->assertLessThanOrEqual(
                4,
                $settings[$index]['typoTolerance']['minWordSizeForTypos']['oneTypo'],
                "{$index} must allow one typo for 4-letter titles like کتیا",
            );
            $this->assertContains('title', $settings[$index]['searchableAttributes']);
        }
    }
}
