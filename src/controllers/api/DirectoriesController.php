<?php
namespace LaraMod\Admin\Files\Controllers\Api;

use App\Http\Controllers\Controller;
use LaraMod\Admin\Files\Models\Directories;
use LaraMod\Admin\Files\Models\Files;
use Illuminate\Http\Request;

class DirectoriesController extends Controller
{

    public function index()
    {
        function get_children($dir){
            $data = [];
            foreach($dir->children as $child){
                $d = $child;
                if($child->children){
                    $d->children = get_children($child);
                }
                $data[] = $d;
            }
            return $data;
        }
        $data = [];
        foreach(Directories::where('directories_id', 0)->get() as $dir){
            $d = $dir;
            if($d->children){
                $d->children = get_children($dir);
            }
            $data[] = $dir;

        }
        return response()->json($data);
    }

    public function getForm(Request $request)
    {
        $directory = ($request->has('id') ? Directories::find($request->get('id')) : new Directories());
        return response()->json($directory);
    }

    public function postForm(Request $request)
    {

        $directory = $request->has('id') ? Directories::find($request->get('id')) : new Directories();
        try {
            if ($request->has('id')) {
                $directory->update($request->only($directory->getFillable()));
            } else {
                $directory = $directory->create($request->only($directory->getFillable()));
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($directory, 200);
    }

    public function delete(Request $request)
    {
        try {
            Directories::find($request->get('id'))->delete();
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }


        return response()->json(['message' => 'Deleted', 'type' => 'success'], 200);
    }

    public function sync()
    {
        $path = public_path('uploads');


        if(!Directories::wherePath('uploads')->count()){
            Directories::create([
                'path' => 'uploads',
                'directories_id' => 0
            ]);
        }
        function parse_directory($path)
        {
            $items = glob($path.'/*');
            foreach ($items as $item) {
                if(is_dir($item)){
                    $parent = Directories::wherePath(basename(dirname($item)))->first();
                    if (!Directories::wherePath(basename($item))->where('directories_id', $parent->id)->count()) {
                        add_directory(realpath($item));
                    }
                    parse_directory(realpath($item));
                }else{
                    $directory = Directories::wherePath(basename(dirname($item)))->first();
                    if (!Files::whereFilename(basename($item))->where('directories_id', $directory->id)->count()) {
                        add_file(realpath($item));
                    }
                }
            }
        }

        function add_directory($path)
        {
            $parent = Directories::wherePath(basename(dirname($path)))->first();
            Directories::create([
                'path' => basename($path),
                'directories_id' => $parent ? $parent->id : 0
            ]);

        }

        function add_file($path)
        {
            $directory = Directories::wherePath(basename(dirname($path)))->first();
            try {
                $exif_data = \exif_read_data($path);
            }catch (\Exception $e){
                $exif_data = null;
            }
            Files::create([
                'filename' => basename($path),
                'directories_id' => $directory->id,
                'extension' => pathinfo($path, PATHINFO_EXTENSION),
                'mime_type' => mime_content_type($path),
                'exif_data' => $exif_data,
                'visible' => true,
            ]);
        }

        try {
            parse_directory($path);
        }catch(\Exception $e){
            return response()->json([
                'type' => 'danger',
                'message' => $e->getMessage()
            ], 500);
        }
        return response()->json([
            'type' => 'success',
            'message' => 'Folders synced'
        ], 200);
    }


}