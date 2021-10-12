<?php

return [
    'banner'      => [
        'title'    => 'Баннер',
        'model'    => App\Models\Components\Banner::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
    'block'       => [
        'title'    => 'Блок',
        'model'    => App\Models\Components\Block::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
    'advantage'   => [
        'title'    => 'Преимущество',
        'model'    => App\Models\Components\Advantage::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
    'slider'      => [
        'title'    => 'Слайдер',
        'model'    => App\Models\Components\Slider::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
    'gallery'     => [
        'title'    => 'Галерея',
        'model'    => App\Models\Components\Gallery::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
    'variable'    => [
        'title'    => 'Переменная',
        'model'    => App\Models\Components\Variable::class,
        'primary'  => 'key',
        'field'    => 'name',
        'multiple' => FALSE
    ],
    'menu'        => [
        'title'    => 'Меню',
        'model'    => App\Models\Components\Menu::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
    'faq'         => [
        'title'    => 'Вопрос/Ответ',
        'model'    => App\Models\Components\Faq::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
    'form'        => [
        'title'    => 'Форма',
        'model'    => App\Models\Form\Forms::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
    'form_button' => [
        'title'    => 'Копка вызова формы',
        'model'    => App\Models\Form\Forms::class,
        'primary'  => 'id',
        'field'    => 'title',
        'multiple' => FALSE
    ],
];
