<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** ERD entity: ChurchCategories */
class ChurchCategory extends Model
{
    protected $table = 'church_categories';

    public $timestamps = false;

    protected $fillable = ['name', 'description', 'created_at', 'updated_at'];

    public function churches(): HasMany
    {
        return $this->hasMany(Church::class, 'category_id');
    }
}
