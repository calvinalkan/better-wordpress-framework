<?php

declare(strict_types=1);

namespace Tests\integration\better_wp_mail\fixtures\Emails;

use Snicco\Mail\Email;

final class HtmlMail extends Email
{
    
    public function configure()
    {
        $this->html('<h1>Hello World</h1>')
             ->subject('Hi');
    }
    
}