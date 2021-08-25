<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'firstName',
        'lastName',
        'email_Address',
        'phone_Number'



    ];

    public function ownedBy(User $user){
        return $user->id === $this->user_id;
    }
    //Burda contact user agit oldugunu soyluyoruz
    public function user(){
        return $this->belongsTo(User::class);
    }
    //Contact sayesinde appointment olusturulmali diyoruz
    public function appointments(){
        return $this->hasMany(Appointments::class);
    }
    public $timestamps = false;


}
