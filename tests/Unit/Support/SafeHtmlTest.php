<?php

namespace Tests\Unit\Support;

use App\Support\SafeHtml;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SafeHtmlTest extends TestCase
{
    #[Test]
    public function it_keeps_safe_markup_and_strips_scripts(): void
    {
        $html = '<p>سلام <strong>دنیا</strong></p><script>alert(1)</script><p onclick="evil()">x</p>';

        $clean = SafeHtml::clean($html);

        $this->assertStringContainsString('<p>سلام <strong>دنیا</strong></p>', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringContainsString('<p>x</p>', $clean);
    }

    #[Test]
    public function it_blocks_javascript_urls_and_iframes(): void
    {
        $html = '<a href="javascript:alert(1)">لینک</a><iframe src="https://evil.test"></iframe>';

        $clean = SafeHtml::clean($html);

        $this->assertStringNotContainsString('javascript:', strtolower($clean));
        $this->assertStringNotContainsString('<iframe', strtolower($clean));
    }

    #[Test]
    public function it_returns_null_for_empty_nullable(): void
    {
        $this->assertNull(SafeHtml::nullable('   '));
        $this->assertNull(SafeHtml::nullable(null));
        $this->assertSame('<p>ok</p>', SafeHtml::nullable('<p>ok</p>'));
    }
}
