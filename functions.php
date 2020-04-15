<?php
function getYandexWeatherByCoords($lat, $lon, $key) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL=>'https://api.weather.yandex.ru/v1/forecast?lat='.$lat.'&lon='.$lon.'&lang=ru_RU&limit=1&hours=false&extra=true',
        CURLOPT_HTTPHEADER=>['X-Yandex-API-Key: '.$key],
        CURLOPT_RETURNTRANSFER=>true,
    ]);
    $answer = curl_exec($ch);
    $weather_data = false;
    try {
        $weather_data = json_decode($answer, true, 512);
    } catch (JsonException $e) {
        //$monolog->error($e->GetMessage());
    }
    return $weather_data;
}

function getCityCoords($city, $key) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL=>'https://geocode-maps.yandex.ru/1.x/?format=json&geocode='.urlencode($city).'&apikey='.$key,
        CURLOPT_RETURNTRANSFER=>true,
    ]);
    $answer = curl_exec($ch);

    try {
        $result = json_decode($answer, true, 512);
        if (!isset($result['response'])) {
            return false;
        }
        return $result['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['Point']['pos'];
    } catch (JsonException $e) {
        //$monolog->error($e->getMessage());
    }
    return false;
}

function sendAnswer($data, $error) {
    try {
        echo json_encode(['data'=>$data, 'error'=>$error]);
    } catch (JsonException $e) {
        header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
    }
}