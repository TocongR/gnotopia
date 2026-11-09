<?php

use App\Support\VideoEmbed;

it('converts supported links to embed urls', function (string $url, string $expected) {
    expect(VideoEmbed::toEmbedUrl($url))->toBe($expected);
})->with([
    'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    'youtube shorts' => ['https://www.youtube.com/shorts/dQw4w9WgXcQ', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    'vimeo' => ['https://vimeo.com/76979871', 'https://player.vimeo.com/video/76979871'],
]);

it('rejects unsupported links', function (?string $url) {
    expect(VideoEmbed::toEmbedUrl($url))->toBeNull();
})->with([
    'null' => [null],
    'empty' => [''],
    'plain file' => ['https://example.com/a.mp4'],
    'javascript' => ['javascript:alert(1)'],
    'lookalike host' => ['https://youtube.com.evil.com/watch?v=dQw4w9WgXcQ'],
    'other host' => ['https://evil.com/watch?v=dQw4w9WgXcQ'],
]);
