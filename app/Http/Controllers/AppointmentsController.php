<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Contacts;
use App\Models\Map;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Scalar\String_;

class AppointmentsController extends Controller
{

    public function __construct()
    {
        //Burda middleware kullaniyoruz
        //Kendi olusturdugumuz JwtMiddleware ekliyoruz
        //Bu sekilde Token olmayan hic kimse islem yapamayacak
        //JwtMiddleWare bakabilirsiniz
        $this->middleware(['jwt.verify']);

    }
    public function index(){
        //Bu kod satiri ile veritabanindan butun Appointments cekilebilir
        return Appointments::all();
    }

    public function update(Request $request){
        //Update aslinda Delete edip sonra post etmekdir
        //Burda ilk once Postmanden gelen request ile kaydi siliyoruz
        //Ondan Sonra Yazdigimiz Post Methodunu calistiriyoruz ve parametre olaraka
        //postmanden gelen $request veriyoruz
        $checkIfAppointmentExists = Appointments::find($request->id);
        if($checkIfAppointmentExists === null){
            return response("There is not a appointment with this id",500);
        }
       $update =  Appointments::find($request->id);

        $appointment_date = $request->get('appointment_date');
        $datetime = Carbon::createFromFormat('Y-m-d H:i:s',$request->get('appointment_date'),'Europe/London'
        );
        //Carbon Laravelin icinde olan bi paket,Burda tarih formatini belirliyoruz tarih formatini belirledikten sonra ise
        //Postmanden gelen requesti atiyoruz carbona
        //Sonda Europe/London yazma sebebim carbon benden son olarak timezone istiyor bende London yazdim
        $client = new \GuzzleHttp\Client();
        //Post Kodu Servisine istek atmak icin GuzzleHttp degilen disardan bi paket ekledik projemize
        //Address diye bi nesne olusturduk ve bunu appointment_address degerini verdik
        $address = $request->get('appointment_address');
        //Burda Eklemis oldugumuz paketin get methodunu kullanarak posta servisine istek atiyoruz
        //sonda yazdigimiz kod emlakcinin adresi
        $response = $client->get('https://api.postcodes.io/postcodes/cm27pj');
        //Burda ise musterinin adresini yaziyoruz
        $responseOfAdress = $client->get("https://api.postcodes.io/postcodes/$address");
        //Eger musterinin posta kodu adresi gecerli degilse invalid_postalcode seklinde hata firlatiyoruz
        if($responseOfAdress->getStatusCode()==500){
            return response()->json(['error' => 'invalid_postalcode'], 500);
        }
        //Posta Kodu servisine istegimizi attik simdi ise attigimiz istekten veri cekmeliyiz
        //Bunu yapma sebebimiz musteri ile emlakcinin arasindaki mesafeyi olcmek
        //Burda bize gereken latitudelar ve logitudelar
        //Json Decode kullanarak attigimiz istegin body kismina yani istegimizin cevap bolumune ulasiyoruz
        //Ayni zamanda bunu Emlakci icinde yapiyoruz
        $data = json_decode($response->getBody());
        $data2 = json_decode($responseOfAdress->getBody());
        //Burda Result icindeki latitude deme sebebimiz latitude ve logitude result icinde
        //Posta kodu servisini calistirirsaniz gorursunuz ki latitude ve logitude result icinde
        $lat1 =  $data->result->latitude;
        $lon1 =  $data->result->longitude;
        $lat2 =  $data2->result->latitude;
        $lon2 = $data2->result->longitude;
        //Burda Mesafeyi olcmek icin kullandigimiz algoritmayi devre sokuyoruz
        //Kisaca burda hem emlakcinin hem musterinin latitude ve longtitudelarini kisaca adreslerini yaziyoruz
        //Sonda Yazdigimiz K harfi mesafeyi Kilometre seklinde gosterecek
        $distancetime =  $this->distance($lat1,$lon1,$lat2,$lon2,"K");
        //burda 60 a bolme sebebim araba ile gidecegi icin emlakci ortalama 60KM hizla ilerleyecek
        $time = $distancetime/60;
        //Mesafeyi 60 km yani arabanin hizi ile bolecegimiz icin bize 1.5,1.6,1.7 saat yolculugu gelebilir
        //Carbonda saatleri ayri dakikalari ayri sekilde tanimladigimiz icin alta yazdigim bi algoritma var

        $whole = floor($time);
        //$fraction kodu saaten sonra yani virgulden sonraki zamani hesapliyor 1,5saat =90Dakika 1,6saat = 96 Dakika,1,7saat = 102Dakika gibi
        $fraction = $time - $whole;
        $fraction = $fraction*10;
        //Simdi burda evden cikma saatini buluyoruz ilk once yine Carbon kullanarak tarih yapisini ve bulusma tarihini iceren degiskenimizi yaziyoruz
        $leave_time = Carbon::createFromFormat('Y-m-d H:i:s',$appointment_date);
        //Evden cikma saatini hesaplamak icin yolu cikmaliyiz
        //Yol 1,5 saat surebilir dedigim gibi ilk once saatleri degistiriyoruz sonra ise dakikalari
        //Saati yaptik
        $leave_time->hour=$datetime->hour-$time;
        //Burda Dakikalari tanimliyoruz
        //Burda bi algoritma yazdim bu algoritmaya gore her bi zaman dilimi 6dakika buyuzden 6 ile carpicagiz
        $leave_time->minute=$datetime->minute-$fraction*6;
        //Digerleri gibi buradada return_time icin Carbon kullanacagiz
        $return_time = Carbon::createFromFormat('Y-m-d H:i:s',$appointment_date);
        //leave_time da cikma islemi yapiyorduk ama burda ekleme
        //bunun sebebi toplanti bitikten sonra(toplanti 1 saat) + yol oldugu icindir
        $return_time->hour = $datetime->hour+1+$time;
        //Yine ayni sekilde dakikalari tanimliyoruz
        $return_time->minute=$datetime->minute+$fraction*6;
        //Modelimizi olusturuyoruz ve veritabanina atiyoruz

            $update->contact_id = $request->get('contact_id');
            $update->appointment_address = $request->get('appointment_address');
            $update->appointment_date = $request->get('appointment_date');
            $update->leave_time =  $leave_time;
            $update->return_time = $return_time;






    }

    public function getById($id){
        //Burda kontrol ediyoruz id var mi yok mu diye
        $checkIfAppointmentExists = Appointments::find($id);
        if($checkIfAppointmentExists === null){
            return response("There is not a appointment with this id",500);
        }
        //Burda Postmanden gelen Id gore bakiyoruz veritabanina ve o id ile eslesen Appointment getiriyoruz
        return Appointments::find($id);
    }

    public function deleteById($id){

        //Burda Postmanden gelen Id gore bakiyoruz veritabanina ve o id ile eslesen Appointment siliyoruz
        $checkIfAppointmentExists = Appointments::find($id);
        if($checkIfAppointmentExists === null){
            return response("There is not an appointment with this id",500);

        }
        Appointments::destroy($id);
        return response("Successfully,Your appointment deleted");

    }
    public function store(Request $request)
    {
        $appointment_date = $request->get('appointment_date');

        $datetime = Carbon::createFromFormat('Y-m-d H:i:s',$request->get('appointment_date'),'Europe/London'
        );
        //Carbon Laravelin icinde olan bi paket,Burda tarih formatini belirliyoruz tarih formatini belirledikten sonra ise
        //Postmanden gelen requesti atiyoruz carbona
        //Sonda Europe/London yazma sebebim carbon benden son olarak timezone istiyor bende London yazdim




        $client = new \GuzzleHttp\Client();
        //Post Kodu Servisine istek atmak icin GuzzleHttp degilen disardan bi paket ekledik projemize
        //Address diye bi nesne olusturduk ve bunu appointment_address degerini verdik
        $address = $request->get('appointment_address');
        //Burda Eklemis oldugumuz paketin get methodunu kullanarak posta servisine istek atiyoruz
        //sonda yazdigimiz kod emlakcinin adresi
        $response = $client->get('https://api.postcodes.io/postcodes/cm27pj');
        //Burda ise musterinin adresini yaziyoruz
        $responseOfAdress = $client->get("https://api.postcodes.io/postcodes/$address");
        //Eger musterinin posta kodu adresi gecerli degilse invalid_postalcode seklinde hata firlatiyoruz
        if($responseOfAdress->getStatusCode()==500){
            return response()->json(['error' => 'invalid_postalcode'], 500);
        }
        //Posta Kodu servisine istegimizi attik simdi ise attigimiz istekten veri cekmeliyiz
        //Bunu yapma sebebimiz musteri ile emlakcinin arasindaki mesafeyi olcmek
        //Burda bize gereken latitudelar ve logitudelar
        //Json Decode kullanarak attigimiz istegin body kismina yani istegimizin cevap bolumune ulasiyoruz
        //Ayni zamanda bunu Emlakci icinde yapiyoruz
        $data = json_decode($response->getBody());
        $data2 = json_decode($responseOfAdress->getBody());
        //Burda Result icindeki latitude deme sebebimiz latitude ve logitude result icinde
        //Posta kodu servisini calistirirsaniz gorursunuz ki latitude ve logitude result icinde
        $lat1 =  $data->result->latitude;
        $lon1 =  $data->result->longitude;
        $lat2 =  $data2->result->latitude;
        $lon2 = $data2->result->longitude;
        //Burda Mesafeyi olcmek icin kullandigimiz algoritmayi devre sokuyoruz
        //Kisaca burda hem emlakcinin hem musterinin latitude ve longtitudelarini kisaca adreslerini yaziyoruz
        //Sonda Yazdigimiz K harfi mesafeyi Kilometre seklinde gosterecek
        $distancetime =  $this->distance($lat1,$lon1,$lat2,$lon2,"K");
        //burda 60 a bolme sebebim araba ile gidecegi icin emlakci ortalama 60KM hizla ilerleyecek
        $time = $distancetime/60;
        //Mesafeyi 60 km yani arabanin hizi ile bolecegimiz icin bize 1.5,1.6,1.7 saat yolculugu gelebilir
        //Carbonda saatleri ayri dakikalari ayri sekilde tanimladigimiz icin alta yazdigim bi algoritma var

        $whole = floor($time);
        //$fraction kodu saaten sonra yani virgulden sonraki zamani hesapliyor 1,5saat =90Dakika 1,6saat = 96 Dakika,1,7saat = 102Dakika gibi
        $fraction = $time - $whole;
        $fraction = $fraction*10;
        //Simdi burda evden cikma saatini buluyoruz ilk once yine Carbon kullanarak tarih yapisini ve bulusma tarihini iceren degiskenimizi yaziyoruz
        $leave_time = Carbon::createFromFormat('Y-m-d H:i:s',$appointment_date);
        //Evden cikma saatini hesaplamak icin yolu cikmaliyiz
        //Yol 1,5 saat surebilir dedigim gibi ilk once saatleri degistiriyoruz sonra ise dakikalari
        //Saati yaptik
        $leave_time->hour=$datetime->hour-$time;
        //Burda Dakikalari tanimliyoruz
        //Burda bi algoritma yazdim bu algoritmaya gore her bi zaman dilimi 6dakika buyuzden 6 ile carpicagiz
        $leave_time->minute=$datetime->minute-$fraction*6;
        //Digerleri gibi buradada return_time icin Carbon kullanacagiz
        $return_time = Carbon::createFromFormat('Y-m-d H:i:s',$appointment_date);
        //leave_time da cikma islemi yapiyorduk ama burda ekleme
        //bunun sebebi toplanti bitikten sonra(toplanti 1 saat) + yol oldugu icindir
        $return_time->hour = $datetime->hour+1+$time;
        //Yine ayni sekilde dakikalari tanimliyoruz
        $return_time->minute=$datetime->minute+$fraction*6;







        //Modelimizi olusturuyoruz ve veritabanina atiyoruz
        $appointment = Appointments::create([
            'contact_id' => $request->get('contact_id'),
            'appointment_address' => $request->get('appointment_address'),
            'appointment_date' => $request->get('appointment_date'),
            'leave_time' =>  $leave_time,
            'return_time'=> $return_time,



        ]);


        //Son olarakda cevabimizi yazdiriyoruz
        return response("Successfully,Your appointment created");



    }





}
