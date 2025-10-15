<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    protected $primaryKey = 'qualification_id';
    protected $fillable = [
        'qualification_type','institute_name','year_of_graduation','cgpa','cgpa_out_of',
        'language_of_study','tahseeli_percentage','qudrat_percentage','specialization',
        'research_title','document_file','application_id'
    ];

    public function application()
    {
        return $this->belongsTo(ApplicantApplication::class, 'application_id', 'application_id');
    }
}
