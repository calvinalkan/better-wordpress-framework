<?php

declare(strict_types=1);

namespace Tests\integration\better_wp_hooks\fixtures;

use Tests\concerns\AssertListenerResponse;

class ClassListener2
{
    
    use AssertListenerResponse;
    
    public function handle(FooEvent $event)
    {
        $this->respondedToEvent(FooEvent::class, ClassListener2::class, $event->val);
    }
    
}