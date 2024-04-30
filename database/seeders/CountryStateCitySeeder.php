<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;

class CountryStateCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (($CountryFile = fopen('public/csv/countries.csv', "r")) !== FALSE) {
            $newCountry = array();
            while (($dataInputRecord = fgetcsv($CountryFile, null, ",")) !== FALSE) {
                $newCountry[] = array('id' => $dataInputRecord[0], 'name' => $dataInputRecord[1], 'status' => 1);
                if(count($newCountry) == 2000) {
                    Country::upsert($newCountry, ['id'], ['name']);
                    echo count($newCountry) . " Countries upserted.\n";
                    $newCountry = array();
                }
            }
            fclose($CountryFile);
            
            if(count($newCountry) > 0) {
                Country::upsert($newCountry, ['id'], ['name']);
            }
            unset($newCountry);
        }

        if (($StateFile = fopen('public/csv/states.csv', "r")) !== FALSE) {
            $newState = array();
            while (($dataInputRecord = fgetcsv($StateFile, null, ",")) !== FALSE) {
                $newState[] = array('id' => $dataInputRecord[0], 'name' => $dataInputRecord[1], 'country_id' => $dataInputRecord[2], 'status' => 1);
                if(count($newState) == 2000) {
                    State::upsert($newState, ['id'], ['name', 'country_id']);
                    echo count($newState) . " States upserted.\n";
                    $newState = array();
                }
            }
            fclose($StateFile);
            
            if(count($newState) > 0) {
                State::upsert($newState, ['id'], ['name', 'country_id']);
            }
            unset($newState);
        }

        if (($CityFile = fopen('public/csv/cities.csv', "r")) !== FALSE) {
            $newCity = array();
            while (($dataInputRecord = fgetcsv($CityFile, null, ",")) !== FALSE) {
                $newCity[] = array('id' => $dataInputRecord[0], 'name' => $dataInputRecord[1], 'state_id' => $dataInputRecord[2], 'unique_id' => $dataInputRecord[3], 'status' => 1);
                if(count($newCity) == 2000) {
                    DB::table('cities')->upsert($newCity, ['id'], ['name', 'state_id']);
                    echo count($newCity) . " Citites upserted.\n";
                    $newCity = array();
                }
            }
            fclose($CityFile);
            
            if(count($newCity) > 0) {
                DB::table('cities')->upsert($newCity, ['id'], ['name', 'state_id']);
            }
            unset($newCity);
        }
    }
}
