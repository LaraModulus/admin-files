<?php
namespace LaraMod\Admin\Files\Models;

use LaraMod\Admin\Core\Scopes\AdminCoreOrderByCreatedAtScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class Directories extends Model
{
    public $timestamps = true;
    protected $table = 'directories';

    protected $guarded = ['id'];
    protected $fillable = ['path', 'directories_id'];

    protected $casts = [
    ];
    protected $appends = [
      'full_path',
        'real_path',
    ];

    public function parent(){
        return $this->hasOne(Directories::class, 'id', 'directories_id');
    }

    public function files(){
        return $this->hasMany(Files::class, 'directories_id', 'id');
    }

    public function children(){
        return $this->hasMany(Directories::class, 'directories_id', 'id');
    }

    public function getFullPathAttribute(){
        $path = new Collection();
        $category = $this;
        $path->prepend($this->path);
        while($category = $category->parent){
            $path->prepend($category->path);
        }
        return $path->implode(PHP_OS == 'WINNT' ? '\\' : '/');
    }

    public function getRealPathAttribute(){
        return realpath(public_path($this->full_path));
    }

    protected function bootIfNotBooted()
    {
        parent::boot();
        static::addGlobalScope(new AdminCoreOrderByCreatedAtScope());
    }

}