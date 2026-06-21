<?php

namespace Tests\Feature;

use Tests\TestCase;

class RequiredPhpExtensionsTest extends TestCase
{
    /**
     * Guards against env/build drift dropping an extension the app relies on.
     * bcmath in particular powers checkout money math; without it cash checkout
     * fatals at runtime (see Dockerfile build-time check for the prod image).
     *
     * @return array<int, array{0: string}>
     */
    public static function requiredExtensions(): array
    {
        return [
            ['bcmath'],
            ['pdo_mysql'],
            ['mbstring'],
            ['intl'],
            ['gd'],
        ];
    }

    /**
     * @dataProvider requiredExtensions
     */
    public function test_required_php_extension_is_loaded(string $extension): void
    {
        $this->assertTrue(
            extension_loaded($extension),
            "Required PHP extension '{$extension}' is not loaded."
        );
    }
}
