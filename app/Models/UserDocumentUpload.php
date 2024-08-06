<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class UserDocumentUpload extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function document() {
        return $this->belongsTo(RequiredDocument::class, 'document_id');
    }
}
