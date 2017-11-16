<?php

namespace LaraMod\Admin\Files\Traits;

use Illuminate\Http\UploadedFile;
use LaraMod\Admin\Files\Models\Directories;
use LaraMod\Admin\Files\Models\Files;

trait Helper
{
    /**
     * Function to upload files. Returns file id on success
     *
     * @param UploadedFile $uploader
     * @param string       $path
     * @param null         $filename
     * @return mixed
     * @throws \Exception
     */
    public function upload(UploadedFile $uploader, $path = 'uploads', $filename = null)
    {
        $directory = $this->directory_structure($path);
        $path = $directory->full_path;
        $image_hash = null;
        if (is_null($filename)) {
            $filename = md5($uploader->getClientOriginalName() . $uploader->getSize()) . '.' . $uploader->getClientOriginalExtension();
        }

        if (in_array($uploader->getMimeType(), ['image/jpeg', 'image/png']) && file_exists(public_path($path . '/' . $filename))) {
            $image_hash = $this->imageHash(public_path($path . '/' . $filename));
            $existing = Files::where('image_hash', $image_hash)->first();
            if ($existing) {
                return $existing;
            }
        }
        try {
            $mime_type = $uploader->getMimeType();
        } catch (\Exception $e) {
            $mime_type = null;
        }
        try {
            if (substr($uploader->getMimeType(), 0, 5) == 'image') {
                $exif_data = json_decode(json_encode(exif_read_data(public_path($path . '/' . $filename))));
            } else {
                $exif_data = [];
            }
        } catch (\Exception $e) {
            $exif_data = [];
        }
        $ext = $uploader->getClientOriginalExtension();

        if (!file_exists(public_path($path . '/' . $filename))) {
            $uploader->move(public_path($directory->full_path), $filename);
        }
        $file = new Files();
        $file->fill([
            'filename'      => $filename,
            'extension'     => $ext,
            'mime_type'     => $mime_type,
            'exif_data'     => $exif_data,
            'size'          => $uploader->getClientSize(),
            'width'         => $exif_data ? $exif_data->COMPUTED->Width : null,
            'height'        => $exif_data ? $exif_data->COMPUTED->Height : null,
            'original_name' => $uploader->getClientOriginalName(),
            'image_hash'    => $image_hash,
            'directories_id' => $directory->id,
        ])->save();
        if ($file->id) {
            return $file;
        }
        throw new \Exception('Can\'t save file into database file', 1);

    }

    /**
     * Returns base64 encoded string from serialization of file $number_of_pixels*$number_of_pixels matrix + width,height and file size
     *
     * @param     $file
     * @param int $number_of_pixels
     * @return null|string
     * @throws \Exception
     */
    public static function imageHash($file, $number_of_pixels = 10)
    {
        if (!file_exists($file)) {
            throw new \Exception('File does not exists!', 404);
        }

        $mime_type = mime_content_type($file);
        if ($mime_type == 'image/jpeg') {
            $img = imagecreatefromjpeg($file);
        } elseif ($mime_type == 'image/png') {
            $img = imagecreatefrompng($file);
        } else {
            return null;
        }
        $pixels_matrix = [];
        for ($w = 1; $w <= $number_of_pixels; $w++) {
            for ($h = 1; $h <= $number_of_pixels; $h++) {
                $pixels_matrix[$w][$h] = imagecolorat($img, $w, $h);
            }
        }
        list($width, $height) = getimagesize($file);
        $pixels_matrix['w'] = $width;
        $pixels_matrix['h'] = $height;
        $pixels_matrix['s'] = filesize($file);
        imagedestroy($img);

        return md5(serialize($pixels_matrix));
    }


    public function download($path = null, $directory = 'uploads')
    {
        if (!$path) {
            return null;
        }
        $path = explode('?', $path)[0];

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $filename = md5($path) . '.' . $ext;
        $directory = $this->directory_structure($directory);
        $image_hash = null;


        if (!file_exists(public_path($directory->full_path .'/'. $filename))) {
//            if ($this->remoteFileExists($path)) {
            try {
                copy($path, public_path($directory->full_path .'/'. $filename));
            } catch (\Exception $e) {
                return $e;
            }
//            }
        } else {
            if (in_array(mime_content_type(public_path($directory->full_path .'/'. $filename)), ['image/jpeg', 'image/png'])) {
                $image_hash = $this->imageHash(public_path($directory->full_path .'/'. $filename));
                $existing = Files::where('image_hash', $image_hash)->first();
                if ($existing) {
                    return $existing;
                }
            }
        }


        try {
            $exif_data = json_decode(json_encode(exif_read_data(public_path($directory->full_path .'/'. $filename))));
        } catch (\Exception $e) {
            $exif_data = [];
        }
        try {
            $mime_type = mime_content_type(public_path($directory->full_path .'/'. $filename));
        } catch (\Exception $e) {
            $mime_type = null;
        }
        $file = Files::create([
            'filename'      => $filename,
            'exif_data'     => $exif_data,
            'mime_type'     => $mime_type,
            'extension'     => $ext,
            'size'          => @filesize(public_path($directory->full_path .'/'. $filename)),
            'width'         => $exif_data ? $exif_data->COMPUTED->Width : null,
            'height'        => $exif_data ? $exif_data->COMPUTED->Height : null,
            'original_name' => pathinfo($path, PATHINFO_FILENAME),
            'image_hash'    => $image_hash,
            'directories_id' => $directory->id,
        ]);

        return $file;


    }

    public function directory_structure($path){
        $directories_path = explode('/', $path);
        if(!file_exists($path)){
            mkdir($path, 0755, true);
        }
        $parent_id = 0;
        foreach($directories_path as $dir){
            $parent = Directories::where('path','like', $dir)->where('directories_id', $parent_id)->first();
            if(!$parent){
                $parent = Directories::create(['path' => $dir, 'directories_id' => $parent_id]);
            }
            $parent_id = $parent->id;
        }
        return $parent; //returns last directory
    }
}