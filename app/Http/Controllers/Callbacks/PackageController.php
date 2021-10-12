<?php

namespace App\Http\Controllers\Callbacks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function get_data(Request $request, $package = null)
    {
        $_data = null;
        if ($request->method() == 'GET' && !is_null($package)) {
            // запрос на апи по номеру
            $_data = $package;
        } elseif ($request->method() == 'POST') {
            $_packages = $request->input('items', []);
            if ($_packages) {
                // запрос по номерам
                $_data = [
                    'AEPU0003733721RU2' => [
                        'couriers' => [
                            'Cainiao',
                            'DPD Россия',
                            'Юнэкс'
                        ],
                        'destination_country' => 'Russia',
                        'events' => [
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-23T10:17:07Z',
                                'status' => 'Доставлено'
                            ],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-23T10:00:09Z',
                                'status' => 'Доставлено'
                            ],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-22T13:30:15Z',
                                'status' => 'Ожидает адресата в постамате/пункте выдачи'],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-22T10:00:08Z',
                                'status' => 'Отправка готова для выдачи получателю'],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-20T10:00:07Z',
                                'status' => 'Отправка следует по маршруту'],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-20T10:00:06Z',
                                'status' => 'Отправка готова к транспортировке по маршруту'],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-20T10:00:05Z',
                                'status' => 'Отправка принята у отправителя'],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-20T10:00:04Z',
                                'status' => 'Закреплен за курьером'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-20T04:54:30Z',
                                'status' => 'Покинуло сортировочный центр'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-20T03:33:50Z',
                                'status' => 'Прибыло в сортировочный центр в стране назначения'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-20T03:32:35Z',
                                'status' => 'Заказ принят у отправителя'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-20T03:03:48Z',
                                'status' => 'Прибыло в страну назначения'],
                            ['courier' => 'Юнэкс',
                                'date' => '2021-03-19T21:12:00Z',
                                'status' => 'Передано в доставку "последней мили".'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-20T02:35:17+08:00',
                                'status' => 'Выпущено таможней'],
                            ['courier' => 'Юнэкс',
                                'date' => '2021-03-19T16:58:00Z',
                                'status' => 'Выпуск товаров без уплаты таможенных платежей'],
                            ['courier' => 'Юнэкс',
                                'date' => '2021-03-19T05:08:00Z',
                                'status' => 'Начало таможенного оформления'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-18T21:56:23+08:00',
                                'status' => 'Начало таможенного оформления'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-18T14:42:51+08:00',
                                'status' => 'Прибыло в аэропорт страны назначения'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-18T09:53:50+08:00',
                                'status' => 'Передано авиакомпании'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-18T02:42:49+08:00',
                                'status' => 'Покинуло Шэньчжэнь'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-16T14:51:37+08:00',
                                'status' => 'Выпущено таможней страны отправления'],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-15T10:00:03Z',
                                'status' => 'Закреплен за курьером'],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-15T10:00:02Z',
                                'status' => 'Заказ оформлен'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-15T13:30:45+08:00',
                                'status' => 'Прибыло на склад'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-14T22:26:12+03:00',
                                'status' => 'Создан заказ'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-15T03:01:44+08:00',
                                'status' => 'Покинуло сортировочный центр'],
                            ['courier' => 'DPD Россия',
                                'date' => '2021-03-14T10:00:01Z',
                                'status' => 'Заказ оформлен'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-14T17:45:04+08:00',
                                'status' => 'Прибыло в сортировочный центр'],
                            ['courier' => 'Cainiao',
                                'date' => '2021-03-14T09:16:36+08:00',
                                'status' => 'Принято перевозчиком']
                        ],
                        'origin_country' => 'China',
                        'other' => [
                            'recipient' => 'Partnerpoint pn00347.488v',
                            'status' => 'archive'
                        ],
                        'status' => True,
                        'sub_status' => 'delivered',
                        'track' => 'AEPU0003733721RU2'
                    ]
                ];
            }
        }

        return response($_data, 200);
    }
}
