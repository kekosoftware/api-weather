<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">


| Laravel Version | PHP Version | 
| -------- | -------- | 
| 8.83.23     | 7.4.22| 


</p>

## About API
With this api you can check the weather of a city
Cities may be separated by commas.
Usage example: http://127.0.0.1:8000/api/v1/current?query=New%20York,formosa

## How to
* git clone https://github.com/kekosoftware/api-weather.git
* composer install
* configure .env file
* Fill in the variable WEATHERSTACK_KEY with your key into the .env file
* php artisan key:generate
* php artisan migrate
* php artisan serve
* http://127.0.0.1:8000


## License
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
This API is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
