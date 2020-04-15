<?php require 'config.php'; ?>
<html>
    <head>
        <meta charset="UTF-8"/>
        <title>Умный сервис прогноза погоды</title>
        <script src="//st.iex.su/jquery.js"></script>
        <script src="//st.iex.su/underscore.js"></script>
        <script src="https://api-maps.yandex.ru/2.1/?apikey=<?php echo YANDEX_MAPS_API_KEY; ?>&load=package.full&lang=ru-RU"></script>
        <style>
            .b-current-weather-icon {
                width: 30px;
                height: 30px;
            }
        </style>
    </head>
    <body>
        <form>
            Ваш город: <input class="js-weather-city" type="text" name="city"/>
            <input class="js-weather-get" type="submit" name="get" value="Получить погоду"/>
        </form>
        <div class="b-answer js-answer-block">

        </div>
        <script>
            const getCityWeather = function () {
                return new Promise(function(resolve, reject) {
                    $.get('get_weather_for_city.php', {city: $('.js-weather-city').val()}, function (result) {
                        try {
                            var myGeocoder = ymaps.geocode("Moscow");

                            const dataFromServer = $.parseJSON(result);
                            if (dataFromServer.error === '') {
                                resolve(dataFromServer.data)
                            } else {
                                reject(dataFromServer.error);
                            }
                        } catch (e) {
                            reject(e.message);
                        }
                    }).fail(function() {
                        reject('Ошибка при обращении к серверу. Попробуйте позже.');
                    });
                });
            };

            const getTemplate = function (template) {
                return new Promise(function (resolve, reject) {
                    $.get(template+'.html', {v: new Date().getTime()}, function (html) {
                        resolve(html);
                    }).fail(function () {
                        reject('Не удалось загрузить шаблон '+template);
                    });
                });
            }

            const prec_strengths = {
                '0.25': 'слабый',
                '0.5': '',
                '0.75': 'сильный',
                '1': 'очень сильный'
            }

            const conditions_descriptions = {
                'clear': 'Ясно',
                'partly-cloudy': 'Малооблачно',
                'cloudy': 'Облачно с прояснениями',
                'overcast': 'Пасмурно',
                'partly-cloudy-and-light-rain': 'Небольшой дождь',
                'partly-cloudy-and-rain': 'Дождь',
                'overcast-and-rain': 'Сильный дождь',
                'overcast-thunderstorms-with-rain': 'Сильный дождь, гроза',
                'cloudy-and-light-rain': 'Небольшой дождь',
                'overcast-and-light-rain': 'Небольшой дождь',
                'cloudy-and-rain': 'Дождь',
                'overcast-and-wet-snow': 'Дождь со снегом',
                'partly-cloudy-and-light-snow': 'Небольшой снег',
                'partly-cloudy-and-snow': 'Снег',
                'overcast-and-snow': 'Снегопад',
                'cloudy-and-light-snow':'Небольшой снег',
                'overcast-and-light-snow': 'Небольшой снег',
                'cloudy-and-snow': 'Снег'
            };

            $(function () {
                let weatherTemplate;
                getTemplate('forecast_template').then(function (html) {
                    weatherTemplate = _.template(html);
                }).then(function () {
                    $('.js-weather-get').click(function (e) {
                        e.preventDefault();
                        const city = $('.js-weather-city').val() || '';
                        if (city !== '') {
                            getCityWeather().then(function (weather_data) {
                                $('.js-answer-block').html(weatherTemplate({'weather_data': weather_data, 'conditions': conditions_descriptions, 'prec_strengths': prec_strengths}));
                            }).catch(function (error) {
                                $('.js-answer-block').html(error);
                            });
                        } else {
                            alert('Введите город');
                        }
                    });
                    ymaps.ready(function () {
                        ymaps.geolocation.get().then(function (result) {
                            const geoipCity = result.geoObjects.get(0).getLocalities()[0];
                            if (confirm('Ваш город '+geoipCity+'?')) {
                                $('.js-weather-city').val(geoipCity);
                                $('.js-weather-get').click();
                            }
                        });
                    });
                }).catch(function () {
                    alert('Не удалось загрузить приложение');
                })
            });
        </script>
    </body>
</html>