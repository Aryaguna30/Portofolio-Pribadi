<?php

namespace Tests\Unit\Stubs;

use Illuminate\Container\Container;
use Intervention\Image\Interfaces\ImageInterface;

/**
 * Minimal stub that mimics Intervention\Image\Laravel\Facades\Image.
 * Used in unit tests when the intervention/image-laravel package is not installed.
 */
class ImageFacadeStub
{
    private static ?Container $app = null;

    public static function setApp(Container $app): void
    {
        self::$app = $app;
    }

    public static function read(mixed $input): ImageInterface
    {
        /** @var \Intervention\Image\Interfaces\ImageManagerInterface $manager */
        $manager = self::$app->make('image');

        return $manager->read($input);
    }
}
