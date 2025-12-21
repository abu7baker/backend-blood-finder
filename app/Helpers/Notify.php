<?php

namespace App\Helpers;

use App\Models\Notification;

class Notify
{
    public static function send($user_id, $title, $body, $type = 'info')
    {
        Notification::create([
            'user_id' => $user_id,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'is_read' => false,
        ]);
    }
}
