LaraMod Admin Filesmanager 0.* Alpha
----------------------------
LaraMod is a modular Laravel based CMS.
https://github.com/LaraModulus

**WARNING: Until v1 there will be no backward compatibility and some versions may require migrate:refresh** 

Installation
---------------
```
composer require laramod/admin-files
```
 **config/app.php**
 
```php 
'providers' => [
    ...
    LaraMod\Admin\Files\AdminFilesServiceProvider::class,
    Intervention\Image\ImageServiceProvider::class,
],

'aliases' => [
    ...
    'Image' => Intervention\Image\Facades\Image::class,
]
```

**Publish migrations**
```
php artisan vendor:publish --tag="migrations"
```
**Publish assets**
```
php artisan vendor:publish --tag="public"
```
**Run migrations**
```
php artisan migrate
```

In `config/admincore.php` you can edit admin menu

**DEMO:** http://laramod.novaspace.eu/admin
```
user: admin@admin.com
pass: admin
```