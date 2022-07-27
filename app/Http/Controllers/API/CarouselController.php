<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Carousel;
use Illuminate\Support\Facades\Storage;

class CarouselController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'title'         => 'required',
            'media'         => 'file',
            'desc'          => 'required',
            'have_button'   => 'required',
        ]);
    }
 
    public function get(Request $request)
    {
        $dataCarousel = Carousel::orderBy('created_at','desc')
        ->paginate($request->pageSize);
        return response()->json([
            'message' => 'data Carousel',
            'serve'   => $dataCarousel
        ], 200);
    }

    public function show($id)
    {
        $dataCarousel = Carousel::find($id);
        if (is_null($dataCarousel)) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'message' => 'data Carousel',
            'serve'   => $dataCarousel
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
        $media->storeAs('public/Carousel', $media->hashName());

        $dataCarousel = new Carousel;
        $dataCarousel->title = $request->title;
        $dataCarousel->media = $media->hashName();
        $dataCarousel->desc = $request->desc;
        $dataCarousel->have_button = $request->have_button;
        $dataCarousel->button_action = $request->button_action;
        $dataCarousel->save();

        if ($dataCarousel) {
            return response()->json([
                'message' => 'Data baru berhasil ditambahkan',
                'serve'   => $dataCarousel
            ], 201);
        } else {
            return response()->json([
                'message' => 'Data gagal ditambahkan',
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $dataCarousel = Carousel::find($id);
        if (is_null($dataCarousel)) {
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
            $dataCarousel->title = $request->title;
            $dataCarousel->desc = $request->desc;
            $dataCarousel->have_button = $request->have_button;
            $dataCarousel->button_action = $request->button_action;
            $dataCarousel->update();
        } else {
            Storage::disk('local')->delete('public/Carousel/'.$dataCarousel->media);

            $media = $request->file('media');
            $media->storeAs('public/Carousel', $media->hashName());
            
            $dataCarousel->title = $request->title;
            $dataCarousel->media = $media->hashName();
            $dataCarousel->desc = $request->desc;
            $dataCarousel->have_button = $request->have_button;
            $dataCarousel->button_action = $request->button_action;
            $dataCarousel->update();
        }

        if ($dataCarousel) {
            return response()->json([
                'message' => 'Data berhasil diperbaharui',
                'serve'   => $dataCarousel
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data gagal diperbaharui',
            ], 400);
        }
    }

    public function destroy($id)
    {
        $dataCarousel = Carousel::find($id);
        if (is_null($dataCarousel)) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        Storage::disk('local')->delete('public/Carousel/'.$dataCarousel->media);
        $dataCarousel->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ], 200);
    }
}
