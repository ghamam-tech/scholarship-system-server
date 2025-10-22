<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    use HasFactory;

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
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
