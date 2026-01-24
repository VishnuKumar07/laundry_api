<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::select('id', 'name', 'status')->where('status', 'active')->get();
            if ($categories->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No categories found',
                    'data'    => []
                ], 404);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Categories fetched successfully',
                'data'    => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Internal Server Error',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
