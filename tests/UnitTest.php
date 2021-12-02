<?php

declare(strict_types=1);

namespace Tests;

use Codeception\PHPUnit\TestCase;

class UnitTest extends TestCase
{
    
    protected function setUp() :void
    {
        parent::setUp();
        $GLOBALS['test'] = [];
    }
    
}