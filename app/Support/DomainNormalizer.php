<?php

namespace App\Support;

class DomainNormalizer
{
    public static function normalize(?string $domain): ?string
    {
        if ($domain === null) {
            return null;
        }

        $value = strtolower(trim($domain));
        if ($value === '') {
            return null;
        }

        $value = preg_replace('#^https?://#', '', $value) ?? $value;
        $value = trim($value, '/');

        $parsedHost = parse_url('http://' . $value, PHP_URL_HOST);
        if (!is_string($parsedHost) || $parsedHost === '') {
            return null;
        }

        $parsedHost = strtolower(trim($parsedHost));
        $parsedHost = preg_replace('/^www\./', '', $parsedHost) ?? $parsedHost;
        $parsedHost = trim($parsedHost, '.');

        return $parsedHost !== '' ? $parsedHost : null;
    }
}
