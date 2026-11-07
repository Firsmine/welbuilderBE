<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section_field_value extends Model
{
    protected $table = 'section_field_values';

    protected $fillable = [
        'page_section_id',
        'template_field_id',
        'value'
    ];

    public function pageSection()
    {
        return $this->belongsTo(Page_section::class, 'page_section_id');
    }
    public function templateField()
    {
        return $this->belongsTo(Template_field::class, 'template_field_id');
    }
}
