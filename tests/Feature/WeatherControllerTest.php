<?php


namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeatherControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }


    public function test_route()
    {
        $response = $this->get('/greeting');

        $response->assertStatus(200);
    }

    public function test_current_route()
    {

        $response = $this->get('/api/v1/current');

        $response->assertStatus(200);
        // $response->assertJson([
        //     'success' => true,
        // ]);
    }

}
