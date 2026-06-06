<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $compiledPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-test-views'.DIRECTORY_SEPARATOR.uniqid(str_replace('\\', '_', static::class).'_', true);

        File::ensureDirectoryExists($compiledPath);

        config()->set('view.compiled', $compiledPath);
    }
}
