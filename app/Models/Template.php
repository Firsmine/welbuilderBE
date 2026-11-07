<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'templates';

    protected $fillable = [
        'name',
        'slug'
    ];

    public function pageSection()
    {
        return $this->hasMany(Page_section::class, 'page_id');
    }
    public function templateField()
    {
        return $this->hasMany(Template_field::class, 'template_id');
    }
}
