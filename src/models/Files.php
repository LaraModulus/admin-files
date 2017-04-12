<?php
namespace LaraMod\AdminFiles\Models;

use LaraMod\AdminCore\Scopes\AdminCoreOrderByCreatedAtScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;

class Files extends Model
{
    public $timestamps = true;
    protected $table = 'files';

    use SoftDeletes;
    protected $guarded = ['id'];

    protected $casts = [
        'viewable' => 'boolean',
        'exif_data' => 'object'
    ];

    protected $appends = [
        'full_path',
        'real_path',
        'file_size',
        'thumb'
    ];
    protected $dates = ['deleted_at'];

    public function scopeVisible($q)
    {
        return $q->whereViewable(true);
    }

    public function directory(){
        return $this->hasOne(Directories::class, 'id', 'directories_id');
    }

    public function getFullPathAttribute(){
        return $this->directory->full_path.(PHP_OS == 'WINNT' ? '\\' : '/').$this->filename;
    }

    public function getRealPathAttribute(){
        return realpath(public_path($this->full_path));
    }

    public function getFileSizeAttribute(){
        return filesize($this->real_path);
    }

    public function getThumbAttribute(){
        if(in_array($this->mime_type, ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/tif', 'image/bmp'])){
            return Cache::remember('image_'.$this->id, 60*24*30, function(){
                return Image::make($this->full_path)->fit(100, 100)->encode('data-url');
            });
        }else{
            return Cache::remember('image_'.$this->id, 60*24*30, function(){
                return Image::canvas(100,100)->text(strtoupper($this->extension),50,50,function($font){
                    $font->file(2);
                    $font->size(100);
                    $font->color('#000000');
                    $font->align('center');
                    $font->valign('center');
                })->encode('data-url');
            });
        }
    }

    protected function bootIfNotBooted()
    {
        parent::boot();
        static::addGlobalScope(new AdminCoreOrderByCreatedAtScope());
    }



}