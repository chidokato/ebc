<?php

namespace App\Support;

class LocalizedUrl
{
    public static function route(string $name, array $parameters = []): string
    {
        if (($parameters['locale'] ?? 'vi') === 'vi') {
            unset($parameters['locale']);
            $name .= '.vi';
        }

        return route($name, $parameters);
    }
}
