<?php

declare(strict_types=1);

namespace Tests\integration\betterwphooks\fixtures;

use Snicco\EventDispatcher\Contracts\Event;

abstract class AbstractLogin implements Event
{
    
    abstract public function message();
    
}