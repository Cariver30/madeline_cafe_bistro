<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extra extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group_name',
        'group_required',
        'max_select',
        'kind',
        'price',
        'description',
        'view_scope',
        'active',
        'clover_id',
        'clover_group_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'active' => 'boolean',
        'group_required' => 'boolean',
        'max_select' => 'integer',
    ];

    public const KINDS = [
        'modifier',
        'extra',
    ];

    public const VIEW_SCOPES = [
        'global',
        'menu',
        'coffee',
        'cocktails',
        'cantina',
    ];

    public function scopeForView($query, string $view): void
    {
        $query->where(function ($q) use ($view) {
            $q->where('view_scope', $view)
                ->orWhere('view_scope', 'global');
        });
    }

    public function assignments()
    {
        return $this->hasMany(ExtraAssignment::class);
    }
}
