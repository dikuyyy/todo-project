<?php

namespace App\Http\Controllers;

use App\Models\Checklist;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckListItemController extends Controller
{
    public function index($checklistId) {
        try {
            $checklistsItem = Item::where('checklist_id', $checklistId)->get();
            return response()->json([
                'status' => 'success',
                'data' => $checklistsItem
            ]);
        } catch(\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function store(Request $request, $checklistId): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'itemName' => 'required|string',
        ]);

        try {
            if($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $auth = auth()->user();
            $checklist = Checklist::find($checklistId);
            if($checklist === null) {
                throw new \Exception('Checklist not found');
            }

            DB::beginTransaction();
            $checklistItem = new Item();
            $checklistItem->item_name = $request->get('itemName');
            $checklistItem->is_done = "false";
            $checklistItem->checklist_id = $checklistId;
            $checklistItem->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $checklistItem
            ]);
        }
        catch(\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getItem($checklistId, $itemId): \Illuminate\Http\JsonResponse
    {
        $item = Item::where('checklist_id', $checklistId)
            ->where('id', $itemId)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        return response()->json($item);
    }

    public function updateStatus(Request $request, $checklistId, $itemId): \Illuminate\Http\JsonResponse
    {
        $item = Item::where('checklist_id', $checklistId)
            ->where('id', $itemId)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->update(['is_done' => !$item->is_done]);
        return response()->json(['message'=> 'status updated successfully']);
    }

    public function deleteItem($checklistId, $itemId): \Illuminate\Http\JsonResponse
    {
        $item = Item::where('checklist_id', $checklistId)
            ->where('id', $itemId)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Item deleted']);
    }

    public function renameItem(Request $request, $checklistid, $checklistitemid): \Illuminate\Http\JsonResponse
    {
        $item = Item::where('checklist_id', $checklistid)
            ->where('id', $checklistitemid)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->update(['item_name' => $request->input('itemName')]);
        return response()->json(['message' => 'Item renamed', 'data' => $item]);
    }
}
