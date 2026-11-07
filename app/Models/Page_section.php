<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page_section extends Model
{
    protected $table = 'page_sections';

    protected $fillable = [
        'name',
        'page_id',
        'template_id',
        'position'
    ];

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }
    public function sectionField()
    {
        return $this->hasMany(Section_field_value::class, 'page_section_id');
    }
}
