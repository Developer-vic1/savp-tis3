<?php

declare(strict_types=1);

namespace App\Models\Oficial\Sistema;

use Illuminate\Database\Eloquent\Model;

/** Tabla técnica existente: respeta sus claves y generadores nativos. */
class JobBatch extends Model
{
    protected $table = 'job_batches';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'total_jobs',
        'pending_jobs',
        'failed_jobs',
        'failed_job_ids',
        'options',
        'cancelled_at',
        'created_at',
        'finished_at',
    ];

    protected $casts = [
        'total_jobs' => 'integer',
        'pending_jobs' => 'integer',
        'failed_jobs' => 'integer',
        'cancelled_at' => 'integer',
        'created_at' => 'integer',
        'finished_at' => 'integer',
    ];
}
