<?php

namespace LaraMod\Admin\Files\Controllers;

use App\Http\Controllers\Controller;
use LaraMod\Admin\Files\Models\Files;
use Illuminate\Http\Request;

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

        $file = Files::firstOrNew(['id' => $request->get('id')]);
        try {
            $file->autoFill($request);

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['errors' => $e->getMessage()]);
        }

        return redirect()->route('admin.files')->with('message', [
            'type' => 'success',
            'text' => 'File saved.',
        ]);
    }

    public function delete(Request $request)
    {
        if (!$request->has('id')) {
            return redirect()->route('admin.files')->with('message', [
                'type' => 'danger',
                'text' => 'No ID provided!',
            ]);
        }
        try {
            Files::find($request->get('id'))->delete();
        } catch (\Exception $e) {
            return redirect()->route('admin.files')->with('message', [
                'type' => 'danger',
                'text' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.files')->with('message', [
            'type' => 'success',
            'text' => 'File moved to trash.',
        ]);
    }

    public function downloadFile(Request $request)
    {
        $file = Files::find($request->get('file'));
        if (!$file) {
            abort(404, 'File not found');
        }

        return response()->download($file->fullPath, $file->filename);
    }


}