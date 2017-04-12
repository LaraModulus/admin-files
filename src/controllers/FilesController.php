<?php
namespace LaraMod\AdminFiles\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use LaraMod\AdminFiles\Models\Files;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class FilesController extends Controller
{

    private $data = [];
    public function __construct()
    {
        config()->set('admincore.menu.files.active', true);
    }

    public function index()
    {
        return view('adminfiles::list', $this->data);
    }

    public function getForm(Request $request)
    {
        $this->data['file'] = ($request->has('id') ? Files::find($request->get('id')) : new Files());
        return view('adminblog::categories.form', $this->data);
    }

    public function postForm(Request $request)
    {

        $file = $request->has('id') ? Files::find($request->get('id')) : new Files();
        try{
            foreach(config('app.locales', [config('app.fallback_locale', 'en')]) as $locale){
                $file->{'title_'.$locale} = $request->get('title_'.$locale);
            }
            $file->viewable = $request->get('visible', 0);
            $file->save();
        }catch (\Exception $e){
            return redirect()->back()->withInput()->withErrors(['errors' => $e->getMessage()]);
        }

        return redirect()->route('admin.files')->with('message', [
            'type' => 'success',
            'text' => 'File saved.'
        ]);
    }

    public function delete(Request $request){
        if(!$request->has('id')){
            return redirect()->route('admin.files')->with('message', [
                'type' => 'danger',
                'text' => 'No ID provided!'
            ]);
        }
        try {
            Files::find($request->get('id'))->delete();
        }catch (\Exception $e){
            return redirect()->route('admin.files')->with('message', [
                'type' => 'danger',
                'text' => $e->getMessage()
            ]);
        }

        return redirect()->route('admin.files')->with('message', [
            'type' => 'success',
            'text' => 'File moved to trash.'
        ]);
    }

    public function downloadFile(Request $request){
        $file = Files::find($request->get('file'));
        if(!$file) abort(404, 'File not found');
        return response()->download($file->fullPath, $file->filename);
    }


}