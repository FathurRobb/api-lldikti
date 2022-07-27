<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Gallery;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'category_id' => 'required',
            'title'       => 'required',
            'media'       => 'file',
        ]);
    }

    public function get(Request $request)
    {
        $dataGallery = Gallery::orderBy('created_at','desc')
        ->paginate($request->pageSize);
        return response()->json([
            'message' => 'data Galeri',
            'serve'   => $dataGallery
        ], 200);
    }

    public function show($id)
    {
        $dataGallery = Gallery::find($id);
        if (is_null($dataGallery)) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'message' => 'data Galeri',
            'serve'   => $dataGallery
        ], 200);
    }

    public function store(Request $request)
    {
        $validate = $this->validator($request->all());
        if ($validate->fails()) {
            return response()->json([
                'message'   => 'Semua kolom wajib diisi',
                'serve'      => $validate->errors()
            ], 400);
        }

        $media = $request->file('media');
        $media->storeAs('public/Galeri', $media->hashName());

        $dataGallery = new Gallery;
        $dataGallery->category_id = $request->category_id;
        $dataGallery->title = $request->title;
        $dataGallery->media = $media->hashName();
        $dataGallery->type = $request->type;
        $dataGallery->save();

        if ($dataGallery) {
            return response()->json([
                'message' => 'Data baru berhasil ditambahkan',
                'serve'   => $dataGallery
            ], 201);
        } else {
            return response()->json([
                'message' => 'Data gagal ditambahkan',
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $dataGallery = Gallery::find($id);
        if (is_null($dataGallery)) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $validate = $this->validator($request->all());
        if ($validate->fails()) {
            return response()->json([
                'message'   => 'Semua kolom wajib diisi',
                'serve'      => $validate->errors()
            ], 400);
        }

        if ($request->file('media') == "") {
            $dataGallery->category_id = $request->category_id;
            $dataGallery->title = $request->title;
            $dataGallery->update();
        } else {
            Storage::disk('local')->delete('public/Galeri/'.$dataGallery->media);

            $media = $request->file('media');
            $media->storeAs('public/Galeri', $media->hashName());
            
            $dataGallery->category_id = $request->category_id;
            $dataGallery->title = $request->title;
            $dataGallery->media = $media->hashName();
            $dataGallery->type = $request->type;
            $dataGallery->update();
        }

        if ($dataGallery) {
            return response()->json([
                'message' => 'Data berhasil diperbaharui',
                'serve'   => $dataGallery
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data gagal diperbaharui',
            ], 400);
        }        
    }

    public function destroy($id)
    {
        $dataGallery = Gallery::find($id);
        if (is_null($dataGallery)) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        Storage::disk('local')->delete('public/Galeri/'.$dataGallery->media);
        $dataGallery->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ], 200);
    }
}
