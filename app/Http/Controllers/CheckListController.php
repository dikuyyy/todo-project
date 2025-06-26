<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckListController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            $checklists = CheckList::where('user_id', $user->id)->get();
            return response()->json([
                'status' => 'success',
                'data' => $checklists
            ]);
        } catch(\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
        ]);

        try {
            if($validator->fails()) {
                throw new \Exception($validator->errors()->first(), 400);
            }

            DB::beginTransaction();
            $auth = Auth::user();
            $checklist = new CheckList();
            $checklist->user_id = $auth->id;
            $checklist->name = $request->input('name');
            $checklist->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Checklist created successfully',
                'data' => $checklist
            ], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }

    public function destroy($checklistId): \Illuminate\Http\JsonResponse
    {
        $checklist = Checklist::find($checklistId);

        if (!$checklist) {
            return response()->json(['message' => 'Checklist not found'], 404);
        }

        $checklist->delete();
        return response()->json(['message' => 'Checklist deleted']);
    }
}
