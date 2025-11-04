<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class RoleType extends Model
{
    use HasFactory;

    protected $table = 'role_types';
    protected $primaryKey = 'role_type_id';
    public $timestamps = true; // your table has created_at/updated_at

    // Columns that actually exist now
    protected $fillable = ['name', 'description'];

    // Always expose a slug (computed from name). If a real slug column exists, we’ll use it.
    protected $appends = ['slug'];

    /* ===================== Relationships ===================== */

    public function workRoles()
    {
        return $this->hasMany(WorkRole::class, 'role_type_id', 'role_type_id');
    }

    // Keep only if workers table has role_type_id FK
    public function workers()
    {
        return $this->hasMany(Worker::class, 'role_type_id', 'role_type_id');
    }

    /* ===================== Accessors ===================== */

    /**
     * Slug accessor:
     * - If a real "slug" column exists and is filled, use it.
     * - Otherwise, derive from "name" (e.g., "Civil Defense" -> "civil_defense").
     */
    public function getSlugAttribute(): string
    {
        if (Schema::hasColumn($this->getTable(), 'slug')) {
            $val = $this->attributes['slug'] ?? null;
            if (!empty($val)) {
                return (string) $val;
            }
        }

        $base = (string) ($this->attributes['name'] ?? '');
        return Str::of($base)->lower()->replace([' ', '-'], '_')->__toString();
    }

    /* ===================== Scopes & Helpers ===================== */

    /** Case-insensitive match by name */
    public function scopeNamed($query, string $name)
    {
        return $query->whereRaw('LOWER(name) = ?', [mb_strtolower($name)]);
    }

    /**
     * Match by slug without requiring a slug column.
     * If a slug column exists, we try that too.
     */
    public function scopeSlug($query, string $slug)
    {
        $normalized = Str::of($slug)->lower()->replace([' ', '-'], '_')->__toString();

        return $query->where(function ($q) use ($normalized) {
            if (Schema::hasColumn($this->getTable(), 'slug')) {
                $q->orWhere('slug', $normalized);
            }
            $q->orWhereRaw(
                "REPLACE(REPLACE(LOWER(name), ' ', '_'), '-', '_') = ?",
                [$normalized]
            );
        });
    }

    /**
     * Helper without colliding with Eloquent\Model::is()
     * Usage: $roleType->isSlug('civil_defense')
     */
    public function isSlug(string $slug): bool
    {
        return $this->slug === Str::of($slug)->lower()->replace([' ', '-'], '_')->__toString();
    }
}
