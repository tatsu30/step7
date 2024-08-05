<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    public function purchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($productId);

            if ($product->stock < $quantity) {
                return response()->json(['error' => '在庫が不足しています'], 400);
            }

            $product->stock -= $quantity;
            $product->save();

            Sale::create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);

            DB::commit();

            return response()->json(['message' => '購入が完了しました'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => '購入に失敗しました'], 500);
        }
    }
}
