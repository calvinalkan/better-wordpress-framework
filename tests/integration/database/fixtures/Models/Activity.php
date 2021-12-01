<?php

declare(strict_types=1);

namespace Tests\integration\database\fixtures\Models;

class Activity extends TestWPModel
{
    
    public function cities()
    {
        return $this->belongsToMany(City::class);
    }
    
}