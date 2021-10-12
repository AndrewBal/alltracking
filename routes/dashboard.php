<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [
    'as'   => 'oleus',
    'uses' => 'DashboardController@index'
]);

Route::get('/polygon', [
    'as'   => 'oleus.polygon',
    'uses' => 'DashboardController@polygon'
]);

Route::get('/artisan/{command}/{target}', [
    'as'   => 'oleus.artisan',
    'uses' => 'DashboardController@artisan'
]);

Route::get('/users/export', [
    'as'   => 'oleus.users.export',
    'uses' => 'Users\UserController@export'
]);

Route::resource('/users', 'Users\UserController', [
    'names' => [
        'index'   => 'oleus.users',
        'show'    => 'oleus.users.show',
        'create'  => 'oleus.users.create',
        'update'  => 'oleus.users.update',
        'store'   => 'oleus.users.store',
        'edit'    => 'oleus.users.edit',
        'destroy' => 'oleus.users.destroy'
    ]
]);

Route::resource('/roles', 'Users\RoleController', [
    'names' => [
        'index'   => 'oleus.roles',
        'show'    => 'oleus.roles.show',
        'create'  => 'oleus.roles.create',
        'update'  => 'oleus.roles.update',
        'store'   => 'oleus.roles.store',
        'edit'    => 'oleus.roles.edit',
        'destroy' => 'oleus.roles.destroy'
    ]
]);

Route::get('/roles/{role}/{locale}', [
    'as'   => 'oleus.roles.translate',
    'uses' => 'Users\RoleController@translate'
]);

Route::get('/groups/users', [
    'as'   => 'oleus.groups.export',
    'uses' => 'Users\GroupController@users'
]);

Route::resource('/groups', 'Users\GroupController', [
    'names' => [
        'index'   => 'oleus.groups',
        'show'    => 'oleus.groups.show',
        'create'  => 'oleus.groups.create',
        'update'  => 'oleus.groups.update',
        'store'   => 'oleus.groups.store',
        'edit'    => 'oleus.groups.edit',
        'destroy' => 'oleus.groups.destroy'
    ]
]);

Route::get('/groups/{group}/{locale}', [
    'as'   => 'oleus.groups.translate',
    'uses' => 'Users\GroupController@translate'
]);

Route::match([
    'get',
    'post'
], 'settings/{setting}', [
    'as'   => 'oleus.settings.option',
    'uses' => 'Others\SettingsController@view'
]);

Route::post('/settings/{setting}/{action}', [
    'as'   => 'oleus.settings.option.translate',
    'uses' => 'Others\SettingsController@translate'
]);

Route::resource('/variables', 'Component\VariablesController', [
    'names' => [
        'index'   => 'oleus.variables',
        'show'    => 'oleus.variables.show',
        'create'  => 'oleus.variables.create',
        'edit'    => 'oleus.variables.edit',
        'update'  => 'oleus.variables.update',
        'store'   => 'oleus.variables.store',
        'destroy' => 'oleus.variables.destroy',
    ]
]);

Route::get('/variables/{variable}/{locale}', [
    'as'   => 'oleus.variables.translate',
    'uses' => 'Component\VariablesController@translate'
]);

Route::resource('/pages', 'Structure\PageController', [
    'names' => [
        'index'   => 'oleus.pages',
        'show'    => 'oleus.pages.show',
        'create'  => 'oleus.pages.create',
        'update'  => 'oleus.pages.update',
        'store'   => 'oleus.pages.store',
        'edit'    => 'oleus.pages.edit',
        'destroy' => 'oleus.pages.destroy'
    ]
]);

Route::get('/pages/{page}/{locale}', [
    'as'   => 'oleus.pages.translate',
    'uses' => 'Structure\PageController@translate'
]);

Route::post('/pages/item/{page}/{action}/{key?}', [
    'as'   => 'oleus.pages.item',
    'uses' => 'Structure\PageController@fields'
]);

Route::resource('/nodes', 'Structure\NodeController', [
    'names' => [
        'index'   => 'oleus.nodes',
        'show'    => 'oleus.nodes.show',
        'create'  => 'oleus.nodes.create',
        'update'  => 'oleus.nodes.update',
        'store'   => 'oleus.nodes.store',
        'edit'    => 'oleus.nodes.edit',
        'destroy' => 'oleus.nodes.destroy'
    ]
]);

Route::get('/nodes/{node}/{locale}', [
    'as'   => 'oleus.nodes.translate',
    'uses' => 'Structure\NodeController@translate'
]);

Route::post('/nodes/{node}/sort', [
    'as'   => 'oleus.nodes.sort',
    'uses' => 'Structure\NodeController@save_sort'
]);

Route::post('/nodes/fields', [
    'as'   => 'oleus.nodes.fields',
    'uses' => 'Structure\NodeController@fields'
]);

Route::post('/nodes/relation/{node}/{field}/{action}/{key?}', [
    'as'   => 'oleus.nodes.relation',
    'uses' => 'Structure\NodeController@relation'
]);

Route::post('/nodes/relation_node', [
    'as'   => 'oleus.nodes.relation_node',
    'uses' => 'Structure\NodeController@relation_node'
]);

Route::resource('/tags', 'Structure\TagController', [
    'names' => [
        'index'   => 'oleus.tags',
        'show'    => 'oleus.tags.show',
        'create'  => 'oleus.tags.create',
        'update'  => 'oleus.tags.update',
        'store'   => 'oleus.tags.store',
        'edit'    => 'oleus.tags.edit',
        'destroy' => 'oleus.tags.destroy'
    ]
]);

Route::get('/tags/{tag}/{locale}', [
    'as'   => 'oleus.tags.translate',
    'uses' => 'Structure\TagController@translate'
]);

Route::get('/node-tags', [
    'as'   => 'oleus.node_tags',
    'uses' => 'Structure\NodeTagController@index'
]);

Route::post('/node-tags/sort', [
    'as'   => 'oleus.node_tags.sort',
    'uses' => 'Structure\NodeTagController@save_sort'
]);

Route::post('/node-tags/{node_tag}/{action?}', [
    'as'   => 'oleus.node_tags.item',
    'uses' => 'Structure\NodeTagController@tag'
]);

Route::resource('/faqs', 'Component\FaqController', [
    'names' => [
        'index'   => 'oleus.faqs',
        'show'    => 'oleus.faqs.show',
        'create'  => 'oleus.faqs.create',
        'update'  => 'oleus.faqs.update',
        'store'   => 'oleus.faqs.store',
        'edit'    => 'oleus.faqs.edit',
        'destroy' => 'oleus.faqs.destroy'
    ]
]);

Route::get('/faqs/{faq}/{locale}', [
    'as'   => 'oleus.faqs.translate',
    'uses' => 'Component\FaqController@translate'
]);

Route::post('/faqs/item/{faq}/{action}/{id?}', [
    'as'   => 'oleus.faqs.item',
    'uses' => 'Component\FaqController@faqs'
]);

Route::post('/faqs/{faq}/sort', [
    'as'   => 'oleus.faqs.sort',
    'uses' => 'Structure\FaqController@sort'
]);

Route::resource('/blocks', 'Component\BlockController', [
    'names' => [
        'index'   => 'oleus.blocks',
        'show'    => 'oleus.blocks.show',
        'create'  => 'oleus.blocks.create',
        'update'  => 'oleus.blocks.update',
        'store'   => 'oleus.blocks.store',
        'edit'    => 'oleus.blocks.edit',
        'destroy' => 'oleus.blocks.destroy'
    ]
]);

Route::get('/blocks/{block}/{locale}', [
    'as'   => 'oleus.blocks.translate',
    'uses' => 'Component\BlockController@translate'
]);

Route::resource('/advantages', 'Component\AdvantageController', [
    'names' => [
        'index'   => 'oleus.advantages',
        'show'    => 'oleus.advantages.show',
        'create'  => 'oleus.advantages.create',
        'update'  => 'oleus.advantages.update',
        'store'   => 'oleus.advantages.store',
        'edit'    => 'oleus.advantages.edit',
        'destroy' => 'oleus.advantages.destroy'
    ]
]);

Route::get('/advantages/{advantage}/{locale}', [
    'as'   => 'oleus.advantages.translate',
    'uses' => 'Component\AdvantageController@translate'
]);

Route::post('/advantages/item/{advantage}/{action}/{id?}', [
    'as'   => 'oleus.advantages.item',
    'uses' => 'Component\AdvantageController@advantage'
]);

Route::post('/advantages/{advantage}/sort', [
    'as'   => 'oleus.advantages.sort',
    'uses' => 'Component\AdvantageController@sort'
]);

Route::resource('/banners', 'Component\BannerController', [
    'names' => [
        'index'   => 'oleus.banners',
        'show'    => 'oleus.banners.show',
        'create'  => 'oleus.banners.create',
        'update'  => 'oleus.banners.update',
        'store'   => 'oleus.banners.store',
        'edit'    => 'oleus.banners.edit',
        'destroy' => 'oleus.banners.destroy'
    ]
]);

Route::get('/banners/{banner}/{locale}', [
    'as'   => 'oleus.banners.translate',
    'uses' => 'Component\BannerController@translate'
]);

Route::resource('/sliders', 'Component\SliderController', [
    'names' => [
        'index'   => 'oleus.sliders',
        'show'    => 'oleus.sliders.show',
        'create'  => 'oleus.sliders.create',
        'update'  => 'oleus.sliders.update',
        'store'   => 'oleus.sliders.store',
        'edit'    => 'oleus.sliders.edit',
        'destroy' => 'oleus.sliders.destroy'
    ]
]);

Route::post('/sliders/item/{slider}/{action}/{id?}', [
    'as'   => 'oleus.sliders.item',
    'uses' => 'Component\SliderController@slider'
]);

Route::post('/sliders/{slider}/sort', [
    'as'   => 'oleus.sliders.sort',
    'uses' => 'Component\SliderController@sort'
]);

Route::resource('/galleries', 'Component\GalleryController', [
    'names' => [
        'index'   => 'oleus.galleries',
        'show'    => 'oleus.galleries.show',
        'create'  => 'oleus.galleries.create',
        'update'  => 'oleus.galleries.update',
        'store'   => 'oleus.galleries.store',
        'edit'    => 'oleus.galleries.edit',
        'destroy' => 'oleus.galleries.destroy'
    ]
]);

Route::get('/galleries/{gallery}/{locale}', [
    'as'   => 'oleus.galleries.translate',
    'uses' => 'Component\GalleryController@translate'
]);

Route::resource('/menus', 'Component\MenuController', [
    'names' => [
        'index'   => 'oleus.menus',
        'show'    => 'oleus.menus.show',
        'create'  => 'oleus.menus.create',
        'edit'    => 'oleus.menus.edit',
        'update'  => 'oleus.menus.update',
        'store'   => 'oleus.menus.store',
        'destroy' => 'oleus.menus.destroy',
    ],
]);

Route::post('/menus/search_link', [
    'as'   => 'oleus.menus.link',
    'uses' => 'Component\MenuController@link'
]);

Route::post('/menus/item/{menu}/{action}/{id?}', [
    'as'   => 'oleus.menus.item',
    'uses' => 'Component\MenuController@item'
]);

Route::post('/menus/{menu}/sort', [
    'as'   => 'oleus.menus.sort',
    'uses' => 'Component\MenuController@sort'
]);

Route::resource('/forms', 'Component\FormController', [
    'names' => [
        'index'   => 'oleus.forms',
        'show'    => 'oleus.forms.view',
        'create'  => 'oleus.forms.create',
        'store'   => 'oleus.forms.store',
        'edit'    => 'oleus.forms.edit',
        'update'  => 'oleus.forms.update',
        'destroy' => 'oleus.forms.destroy',
    ]
]);

Route::get('/forms/{form}/{locale}', [
    'as'   => 'oleus.forms.translate',
    'uses' => 'Component\FormController@translate'
]);

Route::post('/forms/field/{form}/{action}/{key?}', [
    'as'   => 'oleus.forms.item',
    'uses' => 'Component\FormController@field'
]);

Route::post('/forms/{form}/sort', [
    'as'   => 'oleus.forms.sort',
    'uses' => 'Component\FormController@sort'
]);

Route::resource('/forms-data', 'Component\FormDataController', [
    'names'  => [
        'index'   => 'oleus.forms_data',
        'edit'    => 'oleus.forms_data.edit',
        'update'  => 'oleus.forms_data.update',
        'destroy' => 'oleus.forms_data.destroy',
    ],
    'except' => [
        'show',
        'create',
        'store',
    ]
]);
