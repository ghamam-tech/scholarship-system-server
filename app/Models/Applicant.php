<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $primaryKey = 'applicant_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id','ar_name','en_name','nationality','gender','place_of_birth','phone',
        'passport_number','date_of_birth','parent_contact_name','parent_contact_phone',
        'residence_country','passport_copy_url','volunteering_certificate_url','language',
        'is_studied_in_saudi'
    ];

    public function user(){ return $this->belongsTo(\App\Models\User::class, 'user_id', 'user_id'); }
}