<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_the_appointment_page_post_method_working_properly()
    {
        $response = $this->post(
            '/api/appointment',[
                'contact_id' => 1,
                'appointment_address' => "OX49 5NU",
                'appointment_date' =>"2021-05-04 11:05:20",
                'leave_time' =>null,
                'return_time'=>null,

            ]
        );

        $response->assertStatus(200);
         $response->assertSuccessful();
     }
     public function test_the_appointment_page_delete_method_working_properly(){
        $response = $this->delete(
            'api/appointment/1'
        );
        $response->assertStatus(200);
        $response->assertSuccessful();
     }
     public function test_the_appointment_page_getbyId_method_working_properly(){
         $response = $this->get(
             'api/appointment/1'
         );
         $response->assertStatus(200);
         $response->assertSuccessful();
     }
}
