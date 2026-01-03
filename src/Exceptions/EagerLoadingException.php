<?php

namespace Parental\Exceptions;

use RuntimeException;

class EagerLoadingException extends RuntimeException
{
    public static function throwOnUnsupportedLaravelVersions(): void
    {
        if (version_compare(app()->version(), '11.0.0', '<')) {
            throw new self('Eager loading on Parental models are only available in Laravel 11 and above.');
        }
    }
}
