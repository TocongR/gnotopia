<?php

namespace App\Support;

class VideoEmbed
{
    /**
     * Turn a YouTube or Vimeo link into a safe embed URL.
     * Returns null for anything else, so we never iframe arbitrary sites.
     */
    public static function toEmbedUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $parts = parse_url(trim($url));

        if (! $parts || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower(preg_replace('/^www\./', '', $parts['host']));
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        if ($host === 'youtu.be' && preg_match('#^/([\w-]{11})$#', $path, $m)) {
            return self::youtube($m[1]);
        }

        if (in_array($host, ['youtube.com', 'm.youtube.com'], true)) {
            $v = $query['v'] ?? null;

            if ($path === '/watch' && is_string($v) && preg_match('/^[\w-]{11}$/', $v)) {
                return self::youtube($v);
            }

            if (preg_match('#^/(?:embed|shorts)/([\w-]{11})$#', $path, $m)) {
                return self::youtube($m[1]);
            }
        }

        if ($host === 'vimeo.com' && preg_match('#^/(\d+)$#', $path, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    private static function youtube(string $id): string
    {
        return 'https://www.youtube-nocookie.com/embed/'.$id;
    }
}
