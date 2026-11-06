<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_dangerous_elements_are_removed_with_their_content(): void
    {
        $clean = HtmlSanitizer::clean('<p>Aman</p><script>alert("x")</script><style>body{display:none}</style>');

        $this->assertSame('<p>Aman</p>', $clean);
    }

    public function test_event_handler_attributes_are_removed(): void
    {
        $clean = HtmlSanitizer::clean('<img src="/foto.png" onerror="alert(1)" alt="Foto">');

        $this->assertStringNotContainsString('onerror', (string) $clean);
        $this->assertStringContainsString('src="/foto.png"', (string) $clean);
        $this->assertStringContainsString('alt="Foto"', (string) $clean);
    }

    public function test_javascript_and_data_urls_are_removed(): void
    {
        $clean = (string) HtmlSanitizer::clean('<a href="javascript:alert(1)">Klik</a><img src="data:text/html;base64,PHNjcmlwdD4=">');

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringNotContainsString('data:', $clean);
        $this->assertStringContainsString('>Klik</a>', $clean);
    }

    public function test_svg_and_math_payloads_are_removed(): void
    {
        $clean = (string) HtmlSanitizer::clean('<svg onload="alert(1)"><circle /></svg><math><mi>x</mi></math><p>Ok</p>');

        $this->assertStringNotContainsString('<svg', $clean);
        $this->assertStringNotContainsString('<math', $clean);
        $this->assertStringContainsString('<p>Ok</p>', $clean);
    }

    public function test_unknown_tags_are_unwrapped_but_text_is_kept(): void
    {
        $clean = (string) HtmlSanitizer::clean('<custom-tag>Isi penting</custom-tag>');

        $this->assertStringNotContainsString('<custom-tag', $clean);
        $this->assertStringContainsString('Isi penting', $clean);
    }

    public function test_safe_markup_is_preserved(): void
    {
        $html = '<h2>Judul</h2><p><strong>Bold</strong> dan <em>miring</em></p><ul><li>Satu</li></ul>';

        $this->assertSame($html, HtmlSanitizer::clean($html));
    }

    public function test_external_links_get_rel_noopener(): void
    {
        $clean = (string) HtmlSanitizer::clean('<a href="https://bnsp.go.id" target="_blank">BNSP</a>');

        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_null_and_empty_values(): void
    {
        $this->assertNull(HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean(''));
    }
}
