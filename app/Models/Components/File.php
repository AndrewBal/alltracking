<?php

namespace App\Models\Components;

use App\Libraries\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File as FileStorage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
    use BaseModel;

    const IMAGE_MIMETYPE = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/tiff',
    ];
    protected $table = 'files_managed';
    protected $fillable = [
        'file_base_name',
        'file_name',
        'file_mime',
        'file_size',
        'title',
        'alt',
        'sort',
    ];
    protected $attributes = [
        'id'             => NULL,
        'file_base_name' => NULL,
        'file_name'      => NULL,
        'file_mime'      => NULL,
        'file_size'      => NULL,
        'title'          => NULL,
        'alt'            => NULL,
        'sort'           => 0,
    ];
    public $translatable = [
        'title',
        'alt',
    ];
    public $timestamps = FALSE;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
    }

    public function setDuplicate()
    {
        $_new_entity = $this->replicate();
        $_new_entity->save();

        return $_new_entity;
    }

    //    public static function findOrCreate($file_name, $path_to_file)
    //    {
    //        $file = [
    //            'file_base_name' => strtolower($file_name),
    //            'file_name'      => strtolower($file_name),
    //            'file_mime'      => NULL,
    //            'file_size'      => NULL,
    //        ];
    //        $_file_path = "{$path_to_file}/{$file['file_name']}";
    //        if (!($_file = self::where('file_name', $file['file_name'])->first())) {
    //            if (Storage::disk('base')->exists($_file_path)) {
    //                $_file_mime_type = Storage::disk('base')->mimeType($_file_path);
    //                $_file_size = Storage::disk('base')->size($_file_path);
    //                Storage::disk('base')->move($_file_path, "uploads/{$_file_name}");
    //                self::create([
    //                    'filename' => $_file_name,
    //                    'filemime' => $_file_mime_type,
    //                    'filesize' => $_file_size,
    //                ]);
    //            }
    //        }
    //
    //        return $_file ? $_file : new File();
    //    }

    public function getBaseUrlAttribute()
    {
        return Storage::url("{$this->file_name}");
    }

    //    public static function create_file_by_url($url, $folder = NULL)
    //    {
    //        $_response = NULL;
    //        $_file_url = storage_path($url);
    //        if (FileStorage::exists($_file_url)) {
    //            $_file_base_name = FileStorage::basename($_file_url);
    //            $_file_size = FileStorage::size($_file_url);
    //            $_file_extension = FileStorage::extension($_file_url);
    //            $_file_name = Str::slug(str_replace(".{$_file_extension}", '', $_file_base_name)) . '-' . uniqid() . ".{$_file_extension}";
    //            $_item = File::where('base_name', $_file_base_name)
    //                ->where('filesize', $_file_size)
    //                ->first();
    //            if (!$_item) {
    //                $_file_mime_type = FileStorage::mimeType($_file_url);
    //                $_file_save_path = $folder ? "{$folder}/{$_file_name}" : $_file_name;
    //                FileStorage::copy($_file_url, storage_path("app/public/{$_file_save_path}"));
    //                $_item = new File();
    //                $_item->fill([
    //                    'base_name' => $_file_base_name,
    //                    'filename'  => $_file_name,
    //                    'filemime'  => $_file_mime_type,
    //                    'filesize'  => $_file_size,
    //                ]);
    //                $_item->save();
    //            } else {
    //                $_item = $_item->replicate();
    //                $_item->save();
    //            }
    //            $_response = $_item;
    //        }
    //
    //        return $_response;
    //    }
}
