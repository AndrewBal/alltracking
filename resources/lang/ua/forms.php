<?php

return [
    'buttons'  => [
        'submit'           => 'Відправити',
        'auth'             => [
            'login'          => 'Авторизуватись',
            'clear'          => 'Очистити',
            'close'          => 'Закрити',
            'forgot'         => 'Відновити доступ',
            'registration'   => 'Зареєструватися',
            'password_reset' => 'Згенерувати посилання',
        ],
        'search'           => [
            'submit' => 'Шукати'
        ],
        'constructor_form' => [
            'submit' => 'Відправити',
            'open'   => 'Відкрити форму',
        ],
        'consultation' => [
            'open'   => 'Проконсультуватись',
        ]
    ],
    'titles'   => [
        'auth' => [
            'login'          => 'Вхід на сайт',
            'edit'           => 'Редагувати профіль',
            'registration'   => 'Реєстрація',
            'password_reset' => 'Відновити доступ',
        ]
    ],
    'fields'   => [
        'auth'                    => [
            'login'          => [
                'email'    => 'E-mail',
                'password' => 'Пароль',
                'remember' => 'Запам\'ятати мене',
            ],
            'registration'   => [
                'email'                 => 'E-mail',
                'password'              => 'Пароль',
                'password_confirmation' => 'Повторити пароль',
            ],
            'password_reset' => [
                'email' => 'E-mail',
            ]
        ],
        'search'                  => [
            'field' => 'Що Ви шукаєте?'
        ],
        'file_dropdown_start'     => 'Перетягніть файл в це поле або ',
        'file_dropdown_finish'    => 'виберіть файл',
        'file_allow_mime_type'    => 'В поле можна завантажити файли наступних форматів: <span class="uk-text-bold">:mime_type</span>',
        'file_upload_placeholder' => 'Вибрати файл',
        'reCaptcha'               => 'reCaptcha',
    ],
    'messages' => [
        'auth'             => [
            'register'       => [
                'confirm_registration' => 'На адресу, яку Ви вказали при реєстрації, було відправлено лист для підтвердження електронної пошти.'
            ],
            'verify_email'   => [
                'authenticate' => 'Для підтвердження пошти Ви повинні бути залягання.<br>Увійдіть на сайт під свої логіном і паролем і перейдіть за посиланням з листа.',
                'resent'       => 'На адресу, яку Ви вказали в налаштування облікового запису, було повторно надіслано листа для підтвердження електронної пошти.',
            ],
            'reset_password' => [
                'message' => 'На адресу, яку Ви вказали в налаштування облікового запису, було відправлено лист для скидання пароля.',
            ],
            'profile'        => [
                'message' => 'Дані профілю збережені.',
            ]
        ],
        'constructor_form' => [
            'error_open' => 'Сталася помилка завантаження форми.'
        ]
    ]
];
