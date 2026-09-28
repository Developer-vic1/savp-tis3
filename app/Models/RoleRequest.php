<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleRequest extends Model
{
    protected $fillable = [
        'requested_name', 'justification', 'institutional_reason', 'functions', 'scope', 'observations',
        'requested_permissions', 'analysis_result', 'status', 'requested_by', 'director_id',
        'reviewed_by', 'reviewed_at', 'review_note', 'document_path', 'document_hash',
        'document_original_name', 'document_mime', 'document_size', 'document_analysis', 'created_role_id',
    ];

    protected function casts(): array
    {
        return ['requested_permissions' => 'array', 'analysis_result' => 'array', 'document_analysis' => 'array', 'reviewed_at' => 'datetime'];
    }
}
