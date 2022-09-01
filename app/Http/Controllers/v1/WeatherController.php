<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use DB;

class WeatherController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     * * Get info weather from WeatherStack API.
     */
    public function index(Request $request)
    {
        try {
            $ask = $request->query('query');
            $expiration = time() + 3600;
            $cities = explode(',', $ask);
            $response = [];
            $allCities = [];

            DB::beginTransaction();

            foreach ($cities as $city) {
                /**
                 * Check if city was requested
                 */
                if  (in_array($city, $allCities)) {
                    continue;
                }  else {
                    $allCities[] = $city;
                }

                /**
                 * Check if city exist inside database
                 */
                if(!$this->checkExist($city)) {
                    /**
                     * * If the city doesn't exist
                     */

                    try
                    {
                        /**
                         * Check the enpoint
                         */
                        $data = $this->getWeather($city);

                        $record=[];

                        $record['cityname']    = strtolower($city);
                        $record['expiration']  = $expiration;
                        $record['description'] = json_encode($data['json']);
                        $record['created_at']  = DB::raw('CURRENT_TIMESTAMP');

                        DB::table('cities')->insert($record);

                        $response[] =  [
                            'success' => true,
                            'response' => $data['json']
                        ];

                    }
                    catch (\Throwable $e)
                    {
                        DB::rollback();
                        $response[] = [
                            'success' => false,
                            'response' => "An error occurred while inserting the record: ".$city.'-'.$th
                        ];
                    }

                } else {
                    /**
                     * * If the city exist
                     */
                    try
                    {
                        /**
                         * * Check the time
                         */
                        $checkTime = DB::table('cities')
                            ->select('cityname', 'expiration', 'description')
                            ->where('cityname', $city)
                            ->where('expiration', '>', time())
                            ->get();

                        if(!intval(count($checkTime))) {

                            $data = $this->getWeather($city);

                            DB::table('cities')
                                ->where('cityname', $city)
                                ->update(
                                    [
                                        'description' => json_encode($data['json']),
                                        'expiration' => time() + 3600,
                                        'updated_at' => DB::raw('CURRENT_TIMESTAMP'),
                                    ]
                                );

                            $response[] = [
                                'success' => true,
                                'response' => $data['json']
                            ];
                        } else {
dd($city, json_decode($checkTime[0]->description));
                            $response[] = [
                                'success' => true,
                                'response' => json_decode($checkTime[0]->description),
                            ];
                        }
                    }
                    catch (\Throwable $e)
                    {
                        DB::rollback();
                        $response[] = [
                            'success' => false,
                            'response' => "An error occurred while inserting the record: ".$city.'-'.$th
                        ];
                    }
                }

            }

            DB::commit();
        }
        catch (\Throwable $th) {
            $response[] = [
                'success' => false,
                'expiration' => $expiration,
                'city' => $cities,
                'response' => 'An error occurred while recovery the var. '.$th
            ];
        }

        return $response;
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
                ->where('cityname', strtolower($city))
                ->get();

            return ((intval(count($checkCity)) === 0) ? false : true);

        } catch (\Throwable $th) {
            return "An error occurred while querying the database".$th;
        }
    }
}
