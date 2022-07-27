<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\NewsCategory;
use Illuminate\Support\Facades\DB;

class NewsCategoryController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string',
        ]);
    }

    public function get(Request $request)
    {
        try {
            $name = $request->query('name');
            $dataNewsCategory = NewsCategory::when($name, function($query) use ($name){
                return $query->where('name', 'like', '%'.$name.'%');
            })->orderBy("created_at", "desc")
            ->paginate($request->pageSize);
            return response()->json([
                'message' => '',
                'serve' => $dataNewsCategory,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'serve' => [],
            ], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validate = $this->validator($request->all());
            if ($validate->fails()) {
                DB::commit();
                return response()->json([
                    'message' => $validate->errors()->first(),
                    'serve' => []
                ], 400);
            }

            $dataNewsCategory = new NewsCategory;
            $dataNewsCategory->name = $request->name;
            $dataNewsCategory->save();

            DB::commit();
            return response()->json([
                'message' => 'Data baru berhasil ditambahkan.',
                'serve' => [],
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'serve' => [],
            ], 500);
        }
    }

    public function retrieve(Request $request)
    {
        try {
            $dataNewsCategory = NewsCategory::where("id", $request->id)->first();
            if (!$dataNewsCategory) {
                return response()->json([
                    'message' => 'Data tidak diketahui.',
                    'serve' => []
                ], 400);
            }

            return response()->json([
                'message' => '',
                'serve' => $dataNewsCategory,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'serve' => [],
            ], 500);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $validate = $this->validator($request->all());
            if ($validate->fails()) {
                DB::commit();
                return response()->json([
                    'message' => $validate->errors()->first(),
                    'serve' => []
                ], 400);
            }

            $dataNewsCategory = NewsCategory::where('id', $request->id)->first();
            if (!$dataNewsCategory) {
                DB::commit();
                return response()->json([
                    'message' => "Gagal mendapatkan data.",
                    'serve' => []
                ], 400);
            }

            $dataNewsCategory->name = $request->name;
            $dataNewsCategory->save();
            DB::commit();
            return response()->json([
                'message' => 'Data berhasil diubah.',
                'serve' => $dataNewsCategory,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'serve' => [],
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $dataNewsCategory = NewsCategory::where('id', $request->id)->first();
            if (!$dataNewsCategory) {
                DB::commit();
                return response()->json([
                    'message' => "Gagal mendapatkan data.",
                    'serve' => []
                ], 400);
            }

            $dataNewsCategory->delete();
            DB::commit();
            return response()->json([
                'message' => 'Data berhasil dihapus.',
                'serve' => [],
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
                'serve' => [],
            ], 500);
        }
    }
}
