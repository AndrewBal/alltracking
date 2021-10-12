<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\View;
use App\Libraries\Fields;
use App\Models\Components\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Models\Components\Variable;
use App\Models\Components\Block;
use App\Models\Components\Banner;
use App\Models\Components\Advantage;
use App\Models\Components\Slider;
use App\Models\Components\Gallery;
use App\Models\Components\Faq;
use App\Models\Components\Menu;
use App\Models\Components\MenuItems;
use App\Models\Form\Forms;

if (!function_exists('render_attributes')) {
    function render_attributes($attributes = [])
    {
        if (!is_array($attributes) || !count($attributes)) return NULL;
        $_attributes = NULL;
        foreach ($attributes as $key => $attribute) {
            if (is_string($key) && is_array($attribute) && count($attribute)) {
                $_array_attribute = [];
                foreach ($attribute as $_data) if ($_data) $_array_attribute[] = $_data;
                $_array_attribute = count($_array_attribute) ? implode(' ', $_array_attribute) : NULL;
                $_attributes[] = "{$key}=\"{$_array_attribute}\"";
            } elseif (is_string($key) && !is_null($attribute) && !is_bool($attribute) && (is_string($attribute) || is_numeric($attribute) || is_float($attribute))) {
                $_attributes[] = "{$key}=\"{$attribute}\"";
            } elseif (is_string($key) && (is_null($attribute) || (is_bool($attribute) && $attribute == TRUE))) {
                $_attributes[] = $key;
            } elseif (!is_null($attribute) && !is_bool($attribute)) {
                if ($attribute) $_attributes[] = $attribute;
            }
        }

        return $_attributes ? implode(' ', $_attributes) : NULL;
    }
}

if (!function_exists('render_field')) {
    function render_field($name, $options = [])
    {
        $_item = new Fields($name, $options);

        return $_item->_render();
    }
}

if (!function_exists('render_breadcrumb')) {
    function render_breadcrumb($options = [])
    {
        global $wrap;
        $_schema = [
            "@context"        => "https://schema.org",
            "@type"           => "BreadcrumbList",
            "itemListElement" => []
        ];
        if (!isset($wrap['locale'])) $wrap['locale'] = DEFAULT_LOCALE;
        $_options = array_merge([
            'entity' => NULL,
            'parent' => NULL,
        ], $options);
        $_breadcrumb = collect([]);
        if ($_options['entity']) {
            $_position = 2;
            $_entity_class_basename = class_basename($_options['entity']);
            if ($_options['parent']) {
                foreach ($_options['parent'] as $_parent) {
                    if (is_array($_parent)) {
                        $_breadcrumb->push([
                            'name'     => $_parent['title'],
                            'url'      => $_parent['url'] ?? NULL,
                            'position' => $_position
                        ]);
                    } else {
                        $_breadcrumb->push([
                            'name'     => $_parent->breadcrumb_title ? : $_parent->title,
                            'url'      => $_parent->generate_url ?? NULL,
                            'position' => $_position
                        ]);
                    }
                    $_position++;
                }
            }
            if (($_entity_class_basename == 'Page' && $_options['entity']->type != 'front') || $_entity_class_basename != 'Page') {
                $_breadcrumb->push([
                    'name'     => ($_options['entity']->breadcrumb_title ? : $_options['entity']->title) . ($wrap['seo']['page_number_suffix'] ?? NULL),
                    'url'      => $_options['entity']->generate_url ?? NULL,
                    'position' => $_position
                ]);
            }
        }
        if ($_breadcrumb->isNotEmpty()) {
            $_home_path = $wrap['locale'] != DEFAULT_LOCALE ? $wrap['locale'] : '/';
            $_breadcrumb->prepend([
                'name'     => trans('frontend.links.home'),
                'url'      => _u($_home_path),
                'position' => 1
            ]);
        } else {
            $_breadcrumb = NULL;
        }
        if ($_breadcrumb) {
            $_breadcrumb->map(function ($item) use (&$_schema, $wrap) {
                $_schema['itemListElement'][] = [
                    "@type"    => "ListItem",
                    "position" => $item['position'],
                    "item"     => [
                        "@id"  => "{$wrap['seo']['base_url']}{$item['url']}",
                        "name" => $item['name']
                    ]
                ];
            });
            app('wrap')->setMicrodata(json_encode($_schema));
        }

        return $_breadcrumb;
    }
}

if (!function_exists('render_preview_file')) {
    function render_preview_file($file, $options)
    {
        $_default = [
            'field' => NULL,
            'view'  => FALSE
        ];
        $options = array_merge_recursive_distinct($_default, $options);
        $_images_mimeType = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/tiff',
        ];
        $_template = in_array($file->file_mime, $_images_mimeType) ? 'image_preview' : 'file_preview';

        return View::make("backend.fields.{$_template}", compact('file', 'options'))
            ->render(function ($view, $content) {
                return clear_html($content);
            });
    }
}

if (!function_exists('render_image')) {
    function render_image($file = NULL, $preset = NULL, $options = [])
    {
        $_options = [
            'outside_file'   => NULL,
            'no_last_modify' => FALSE,
            'only_way'       => FALSE,
            'attributes'     => [
                'title' => $file->title ?? NULL,
                'alt'   => $file->alt ?? NULL,
            ]
        ];
        $_options = array_merge_recursive_distinct($_options, $options);
        $_presets = collect(config('preset_images'));
        $_file_mimetype = [
            'images'      => File::IMAGE_MIMETYPE,
            'no_generate' => [
                'image/x-icon',
                'image/vnd.microsoft.icon',
            ],
            'svg'         => [
                'image/svg+xml'
            ]
        ];
        $_no_image = is_null($file) ? TRUE : FALSE;
        $_file_is_image = FALSE;
        $_path = $preset ? public_path("preset/{$preset}") : public_path('images');
        $_file_path = $preset ? "preset/{$preset}" : "images";
        if ($file instanceof File || is_object($file)) {
            $_file_name = $file->file_name;
            if (in_array($file->file_mime, $_file_mimetype['images'])) {
                $_file_is_image = TRUE;
            }
        } elseif (is_string($file)) {
            $_file_name = $file;
            $_file_is_image = (boolean)preg_match('/(.jpg|jpeg|gif|png|svg)$/i', $file, $_file_is_image);
        } else {
            $_file_name = 'no-image.jpg';
            $_file_is_image = TRUE;
        }
        $_file_exists = file_exists("{$_path}/{$_file_name}");
        $_file_content = NULL;
        if (!$_file_exists) {
            FileFacade::isDirectory($_path) or FileFacade::makeDirectory($_path, 0777, TRUE, TRUE);
            if ($_options['outside_file']) {
                //                $_outside_file = NULL;
                //                $_outside_file_path = $_options['outside_file']['path'];
                //                $_outside_file_name = $_options['outside_file']['name'];
                //                if (file_exists("{$_path}/{$_outside_file_name}")) {
                //                    $_outside_file = "{$_file_path}/{$_outside_file_name}";
                //                } else {
                //                    $_headers = get_headers($_outside_file_path);
                //                    $_response_status_ok = stripos($_headers[0], "200 OK") ? TRUE : FALSE;
                //                    if ($_response_status_ok == FALSE) $_outside_file_path = 'images/no-image.jpg';
                //                    $_preset = $_presets->get($preset);
                //                    $_file = Image::make($_outside_file_path);
                //                    $_render_image = NULL;
                //                    $_w = isset($_preset['w']) && $_preset['w'] ? $_preset['w'] : NULL;
                //                    $_h = isset($_preset['h']) && $_preset['h'] ? $_preset['h'] : NULL;
                //                    $_background = isset($_preset['background']) && $_preset['background'] ? $_preset['background'] : NULL;
                //                    $_border = isset($_preset['border']) && $_preset['border'] ? $_preset['border'] : 0;
                //                    $_quality = isset($_preset['quality']) && is_numeric($_preset['quality']) ? $_preset['quality'] : 100;
                //                    $_render_image = NULL;
                //                    if ($_background) {
                //                        $_render_image = Image::canvas($_w, $_h, $_background);
                //                        $_w -= $_border;
                //                        $_h -= $_border;
                //                    }
                //                    if (isset($_preset['fit']) && $_w && $_h) {
                //                        $_file->fit($_w, $_h, function ($constraint) {
                //                            $constraint->aspectRatio();
                //                        });
                //                    } else {
                //                        $_file->resize($_w, $_h, function ($constraint) {
                //                            $constraint->aspectRatio();
                //                        });
                //                    }
                //                    if ($_render_image) {
                //                        $_render_image->insert($_file, 'center');
                //                    } else {
                //                        $_render_image = $_file;
                //                    }
                //                    if (isset($_preset['blur']) && $_preset['blur']) $_render_image->blur($_preset['blur']);
                //                    if (isset($_preset['watermark']) && is_array($_preset['watermark']) && isset($_preset['watermark']['image']) && $_preset['watermark']['image']) {
                //                        $_watermark_position = isset($_preset['watermark']['position']) && $_preset['watermark']['position'] ? $_preset['watermark']['position'] : 'center';
                //                        $_watermark_position_x = $_watermark_position != 'center' ? 15 : NULL;
                //                        $_watermark_position_y = $_watermark_position != 'center' ? 15 : NULL;
                //                        $_file->insert(public_path($_preset['watermark']['image']), $_watermark_position, $_watermark_position_x, $_watermark_position_y);
                //                    }
                //                    $_outside_file = "{$_file_path}/{$_outside_file_name}";
                //                    $_file_name_webp = explode('.', $_outside_file_name);
                //                    $_file_path_webp = "{$_file_path}/{$_file_name_webp[0]}.webp";
                //                    $_render_image->save(public_path($_outside_file), $_quality);
                //                    ImageOptimizer::optimize(public_path($_outside_file));
                //                    $_render_image->encode('webp')
                //                        ->save(public_path($_file_path_webp));
                //                }
                //                $_file_path = formalize_path($_outside_file, $_options['no_last_modify']);
            } else {
                // if($file instanceof File && $file->id == 4){
                //     dd($file->filemime, $_file_mimetype['svg'], in_array($file->filemime, $_file_mimetype['svg']));
                // }

                if ($_file_is_image) {
                    if ($preset && $_presets->has($preset)) {
                        $_preset = $_presets->get($preset);
                        $_width = isset($_preset['width']) && $_preset['width'] ? $_preset['width'] : NULL;
                        $_height = isset($_preset['height']) && $_preset['height'] ? $_preset['height'] : NULL;
                        $_background = isset($_preset['background']) && $_preset['background'] ? $_preset['background'] : NULL;
                        $_border = isset($_preset['border']) && $_preset['border'] ? $_preset['border'] : 0;
                        $_quality = isset($_preset['quality']) && is_numeric($_preset['quality']) ? $_preset['quality'] : 100;
                        $_render_image = NULL;
                        if ($_background) {
                            $_render_image = Image::canvas($_width, $_height, $_background);
                            $_width -= $_border;
                            $_height -= $_border;
                        }
                        if ($file instanceof File || is_object($file)) {
                            $_file_path_load = storage_path("app/public/$_file_name");
                            if (!$_file_path_load) return NULL;
                            $_file = Image::make($_file_path_load);
                        } else {
                            $_file_path_load = $_file_name == 'no-image.jpg' ? public_path('no-image.jpg') : public_path($_file_name);
                            $_file = Image::make($_file_path_load);
                        }
                        if (isset($_preset['fit']) && $_width && $_height) {
                            $_file->fit($_width, $_height, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                        } else {
                            //                                if ($_file->height() < $_h) $_file->heighten($_h);
                            //                                if ($_file->width() < $_w) $_file->widen($_w);
                            $_file->resize($_width, $_height, function ($constraint) {
                                $constraint->aspectRatio();
                                //                                    $constraint->upsize();
                            });
                            //                                $_file->resizeCanvas($_w, $_h, 'center', TRUE);
                            //                                $_background = isset($_preset['background']) && $_preset['background'] ? $_preset['background'] : NULL;
                            //                                if (isset($_preset['w']) && isset($_preset['h'])) $_file->resizeCanvas($_preset['w'], $_preset['h'], 'center', FALSE, $_background);
                        }
                        if ($_render_image) {
                            $_render_image->insert($_file, 'center');
                        } else {
                            $_render_image = $_file;
                        }
                        if (isset($_preset['blur']) && $_preset['blur']) {
                            $_render_image->blur($_preset['blur']);
                        }
                        if (isset($_preset['watermark']) && is_array($_preset['watermark']) && isset($_preset['watermark']['image']) && $_preset['watermark']['image']) {
                            $_watermark_position = isset($_preset['watermark']['position']) && $_preset['watermark']['position'] ? $_preset['watermark']['position'] : 'center';
                            $_watermark_position_x = $_watermark_position != 'center' ? 15 : NULL;
                            $_watermark_position_y = $_watermark_position != 'center' ? 15 : NULL;
                            $_render_image->insert(public_path($_preset['watermark']['image']), $_watermark_position, $_watermark_position_x, $_watermark_position_y);
                        }
                        if (is_string($file)) {
                            $_file_name = explode('/', $_file_name);
                            $_file_name = array_pop($_file_name);
                        }
                        $_file_path_file = "{$_file_path}/{$_file_name}";
                        $_file_name_webp = explode('.', $_file_name);
                        $_file_path_webp = "{$_file_path}/{$_file_name_webp[0]}.webp";
                        $_render_image->save(public_path($_file_path_file), $_quality);
                        //                        ImageOptimizer::optimize(public_path($_file_path_file));
                        $_render_image->encode('webp')
                            ->save(public_path($_file_path_webp));
                    } else {
                        $_file_path_load = (is_null($file) ? public_path($_file_name) : storage_path("app/public/$_file_name"));
                        if (file_exists($_file_path_load)) {
                            $_file = Image::make($_file_path_load);
                            $_file_path_file = "{$_file_path}/{$_file_name}";
                            $_file_name_webp = explode('.', $_file_name);
                            $_file_path_webp = "{$_file_path}/{$_file_name_webp[0]}.webp";
                            $_file->save(public_path($_file_path_file), 90);
                            //                            ImageOptimizer::optimize(public_path($_file_path_file));
                            $_file->encode('webp')
                                ->save(public_path($_file_path_webp));
                        } else {
                            return NULL;
                        }
                    }
                    $_file_path = formalize_path($_file_path_file, $_options['no_last_modify']);
                } elseif (isset($file->file_mime) && in_array($file->file_mime, $_file_mimetype['no_generate'])) {
                    $_file_path = "/storage/{$file->file_name}";
                    $_file_content = Storage::disk('public')
                        ->get($file->file_name);
                } elseif (isset($file->file_mime) && in_array($file->file_mime, $_file_mimetype['svg'])) {
                     $_options['attributes']['uk-svg'] = TRUE;
                    $_file_path = "/storage/{$file->file_name}";
                } elseif (is_string($file)) {
                    $_file_content = Storage::disk('base')
                        ->get($file);
                } else {
                    $_file_path = formalize_path('no-image.jpg', $_options['no_last_modify']);
                }
            }
        } else {
            $_file_path = formalize_path("{$_file_path}/{$_file_name}", $_options['no_last_modify']);
        }

        // if($file instanceof File && $file->id == 4){
        //     dd($file, $_options);
        // }

        if ($_options['only_way']) {
            return $_file_path;
        } elseif ($_file_content) {
            return $_file_content;
        } else {



            if (isset($_options['attributes']['title'])) {
                $_options['attributes']['title'] = str_replace([
                    "'",
                    '"'
                ], '', $_options['attributes']['title']);
            }
            if (isset($_options['attributes']['alt'])) {
                $_options['attributes']['alt'] = str_replace([
                    "'",
                    '"'
                ], '', $_options['attributes']['alt']);
            }
            $_attributes = render_attributes($_options['attributes']);
            if (isset($_options['attributes']['uk-svg'])) {
                $_output = '<img src="' . $_file_path . '" ' . $_attributes . '>';
            } else {
                $_file_path_webp = str_replace([
                    '.jpeg',
                    '.jpg',
                    '.png',
                    '.gif',
                    '.JPEG',
                    '.JPG',
                    '.GIF',
                    '.svg',
                    '.PNG'
                ], '.webp', $_file_path);
                $_output = '<picture' . (isset($_options['attributes']['class']) && $_options['attributes']['class'] ? " class='{$_options['attributes']['class']}'" : NULL) . '>';
               // $_output .= '<source type="image/webp" srcset="' . $_file_path_webp . '">';
                $_output .= '<source type="' . ($_no_image ? 'image/jpeg' : ($file->filemime ?? NULL)) . '" srcset="' . $_file_path . '">';
                $_output .= '<img src=' . $_file_path . ' uk-img="data-src:' . $_file_path . '" ' . $_attributes . '>';
                $_output .= '</picture>';
            }

            return $_output;
        }
    }
}


if (!function_exists('render_unique_value')) {
    function render_unique_value($value, $table, $field, $locale = NULL, $exception = NULL)
    {
        if (is_string($value)) {
            $_value = $value;
        } else {
            $_value = implode('', $value);
        }
        $_response = $_value;
        $_exists = DB::table($table)
            ->where($field, $_value)
            ->when($exception, function ($query) use ($exception) {
                $query->where('id', '<>', $exception);
            })
            ->when($locale, function ($query) use ($locale) {
                $query->where('locale', $locale);
            })
            ->count();
        if ($_exists) {
            $_i = 0;
            while ($_i <= 1000) {
                if (is_string($value)) {
                    $_generate = "{$value}-{$_i}";
                } else {
                    $value[0] = "{$value[0]}-{$_i}";
                    $_generate = implode('', $value);
                }
                $_exists = DB::table($table)
                    ->where($field, $_generate)
                    ->when($exception, function ($query) use ($exception) {
                        $query->where('id', '<>', $exception);
                    })
                    ->when($locale, function ($query) use ($locale) {
                        $query->where('locale', $locale);
                    })
                    ->count();
                if ($_exists == 0) {
                    $_response = $_generate;
                    break;
                }
                $_i++;
            }
        }

        return $_response;
    }
}

if (!function_exists('content_render')) {
    function content_render($model, $object = 'body')
    {
        $_content = NULL;
        if (is_object($model)) {
            $_content = $model->{$object};
            if (!$_content) return NULL;
            preg_match_all('|@short\((.*?)\)|xs', $_content, $_shorts);
            $_variables = NULL;
            if (count($_shorts[0]) && isset($_shorts[1]) && $_shorts[1]) {
                $_models_config = config('shortcut');
                foreach ($_shorts[0] as $_index_short => $_data_short) {
                    $_values = explode(';', $_shorts[1][$_index_short]);
                    $_variable = [
                        'code'    => $_data_short,
                        'replace' => NULL
                    ];
                    foreach ($_values as &$item) $item = trim($item);
                    if (isset($_values[0]) && $_values[0]) {
                        if (isset($_models_config[$_values[0]])) {
                            $_entity_data = $_models_config[$_values[0]];
                            $_entity_id = $_values[1] ?? $model->id;
                            $_entity_options = $_values[2] ?? [];
                            if ($_entity_options) {
                                $_entity_options = explode(',', $_entity_options);
                                $__entity_options = [];
                                if (is_array($_entity_options) && $_entity_options) {
                                    foreach ($_entity_options as &$_option) $_option = explode(':', $_option);
                                    foreach ($_entity_options as $_option) {
                                        $__entity_options[$_option[0]] = $_option[1] ?? TRUE;
                                    }
                                }
                                $_entity_options = $__entity_options;
                            }
                            $_entity = new $_entity_data['model'];
                            if (method_exists($_entity, 'getShortcut')) {
                                if (Str::is('*,*', $_entity_id)) {
                                    $_entity->items = $_entity_data['model']::whereIn($_entity_data['primary'], explode(',', $_entity_id))
                                        ->remember(REMEMBER_LIFETIME)
                                        ->get();
                                } else {
                                    $_entity = $_entity_data['model']::where($_entity_data['primary'], $_entity_id)
                                        ->remember(REMEMBER_LIFETIME)
                                        ->first();
                                }
                                $_entity_options['type'] = $_values[0];
                                $_entity_options['entity_id'] = $_entity_id;
                                if ($_entity && method_exists($_entity, 'getShortcut')) $_variable['replace'] = $_entity->getShortcut($_entity_options);
                            }
                        }
                    }
                    $_variables[] = $_variable;
                }
                foreach ($_variables as $_replace_code) {
                    $_content = str_replace($_replace_code['code'], $_replace_code['replace'], $_content);
                }
            }
        }

        return $_content ? replace_spaces($_content) : NULL;
    }
}

if (!function_exists('teaser_render')) {
    function teaser_render($entity, $count_word = 130)
    {
        $_response = NULL;
        if (is_object($entity)) {
            $_object = $entity->body;
            if ($entity->hasAttribute('teaser') && $entity->teaser) $_object = $entity->teaser;
            $_response = Arr::get(preg_split('/<div style=\"page-break-after\:always\">(.*?)<\/div>/s', $_object), 0);
            if ($_response) {
                $_response = truncate_string($_response, [
                    'count_word' => $count_word
                ]);
            }
            $_response = str_replace("\r\n", NULL, nl2br($_response));
            $_response = preg_replace("/(<br>|<\/br>|<br \/>){2,}/s", '<br>', nl2br($_response));
        }

        return $_response;
    }
}

if (!function_exists('render_variable')) {
    function render_variable($key, $variables = NULL)
    {
        try {
            $_cache_key = cache_key('variable', $key);

            return Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($key, $variables) {
                $_variable = new Variable();
                $_response = $_variable->_load($key);
                if (is_array($variables)) {
                    $_variables = [];
                    foreach ($variables as $_variable_key => $_variable_value) if (is_string($_variable_key)) $_variables["@{$_variable_key}"] = $_variable_value;
                    if ($_response && $_variables) $_response = strtr($_response, $_variables);
                }

                return $_response;
            });
        } catch (Throwable $exception) {
            report($exception);
        }

        return NULL;
    }
}

if (!function_exists('render_block')) {
    function render_block($entity, $options = [])
    {
        try {
            $_cache_key = cache_key('block', $entity);

            return Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($entity, $options) {
                if ($entity instanceof Block) {
                    $_item = $entity;
                } elseif (is_numeric($entity)) {
                    $_item = Block::where('id', $entity)
                        ->active()
                        ->first();
                }
                if ($_item) return $_item->_render($options);

                return NULL;
            });
        } catch (Throwable $exception) {
            report($exception);
        }

        return NULL;
    }
}

if (!function_exists('render_banner')) {
    function render_banner($entity, $options = [])
    {
        try {
            $_cache_key = cache_key('banner', $entity);

            return Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($entity, $options) {
                if ($entity instanceof Banner) {
                    $_item = $entity;
                } elseif (is_numeric($entity)) {
                    $_item = Banner::where('id', $entity)
                        ->active()
                        ->first();
                }
                if ($_item) return $_item->_render($options);

                return NULL;
            });
        } catch (Throwable $exception) {
            report($exception);
        }

        return NULL;
    }
}

if (!function_exists('render_advantage')) {
    function render_advantage($entity, $options = [])
    {
        try {
            $_cache_key = cache_key('advantage', $entity);

            return Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($entity, $options) {
                if ($entity instanceof Advantage) {
                    $_item = $entity;
                } elseif (is_numeric($entity)) {
                    $_item = Advantage::where('id', $entity)
                        ->active()
                        ->first();
                }
                if ($_item) return $_item->_render($options);

                return NULL;
            });
        } catch (Throwable $exception) {
            report($exception);
        }

        return NULL;
    }
}

if (!function_exists('render_slider')) {
    function render_slider($entity, $options = [])
    {
        try {
            $_cache_key = cache_key('slider', $entity);

            return Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($entity, $options) {
                if ($entity instanceof Slider) {
                    $_item = $entity;
                } elseif (is_numeric($entity)) {
                    $_item = Slider::where('id', $entity)
                        ->active()
                        ->first();
                }
                if ($_item) return $_item->_render($options);

                return NULL;
            });
        } catch (Throwable $exception) {
            report($exception);
        }


        return NULL;
    }
}

if (!function_exists('render_gallery')) {
    function render_gallery($entity, $options = [])
    {
        try {
            $_cache_key = cache_key('gallery', $entity);

            return Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($entity, $options) {
                if ($entity instanceof Gallery) {
                    $_item = $entity;
                } elseif (is_numeric($entity)) {
                    $_item = Gallery::where('id', $entity)
                        ->active()
                        ->first();
                }
                if ($_item) return $_item->_render($options);

                return NULL;
            });
        } catch (Throwable $exception) {
            report($exception);
        }


        return NULL;
    }
}

if (!function_exists('render_menu')) {
    function render_menu($entity, $options = [])
    {
        try {
            if ($entity instanceof Menu) {
                $_item = $entity;
            } elseif (is_numeric($entity)) {
                $_item = Menu::where('id', $entity)
                    ->active()
                    ->remember(REMEMBER_LIFETIME * 24 * 7)
                    ->first();
            }
            if ($_item) return $_item->_render($options);

            return NULL;
        } catch (Throwable $exception) {
            report($exception);
        }

        return NULL;
    }
}

if (!function_exists('render_menu_item')) {
    function render_menu_item($item, $level = 0)
    {
        $_hook_menu_function = function_exists("menu_{$item['item']['menu_id']}_item_render");
        if ($_hook_menu_function) return call_user_func("menu_{$item['item']['menu_id']}_item_render", $item, $level);
        $_output = NULL;
        $level++;
        $item['item']['attributes']['class'][] = "level-item-{$level}";
        $_output = '<li ' . render_attributes($item['item']['wrapper']) . '>';
        if ($item['item']['active'] || is_null($item['item']['path'])) {
            $_attr = Arr::only($item['item']['attributes'], 'class');
            if ($item['item']['icon']) {
                $_output .= '<span ' . render_attributes($_attr) . '>' . $item['item']['icon'] . '<div>' . $item['item']['title'] . '</div></span>';
            } else {
                $_output .= '<span ' . render_attributes($_attr) . '>' . $item['item']['title'] . '</span>';
            }
        } else {
            if ($item['item']['icon']) {
                $_output .= '<a ' . render_attributes($item['item']['attributes']) . '>' . $item['item']['icon'] . '<div>' . $item['item']['title'] . '</div></a>';
            } else {
                $_output .= '<a ' . render_attributes($item['item']['attributes']) . '>' . $item['item']['title'] . '</a>';
            }

        }
        if ($item['children']->isNotEmpty()) {
            $_output .= '<ul>';
            foreach ($item['children'] as $_sub_item) $_output .= render_menu_item($_sub_item, $level);
            $_output .= '</ul>';
        }
        $_output .= '</li>';

        return $_output;
    }
}

if (!function_exists('render_faq')) {
    function render_faq($entity, $options = [])
    {
        try {
            $_cache_key = cache_key('faq', $entity);

            return Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($entity, $options) {
                if ($entity instanceof Faq) {
                    $_item = $entity;
                } elseif (is_numeric($entity)) {
                    $_item = Faq::where('id', $entity)
                        ->active()
                        ->first();
                }
                if ($_item) return $_item->_render($options);

                return NULL;
            });
        } catch (Throwable $exception) {
            report($exception);
        }

        return NULL;
    }
}

if (!function_exists('render_form')) {
    function render_form($entity, $options = [])
    {
//        try {
            $_cache_key = cache_key('form', $entity);

            return Cache::remember($_cache_key, REMEMBER_LIFETIME * 24 * 7, function () use ($entity, $options) {
                if ($entity instanceof Forms) {
                    $_item = $entity;
                } elseif (is_numeric($entity)) {
                    $_item = Forms::where('id', $entity)
                        ->active()
                        ->first();
                }
                if ($_item) return $_item->_render($options);

                return NULL;
            });
//        } catch (Throwable $exception) {\
//            report($exception);
//        }

        return NULL;
    }
}
