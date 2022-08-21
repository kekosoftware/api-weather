<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     * * Get info weather from WeatherStack API.
     */
    public function index(Request $request)
    {
        try {
            $city = $request->query('query');
            $expiration = time() + 3600;

            if(!$this->checkExist($city)) {
                /**
                 * * If the city doesn't exist
                 */
                DB::beginTransaction();
                try
                {
                    $data = $this->getWeather($city);

                    $record=[];

                    $record['cityname']    = $city;
                    $record['expiration']  = $expiration;
                    $record['description'] = json_encode($data['json']);
                    $record['created_at']  = DB::raw('CURRENT_TIMESTAMP');


                    DB::table('cities')->insert($record);

                    DB::commit();

                    return [
                        'success' => true,
                        'response' => $data['json']
                    ];
                }
                catch (Throwable $e)
                {
                    DB::rollback();
                    return [
                        'success' => false,
                        'response' => "An error occurred while inserting the record: ".$city.'-'.$th
                    ];
                }
            } else {
                /**
                 * * If the city exist
                 */

                DB::beginTransaction();
                try
                {
                    /**
                     * * Check the time
                     */
                    $checkTime = DB::table('cities')
                        ->select('cityname', 'expiration', 'description')
                        ->where('expiration', '>', time())
                        ->get();

                    if(!intval(count($checkTime))) {

                        $data = $this->getWeather($city);

                        $updateResponse = DB::table('cities')
                            ->where('cityname', $city)
                            ->update(
                                [
                                    'description' => json_encode($data['json']),
                                    'expiration' => time() + 3600,
                                    'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
                                ]
                            );

                        DB::commit();

                        return [
                            'success' => true,
                            'response' => $data['json']
                        ];
                    } else {

                        return [
                            'success' => true,
                            'response' => json_decode($checkTime[0]->description),
                        ];
                    }
                }
                catch (Throwable $e)
                {
                    DB::rollback();
                    return [
                        'success' => false,
                        'response' => "An error occurred while inserting the record: ".$city.'-'.$th
                    ];
                }
            }
        }
        catch (\Throwable $th) {
            return [
                'success' => false,
                'expiration' => $expiration,
                'response' => 'An error occurred while recovery the var. '.$th
            ];
        }
    }

    private function getWeather($city)
    {
        try {
            /**
             * @string URL to Check API
             */
            $uri  = 'http://api.weatherstack.com/current';
            $uri .= '?access_key='.env('WEATHERSTACK_KEY');
            $uri .= '&query='.$city;

            $clientWeatherStack = new Client();
            $responseAPI = $clientWeatherStack->request('GET', $uri);
            $jsonArray = json_decode($responseAPI->getBody()->getContents(), true);

            if(isset($jsonArray['success'])){
                return [
                        'success' => false,
                        'json' => $jsonArray['error']
                    ];
            } else {
                return [
                    'success' => true,
                    'json' => $jsonArray
                ];
            }
        }
        catch (\Throwable $th) {
            return "An error occurred while querying the API".$th;
        }
    }

    private function checkExist($city)
    {
        try {
            $checkCity = DB::table('cities')
                ->select('cityname')
                ->where('cityname', $city)
                ->get();

            return ((intval(count($checkCity)) == 0) ? false : true);


        } catch (\Throwable $th) {
            return "An error occurred while querying the database".$th;
        }
    }
}
