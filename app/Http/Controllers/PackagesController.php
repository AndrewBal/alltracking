<?php

namespace App\Http\Controllers;

use App\Models\Seo\UrlAlias;
use App\Http\Controllers\Controller;
use App\Models\Structure\Node;
use App\Models\Structure\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;


class PackagesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(Request $request, $package = null)
    {
        global $wrap;
        $_locale = $wrap['locale'] ?? DEFAULT_LOCALE;
        $_locale = config("laravellocalization.supportedLocales.{$_locale}");
        $_others = NULL;
        $_response = NULL;


        $_deliveries = Node::getDeliveries();
        $_others['deliveries'] = json_encode($_deliveries['deliveries']);
        $_others['alphabet'] = json_encode($_deliveries['alphabet']);

        if (is_null($package)) $package = Cookie::get('packages', null);

        $_page = new Page();
        $_page->fill([
            'title' => 'Tracking' . ($package ? " {$package}" : null),
            'language' => $_page->locale,
            'generate_url' => _r('tracking_packages')
        ]);
        $_page->setWrap([
            'seo.title' => $_page->title,
            'seo.robots' => 'noindex, nofollow',
            'page.title' => $_page->title,
            'page.translate_links' => collect([]),
            'breadcrumbs' => render_breadcrumb([
                'entity' => $_page
            ]),
        ]);
        $_page->packages = $package ? $this->getData([$package]) : null;
        $_packages = null;
        if($_page->packages) {
            foreach ($_page->packages as $_package){
                $_packages[] = $_package['track'];
            }
        }

        return
            View::first([
            "frontend.{$_page->device_template}.pages.packages",
            'frontend.default.pages.packages'
        ], [
            'wrap' => app('wrap')->render(),
            '_item' => $_page,
            '_packages' => $_packages,
            '_others' => $_others,
            ]);
    }

    public function getData($packages = [])
    {
        $_response = null;
        foreach ($packages as $_package) {

        }
        $_response = [
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

        return $_response;
    }

    public function setPackage(Request $request)
    {
        $_response = null;
        $_packages = $request->input('items', null);
        if ($_packages) {
            Cookie::queue(Cookie::make('packages', $_packages));
            $_response = [
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

        } else {
            Cookie::queue(Cookie::forget('packages'));
        }

        return response($_response, 200);
    }
}
