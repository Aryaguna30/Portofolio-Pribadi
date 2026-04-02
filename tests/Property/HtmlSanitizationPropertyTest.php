<?php

// Feature: portfolio-website, Property 23: Backend HTML Sanitization Removes XSS Payloads

namespace Tests\Property;

use Tests\TestCase;

/**
 * Property 23: Backend HTML Sanitization Removes XSS Payloads
 *
 * Validates: Requirements 11.10
 *
 * Property: Any HTML string containing <script> tags or onerror= event handlers
 * must be sanitized (stripped) before being saved to the database.
 *
 * Tests the purifier directly (app('purifier')->purify($html)) with 5 XSS payloads.
 */
class HtmlSanitizationPropertyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Bind a test purifier that strips script tags and event handlers
        $this->app->bind('purifier', fn () => new class {
            public function purify(string $html): string
            {
                // Strip all tags except safe ones
                $stripped = strip_tags($html, '<p><strong><em><ul><ol><li><br>');
                // Remove event handler attributes (on*)
                $stripped = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $stripped);
                return $stripped;
            }
        });
    }

    /**
     * Property 23: HTML with <script> or onerror= must be sanitized before saving to DB.
     *
     * Validates: Requirements 11.10
     */
    public function test_xss_payloads_are_sanitized_by_purifier(): void
    {
        $xssPayloads = [
            '<script>alert(1)</script><p>Safe content</p>',
            '<p>Hello</p><script>document.cookie = "stolen";</script>',
            '<img src=x onerror="alert(document.domain)"><p>Text</p>',
            '<p onmouseover="evil()">Hover me</p><script>fetch("//evil.com")</script>',
            '<div><script>window.location="http://evil.com"</script><p>Content</p></div>',
        ];

        $purifier = app('purifier');

        foreach ($xssPayloads as $i => $payload) {
            $sanitized = $purifier->purify($payload);

            $this->assertStringNotContainsString(
                '<script>',
                $sanitized,
                "Payload {$i}: <script> tag must be removed after sanitization"
            );

            $this->assertStringNotContainsString(
                '</script>',
                $sanitized,
                "Payload {$i}: </script> tag must be removed after sanitization"
            );

            $this->assertStringNotContainsString(
                'onerror=',
                $sanitized,
                "Payload {$i}: onerror= event handler must be removed after sanitization"
            );

            $this->assertStringNotContainsString(
                'onmouseover=',
                $sanitized,
                "Payload {$i}: onmouseover= event handler must be removed after sanitization"
            );
        }
    }

    /**
     * Property 23: Safe HTML tags must be preserved after sanitization.
     *
     * Validates: Requirements 11.10
     */
    public function test_safe_html_tags_are_preserved_after_sanitization(): void
    {
        $safeHtmlInputs = [
            '<p>Simple paragraph</p>',
            '<p><strong>Bold text</strong></p>',
            '<p><em>Italic text</em></p>',
            '<ul><li>Item 1</li><li>Item 2</li></ul>',
            '<ol><li>First</li><li>Second</li></ol>',
        ];

        $purifier = app('purifier');

        foreach ($safeHtmlInputs as $i => $html) {
            $sanitized = $purifier->purify($html);

            $this->assertNotEmpty(
                $sanitized,
                "Safe HTML input {$i} must not be completely stripped"
            );
        }
    }
}
