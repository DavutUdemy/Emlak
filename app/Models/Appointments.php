<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    use HasFactory;
    //Fillable da Postman tarafinda kullanici neler doldurmali onu belirlegen bi yapidir
    protected $fillable = [
        'contact_id',
        'appointment_address',
        'appointment_date',
        'leave_time',
        'return_time'


    ];

    //Appointment aslinda contact agitir diyoruz
    public function contact(){
        return $this->belongsTo(Contacts::class);
    }

    public $timestamps = false;

}
