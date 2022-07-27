<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;

class TestimoniController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'media'     => 'file',
            'name'      => 'required',
            'desc'      => 'required'
        ]);
    }

    public function get(Request $request)
    {
        $dataTestimoni = Testimonial::orderBy('created_at','desc')
        ->paginate($request->pageSize);
        return response()->json([
            'message' => 'data Testimoni',
            'serve'   => $dataTestimoni
        ], 200);
    }

    public function show($id)
    {
        $dataTestimoni = Testimonial::find($id);
        if (is_null($dataTestimoni)) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'message' => 'data Testimoni',
            'serve'   => $dataTestimoni
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
        $media->storeAs('public/Testimoni', $media->hashName());

        $dataTestimoni = new Testimonial;
        $dataTestimoni->media = $media->hashName();
        $dataTestimoni->name = $request->name;
        $dataTestimoni->desc = $request->desc;
        $dataTestimoni->save();

        if ($dataTestimoni) {
            return response()->json([
                'message' => 'Data baru berhasil ditambahkan',
                'serve'   => $dataTestimoni
            ], 201);
        } else {
            return response()->json([
                'message' => 'Data gagal ditambahkan',
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $dataTestimoni = Testimonial::find($id);
        if (is_null($dataTestimoni)) {
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
            $dataTestimoni->name = $request->name;
            $dataTestimoni->desc = $request->desc;
            $dataTestimoni->update();
        } else {
            Storage::disk('local')->delete('public/Testimoni/'.$dataTestimoni->media);

            $media = $request->file('media');
            $media->storeAs('public/Testimoni', $media->hashName());
    
            $dataTestimoni->media = $media->hashName();
            $dataTestimoni->name = $request->name;
            $dataTestimoni->desc = $request->desc;
            $dataTestimoni->update();
        }

        if ($dataTestimoni) {
            return response()->json([
                'message' => 'Data berhasil diperbaharui',
                'serve'   => $dataTestimoni
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data gagal diperbaharui',
            ], 400);
        }        
    }

    public function destroy($id)
    {
        $dataTestimoni = Testimonial::find($id);
        if (is_null($dataTestimoni)) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        Storage::disk('local')->delete('public/Testimoni/'.$dataTestimoni->media);
        $dataTestimoni->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ], 200);
    }    
}
