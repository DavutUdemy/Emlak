<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{

    public function test_the_contact_page_post_method_working_properly()
    {
        $response = $this->post(
            '/api/contact',[
                'user_id' => 2,
                'firstName' =>"First Name",
                'lastName' => "Last Name",
                'email_Address' =>"Email Address",
                'phone_Number' =>"Phone Number",
            ]
        );

        $response->assertStatus(200);
        $response->assertSuccessful();
    }
    public function test_the_contact_page_delete_method_working_properly(){
        $response = $this->delete(
            'api/contact/1'
        );
        $response->assertStatus(200);
        $response->assertSuccessful();
    }
    public function test_the_contact_page_getbyId_method_working_properly(){
        $response = $this->get(
            'api/appointment/1'
        );
        $response->assertStatus(200);
        $response->assertSuccessful();
    }


}
