<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\News;
use App\Models\NewsMultipleCategories;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'title' => 'required',
        ]);
    }

    public function get(Request $request)
    {
        try {
            $name = $request->query('name');
            $category = $request->query('category');
            $status = $request->query('status');
            $dataNews = News::when($name, function($query) use ($name){
                return $query->where('name', 'like', '%'.$name.'%');
            })->when($status, function($query) use ($status){
                return $query->where('status', $status);
            })->when($category, function($query) use ($category){
                return $query->where('news_multiple_categories.category_id',$category)
                ->join('news_multiple_categories','news_multiple_categories.news_id','=','news.id');
            })->orderBy("created_at", "desc")
            ->paginate($request->pageSize);
            return response()->json([
                'message' => '',
                'serve' => $dataNews,
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

            $dataNews = new News;
            $dataNews->title = $request->title;
            $dataNews->thumbnail = $request->thumbnail;
            $dataNews->desc = $request->desc;
            $dataNews->status = $request->status;
            $dataNews->meta_title = $request->meta_title;
            $dataNews->meta_description = $request->meta_description;
            $dataNewsTitle = News::where('path', strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->title))))->get();
            if (count($dataNewsTitle) > 0) {
                $dataNews->path = strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->title)))."-".(count($dataNewsTitle)+1);
            } else {
                $dataNews->path = strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->title)));
            }
            $dataNews->save();

            if (count($request->categories) > 0) {
                foreach ($request->categories as $cat) {
                    if($cat != ''){
                        $dataMultipleCategory = new NewsMultipleCategories;
                        $dataMultipleCategory->news_id = $dataNews->id;
                        $dataMultipleCategory->category_id = $cat['id'];
                        $dataMultipleCategory->save();
                    }
                }
            }

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
            $path = $request->path;
            $id = $request->id;
            $dataNews = News::when($path, function($query) use ($path){
                                    return $query->where("path", $path);
                                })->when($id, function($query) use ($id){
                                    return $query->where('id', $id);
                                })->first();
            if (!$dataNews) {
                return response()->json([
                    'message' => 'Data tidak diketahui.',
                    'serve' => []
                ], 400);
            }

            return response()->json([
                'message' => '',
                'serve' => $dataNews,
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

            $dataNews = News::where('id', $request->id)->first();
            if (!$dataNews) {
                DB::commit();
                return response()->json([
                    'message' => "Gagal mendapatkan data.",
                    'serve' => []
                ], 400);
            }

            $dataNews->title = $request->title;
            $dataNews->thumbnail = $request->thumbnail;
            $dataNews->desc = $request->desc;
            $dataNews->status = $request->status;
            $dataNews->meta_title = $request->meta_title;
            $dataNews->meta_description = $request->meta_description;
            if ($dataNews->title !== $request->title) {
                $dataNewsTitle = News::where('path', strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->title))))->get();
                if (count($dataNewsTitle) > 0) {
                    $dataNews->news_route = strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->title)))."-".(count($dataNewsTitle)+1);
                } else {
                    $dataNews->news_route = strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->title)));
                }
            }
            $dataNews->save();
            
            if(count($request->categories) > 0){
                $dataMultipleCategory = NewsMultipleCategories::where('news_id', $request->id)->get();
                if($dataMultipleCategory->count() > 0){
                    NewsMultipleCategories::where('news_id',$request->id)->delete();
                }
                foreach ($request->categories as $cat) {
                    if($cat != ''){
                        $dataMultipleCategory = new NewsMultipleCategories;
                        $dataMultipleCategory->news_id = $dataNews->news_id;
                        $dataMultipleCategory->category_id = $cat['id'];
                        $dataMultipleCategory->save();
                    }
                }
            }
            DB::commit();
            return response()->json([
                'message' => 'Data berhasil diubah.',
                'serve' => $dataNews,
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
            $dataNews = News::where('id', $request->id)->first();
            if (!$dataNews) {
                DB::commit();
                return response()->json([
                    'message' => "Gagal mendapatkan data.",
                    'serve' => []
                ], 400);
            }

            $deletedPhoto = explode("/", $dataNews->thumbnail);
            Storage::delete('news/' . $deletedPhoto[5]);

            $dataNews->delete();
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

    public function uploadThumbnail(Request $request)
    {
        if (request()->hasFile('media')) {
            $filePath = $request->file('media')->store('news');
        }
        $imageUrl = isset($filePath) ? env('APP_URL') . '/storage/' . $filePath : null;

        return response()->json([
            'imageUrl' => $imageUrl
        ], 200);
    }
}
