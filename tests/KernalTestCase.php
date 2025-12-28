<?php

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class KernalTestCase extends KernelTestCase
{
    public static function boot()
    {
        static::bootKernel();
    }

    /**
     * @psalm-param class-string<T> $className
     *
     * @psalm-return T
     *
     * @template T of object
     */
    public function service(string $className): object
    {
        return static::getContainer()->get($className);
    }
}
