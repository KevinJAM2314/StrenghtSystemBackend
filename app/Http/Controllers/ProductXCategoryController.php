<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductXCategory;
use Illuminate\Support\Facades\Lang;

class ProductXCategoryController extends Controller
{
    public function store (Request $request)
    {
        try {
            ProductXCategory::create([
                'product_id' => $request->product_id,
                'category_id' => $request->category_id
            ]);
        } catch (\Exception $e) {
            throw new \Exception(json_encode(['title' => Lang::get('messages.alerts.title.warning'), 
            'message' => Lang::get('messages.alerts.message.not_found', ['table' => 'Category'])
            ]));
        }
    }
}
