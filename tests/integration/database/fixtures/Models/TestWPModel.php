<?php

declare(strict_types=1);

namespace Tests\integration\database\fixtures\Models;

use Snicco\Database\Illuminate\WPModel;
use Snicco\Database\Illuminate\WithFactory;

class TestWPModel extends WPModel
{
    
    use WithFactory;
}