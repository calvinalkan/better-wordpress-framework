<?php

declare(strict_types=1);

namespace Tests\integration\betterwphooks\fixtures;

use Snicco\EventDispatcher\ClassAsName;
use Snicco\EventDispatcher\ClassAsPayload;
use Tests\integration\betterwphooks\fixtures;

class LogEvent1 implements fixtures\LoggableEvent
{
    
    use ClassAsName;
    use ClassAsPayload;
    
    private $message;
    
    public function __construct($message)
    {
        $this->message = $message;
    }
    
    public function message()
    {
        return $this->message;
    }
    
}