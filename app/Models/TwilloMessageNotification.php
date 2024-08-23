<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwilloMessageNotification extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function twillotemplate()
    {
        return $this->belongsTo(TwilloTemplate::class, 'template_id', 'contentsid');
    }
}
