<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    protected $primaryKey = 'qualification_id';
    protected $fillable = [
        'qualification_type',
        'institute_name',
        'year_of_graduation',
        'cgpa',
        'cgpa_out_of',
        'language_of_study',
        'specialization',
        'research_title',
        'document_file',
        'applicant_id'
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id', 'applicant_id');
    }
}
