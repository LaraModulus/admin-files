<?php
namespace LaraMod\Admin\Files\Controllers\Api;

use App\Http\Controllers\Controller;

use LaraMod\Admin\Files\Models\Directories;
use LaraMod\Admin\Files\Models\Files;
use Illuminate\Http\Request;

class FilesController extends Controller
{

    public function index(Request $request)
    {
        $files = new Files();
        if($request->has('directory')){
            return $files->where('directories_id', $request->get('directory'))->get();
        }
        return response()->json($files->get());
    }

    public function getForm(Request $request)
    {
        $file = ($request->has('id') ? Files::find($request->get('id')) : new Files());
        return response()->json($file);
    }

    public function postForm(Request $request)
    {

        $file = Files::firstOrCreate(['id' => $request->get('id')]);
        $path = public_path('uploads');
        if ($request->has('directories_id')) {
            $directory = Directories::find($request->get('directories_id'));
            if ($directory) {
                $path = $directory->full_path;
            }
        }
        try {
            if ($request->hasFile('file')) {

                if(in_array(request()->file('file')->getClientOriginalExtension(), ['php'])){
                    throw new \Exception("File not allowed", 1);
                }
                if (request()->file('file')->move($path, request()->file('file')->getClientOriginalName())) {
                    $file->filename = $request->file('file')->getClientOriginalName();
                }
            }
            $file->directories_id = $request->get('directories_id');
            /**
             * If request to move file is passed - move the file and change the path
             */
            if($file->id && $request->has('directories_id')){
                if($file->directory->id != $request->get('directories_id')){
                    rename($file->directory->full_path.'/'.$file->filename, Directories::find($request->get('directories_id'))->full_path.'/'.$file->filename);
                    $file->directories_id = $request->get('directories_id');
                    $path = $file->directory->path;
                }
            }
            if(!$file->id){
                $file->extension = pathinfo($file->filename, PATHINFO_EXTENSION);
                $file->mime_type = mime_content_type($path . '/' . $file->filename);
                try{
                    $file->exif_data = exif_read_data($path . '/' . $file->filename);
                }catch (\Exception $e){
                    $file->exif_data = [];
                }
            }
            $file->author = $request->get('author');
            $file->viewable = $request->get('visible', 0);
            foreach (config('app.locales', [config('app.fallback_locale', 'en')]) as $locale) {
                $file->{'title_' . $locale} = $request->get('title_' . $locale);
                $file->{'description_' . $locale} = $request->get('description_' . $locale);
            }
            $file->save();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($file, 200);
    }

    public function delete(Request $request)
    {
        $file = Files::find($request->get('id'));
        try {
            unlink($file->full_path);
            Files::find($request->get('id'))->delete();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }


        return response()->json(['message' => 'Deleted', 'type' => 'success'], 200);
    }
}