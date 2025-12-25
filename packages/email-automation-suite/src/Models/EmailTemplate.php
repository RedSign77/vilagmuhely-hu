<?php

/*
 * Webtech-solutions 2025, All rights reserved.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [
        'code',
        'subject',
        'body',
        'description',
    ];

    /**
     * Replace variables in template content with actual values
     */
    public function replaceVariables(array $variables): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($variables as $key => $value) {
            $placeholder = '{{ ' . $key . ' }}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
