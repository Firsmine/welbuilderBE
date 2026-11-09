<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template_field extends Model
{
    protected $table = 'template_fields';

    protected $fillable = [
        'name',
        'type',
        'template_id',
    ];
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
    public function sectionField()
    {
        return $this->hasMany(Section_field_value::class, 'template_field_id');
    }
}
