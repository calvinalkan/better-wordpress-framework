<?php

declare(strict_types=1);

namespace Tests\integration\eloquent\fixtures\Models;

class Activity extends TestWPModel
{
    
    public function cities()
    {
        return $this->belongsToMany(City::class);
    }
    
}