<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Activity;
use App\Models\DocumentActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
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
            $start = $request->query('start_date');
            $end = $request->query('end_date');
            $type = $request->query('type');
            $dataActivity = Activity::when($name, function($query) use ($name){
                return $query->where('name', 'like', '%'.$name.'%');
            })->when($start && $end, function ($query) use ($start, $end) {
                return $query->whereRaw('(created_at >= ? AND created_at <= ?)', [$start." 00:00:00", $end." 23:59:59"]);
            })->when($type, function ($query) use ($type) {
                return $query->where('type', $type);
            })->orderBy("created_at", "desc")
            ->paginate($request->pageSize);
            return response()->json([
                'message' => '',
                'serve' => $dataActivity,
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

            $dataActivity = new Activity;
            $dataActivity->name = $request->name;
            $dataActivity->desc = $request->desc;
            $dataActivity->media = $request->media;
            $dataActivity->type = $request->type;
            $dataActivity->meta_title = $request->meta_title;
            $dataActivity->meta_description = $request->meta_description;
            $dataActivityTitle = Activity::where('path', strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->name))))->get();
            if (count($dataActivityTitle) > 0) {
                $dataActivity->path = strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->name)))."-".(count($dataActivityTitle)+1);
            } else {
                $dataActivity->path = strtolower(preg_replace('/\s+/', '-', preg_replace('/[^a-zA-Z0-9_ -]/s','',$request->name)));
            }
            $dataActivity->save();

            if (count($request->docs) > 0) {
                foreach ($request->docs as $doc) {
                    $dataDocActivity = new DocumentActivity;
                    $dataDocActivity->activity_id = $dataActivity->id;
                    $dataDocActivity->media = $doc['url'];
                    $dataDocActivity->save();
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
            $dataActivity = Activity::when($path, function($query) use ($path){
                                    return $query->where("path", $path);
                                })->when($id, function($query) use ($id){
                                    return $query->where('id', $id);
                                })->first();
            if (!$dataActivity) {
                return response()->json([
                    'message' => 'Data tidak diketahui.',
                    'serve' => []
                ], 400);
            }

            return response()->json([
                'message' => '',
                'serve' => $dataActivity,
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

            $dataActivity = Activity::where('id', $request->id)->first();
            if (!$dataActivity) {
                DB::commit();
                return response()->json([
                    'message' => "Gagal mendapatkan data.",
                    'serve' => []
                ], 400);
            }

            $dataActivity->name = $request->name;
            $dataActivity->desc = $request->desc;
            $dataActivity->media = $request->media;
            $dataActivity->type = $request->type;
            $dataActivity->save();
            
            $dataDocActivity = DocumentActivity::where("activity_id", $dataActivity->id)->get();
            if (count($dataDocActivity) > 0) {
                DocumentActivity::where("activity_id", $dataActivity->id)->delete();
            }

            foreach ($request->docs as $doc) {
                $dataDocActivity = new DocumentActivity;
                $dataDocActivity->activity_id = $dataActivity->id;
                $dataDocActivity->media = $doc['url'];
                $dataDocActivity->save();
            }
            
            DB::commit();
            return response()->json([
                'message' => 'Data berhasil diubah.',
                'serve' => $dataActivity,
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
            $dataActivity = Activity::where('id', $request->id)->first();
            if (!$dataActivity) {
                DB::commit();
                return response()->json([
                    'message' => "Gagal mendapatkan data.",
                    'serve' => []
                ], 400);
            }

            $dataDocActivity = DocumentActivity::where("activity_id", $request->id)->get();
            if (count ($dataDocActivity) > 0) {
                foreach ($dataDocActivity as $doc) {
                    $deletedPhoto = explode("/", $doc['media']);
                    Storage::delete('activity/' . $deletedPhoto[5]);
                }
            }

            $dataActivity->delete();
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

    public function uploadDoc(Request $request)
    {
        if (request()->hasFile('media')) {
            $filePath = $request->file('media')->store('activity');
        }
        $imageUrl = isset($filePath) ? env('APP_URL') . '/storage/' . $filePath : null;

        return response()->json([
            'imageUrl' => $imageUrl
        ], 200);
    }
}
