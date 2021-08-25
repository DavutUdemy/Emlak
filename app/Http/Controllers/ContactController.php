<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Contacts;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function __construct()
    {
        //Burda middleware kullaniyoruz
        //Kendi olusturdugumuz JwtMiddleware ekliyoruz
        //Bu sekilde Token olmayan hic kimse islem yapamayacak
        //JwtMiddleWare bakabilirsiniz
        $this->middleware(['jwt.verify']);
    }


    public function index()
    {
        //Bu kod satiri ile veritabanindan butun Appointments cekilebilir
        return Contacts::all();
    }

    public function update(Request $request)
    {
        //Update aslinda Delete edip sonra post etmekdir
        //Burda ilk once Postmanden gelen request ile kaydi siliyoruz
        //Ondan Sonra Yazdigimiz Post Methodunu calistiriyoruz ve parametre olaraka
        //postmanden gelen $request veriyoruz
        $checkIfContactExists = Contacts::find($request->id);
        if ($checkIfContactExists === null) {
            return response("There is not a contact with this id", 500);
        }
          $update =  Appointments::find($request->id);
        $update->user_id = $request->get('user_id');
        $update->firstName = $request->get('firstName');
        $update->lastName = $request->get('lastName');
        $update->email_Address = $request->get('email_Address');
        $update->return_time = $request->get('phone_Number');

        return response("Successfully,Your appointment updated");


    }

    public function getById($id)
    {
        //Burda Postmanden gelen Id gore bakiyoruz veritabanina ve o id ile eslesen Appointment getiriyoruz
        return Contacts::find($id);
    }

    public function deleteById($id)
    {
        //Burda Postmanden gelen Id gore bakiyoruz veritabanina ve o id ile eslesen Appointment siliyoruz
        $checkIfContactExists = Contacts::find($id);
        if ($checkIfContactExists === null) {
            return response("There is not a contact with this id", 500);

        }
        Contacts::destroy($id);

        return response("Successfully,Your contact deleted");

    }

    //Bu bizim post methodumuz yani veritabanina data atigimiz kisim
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //Burda Validator ile Validation kuralarimizi yazdik
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email_Address' => 'required|string|max:255'
        ]);


        //Eger validasyon kuralarina uymuyorsak hata yiyoruz
        if ($validator->fails()) {
            //Hatamizin icerigi ve statusunu belirliyoruz
            return response()->json($validator->errors()->toJson(), 400);
        }
        //Burda da artik validasyondan gecmis datalarimizi veirtabanina kaydediyoruz
        $contact = Contacts::create([
            'user_id' => $request->get('user_id'),
            'firstName' => $request->get('firstName'),
            'lastName' => $request->get('lastName'),
            'email_Address' => $request->get('email_Address'),
            'phone_Number' => $request->get('phone_Number'),
        ]);

        //Son olarak basarili mesajini veriyoruz
        return response("Succesufully,Your contact information saved");


    }

     
}
