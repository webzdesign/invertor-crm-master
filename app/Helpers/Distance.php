<?php

namespace App\Helpers;

class Distance {

    public static function measure($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $unit = 'miles', $earthRadius = 6371000)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        $distance = $angle * $earthRadius;

        switch ($unit) {
            case 'km':
                $distance /= 1000;
                break;
            case 'miles':
                $distance *= 0.000621371;
                break;
            case 'nautical_miles':
                $distance *= 0.000539957;
                break;
            case 'meters':
                break;
            default:
                throw new \InvalidArgumentException('Invalid unit provided. Must be one of: km, miles, nautical_miles, meters');
        }

        return $distance;
    }

    public static function haversineRaw($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    public static function applyHaversine($query, $latitudeFrom, $longitudeFrom, $latitudeColumn = 'latitude', $longitudeColumn = 'longitude', $unit = 'miles')
    {
        $distanceExpression = static::haversineRaw(
            $latitudeFrom,
            $longitudeFrom,
            $query->getModel()->{$latitudeColumn},
            $query->getModel()->{$longitudeColumn}
        );

        switch ($unit) {
            case 'km':
                $distanceExpression /= 1000;
                break;
            case 'miles':
                $distanceExpression *= 0.000621371;
                break;
            case 'nautical_miles':
                $distanceExpression *= 0.000539957;
                break;
            case 'meters':
                break;
            default:
                throw new \InvalidArgumentException('Invalid unit provided. Must be one of: km, miles, nautical_miles, meters');
        }

        // Return the modified query
        return $query->selectRaw("*, $distanceExpression AS distance");
    }

}