<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\GalleryCategory;

class GalleryCategoryController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required',
        ]);
    }

    public function get(Request $request)
    {
        $dataGC = GalleryCategory::orderBy('created_at','desc')
        ->paginate($request->pageSize);
        return response()->json([
            'message' => 'data Kategori Galeri',
            'serve'   => $dataGC
        ], 200);
    }

    public function show($id)
    {
        $dataGC = GalleryCategory::find($id);
        if (is_null($dataGC)) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'message' => 'data Kategori Galery',
            'serve'   => $dataGC
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
     
        $dataGC = new GalleryCategory;
        $dataGC->name = $request->name;
        $dataGC->save();

        if ($dataGC) {
            return response()->json([
                'message' => 'Data baru berhasil ditambahkan',
                'serve'   => $dataGC
            ], 201);
        } else {
            return response()->json([
                'message' => 'Data gagal ditambahkan',
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $dataGC = GalleryCategory::find($id);
        if (is_null($dataGC)) {
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

        $dataGC->name = $request->name;
        $dataGC->update();

        if ($dataGC) {
            return response()->json([
                'message' => 'Data berhasil diperbaharui',
                'serve'   => $dataGC
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data gagal diperbaharui',
            ], 400);
        }        
    }

    public function destroy($id)
    {
        $dataGC = GalleryCategory::find($id);
        if (is_null($dataGC)) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        $dataGC->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ], 200);
    }
}
