<?php

return [
    // Captcha настройки по-умолчанию
    'default' => [
        'name' => 'captcha',
        'width' => 150, // Ширина картинки
        'height' => 50, // Высота картинки
        'numberLines' => 20, //Количество линий
        'lengthMin' => 3, // Минимальное число символов
        'lengthMax' => 4, // Максимальное число символов
        'letters' => '23456789abcdeghkmnpqsuvxyz', // Используемые символы. Не ставить похожие! (o=0, 1=l, i=j, t=f)
    ]
];