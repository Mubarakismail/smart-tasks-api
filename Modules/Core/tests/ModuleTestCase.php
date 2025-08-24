<?php

namespace Modules\Core\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase as BaseTestCase;

abstract class ModuleTestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;
}
