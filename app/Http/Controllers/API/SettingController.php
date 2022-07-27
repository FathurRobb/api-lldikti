<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'      => 'required',
            'logo'      => 'image|mimes:png,jpg,jpeg',
            'address'   => 'required',
            'hp'        => 'required',
            'phone'     => 'required',
            'fax'       => 'required',
            'email'     => 'required|email',
            'lat'       => 'required',
            'long'      => 'required',
            'facebook'  => 'required',
            'instagram' => 'required',
            'youtube'   => 'required',
            'twitter'   => 'required',
        ]);
    }

    public function get(Request $request)
    {
        $dataSetting = Setting::orderBy('created_at','desc')
        ->paginate($request->pageSize);
        return response()->json([
            'message' => 'data Setting',
            'serve'   => $dataSetting,
        ], 200);   
    }

    public function show($id)
    {
        $dataSetting = Setting::find($id);
        if (is_null($dataSetting)) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        return response()->json([
            'message' => 'data Setting',
            'serve'   => $dataSetting,
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

        $logo = $request->file('logo');
        $logo->storeAs('public/Setting', $logo->hashName());

        $dataSetting = new Setting;
        $dataSetting->name = $request->name;
        $dataSetting->logo = $logo->hashName();
        $dataSetting->address = $request->address;
        $dataSetting->hp = $request->hp;
        $dataSetting->phone = $request->phone;
        $dataSetting->fax = $request->fax;
        $dataSetting->email = $request->email; 
        $dataSetting->lat = $request->lat;
        $dataSetting->long = $request->long;
        $dataSetting->facebook = $request->facebook;
        $dataSetting->instagram = $request->instagram;
        $dataSetting->youtube = $request->youtube;
        $dataSetting->twitter =  $request->twitter;
        $dataSetting->save();
        
        if ($dataSetting) {
            return response()->json([
                'message' => 'Data baru berhasil ditambahkan',
                'serve'   => $dataSetting
            ], 201);
        } else {
            return response()->json([
                'message' => 'Data gagal ditambahkan',
            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $dataSetting = Setting::find($id);
        if (is_null($dataSetting)) {
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

        if ($request->file('logo') == "") {
            $dataSetting->name = $request->name;
            $dataSetting->address = $request->address;
            $dataSetting->hp = $request->hp;
            $dataSetting->phone = $request->phone;
            $dataSetting->fax = $request->fax;
            $dataSetting->email = $request->email; 
            $dataSetting->lat = $request->lat;
            $dataSetting->long = $request->long;
            $dataSetting->facebook = $request->facebook;
            $dataSetting->instagram = $request->instagram;
            $dataSetting->youtube = $request->youtube;
            $dataSetting->twitter =  $request->twitter;
            $dataSetting->update();
        } else {
            Storage::disk('local')->delete('public/Setting/'.$dataSetting->logo);

            $logo = $request->file('logo');
            $logo->storeAs('public/Setting', $logo->hashName());
    
            $dataSetting->name = $request->name;
            $dataSetting->logo = $logo->hashName();
            $dataSetting->address = $request->address;
            $dataSetting->hp = $request->hp;
            $dataSetting->phone = $request->phone;
            $dataSetting->fax = $request->fax;
            $dataSetting->email = $request->email; 
            $dataSetting->lat = $request->lat;
            $dataSetting->long = $request->long;
            $dataSetting->facebook = $request->facebook;
            $dataSetting->instagram = $request->instagram;
            $dataSetting->youtube = $request->youtube;
            $dataSetting->twitter =  $request->twitter;
            $dataSetting->update();
        }

        if ($dataSetting) {
            return response()->json([
                'message' => 'Data berhasil diperbaharui',
                'serve'   => $dataSetting
            ], 200);
        } else {
            return response()->json([
                'message' => 'Data gagal diperbaharui',
            ], 400);
        }
    }

    public function destroy($id)
    {
        $dataSetting = Setting::find($id);
        if (is_null($dataSetting)) {
            return response()->json([
                'message' => 'Data tidak ditemukan',
            ], 404);
        }

        Storage::disk('local')->delete('public/Setting/'.$dataSetting->logo);
        $dataSetting->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus'
        ], 200);
    }
}
