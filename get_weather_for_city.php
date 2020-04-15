<?php
require 'config.php';
require 'functions.php';

$data = '';
$error = '';
if (isset($_GET['city']) && !empty($_GET['city'])) {
    $coords = getCityCoords($_GET['city'], YANDEX_MAPS_API_KEY);
    if ($coords) {
        $coords = explode(' ', $coords);
        $weather = getYandexWeatherByCoords($coords[1], $coords[0], YANDEX_WEATHER_API_KEY);
        if ($weather !== false) {
            $data = array_merge($weather['fact'], array('day_forecast'=>$weather['forecasts'][0]['parts']['day_short']));
        } else {
            $error = 'Не удалось получить прогноз погоды';
        }
    } else {
        $error = 'Не удалось получить координаты города';
    }
} else {
    $error = 'Не передан город';
}
sendAnswer($data, $error);
?>