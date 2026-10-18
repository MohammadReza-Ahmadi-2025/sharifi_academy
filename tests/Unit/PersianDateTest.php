<?php

namespace Tests\Unit;

use App\Support\PersianDate;
use Carbon\Carbon;
use Tests\TestCase;

class PersianDateTest extends TestCase
{
    public function test_formats_gregorian_date_as_jalali_label(): void
    {
        $label = PersianDate::formatDate(Carbon::parse('2024-03-20 12:00:00'));

        $this->assertNotNull($label);
        $this->assertStringContainsString('1403', $label);
        $this->assertStringContainsString('فروردین', $label);
    }

    public function test_format_display_value_parses_api_datetime_strings(): void
    {
        $label = PersianDate::formatDisplayValue('2024-03-20 15:30:00');

        $this->assertStringContainsString('1403', $label);
        $this->assertStringContainsString('فروردین', $label);
        $this->assertStringContainsString('15:30', $label);
    }
}
