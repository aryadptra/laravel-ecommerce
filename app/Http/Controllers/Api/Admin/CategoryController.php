<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Display all data for the category
        $category = Category::all();

        // Count all categories
        $total = Category::count();

        if ($total == 0) {
            return response()->json([
                'status' => true,
                'message' => "DATA EMPTY"
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => "GET DATA SUCCESSFULLY",
            'data' => $category
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make([
            'name' => 'required',
            'icon'    => 'required|image|mimes:jpeg,jpg,png|max:2000',
        ], [
            'name.required' => 'NAME REQUIRED',
            'icon.required' => 'ICON REQUIRED',
            'icon.image' => 'ICON MUST BE AN IMAGE'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $icon = $request->file('icon');
        $icon->storeAs('public/categories', $icon->hashName());

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
            'icon' => $icon->hashName(),
        ]);

        if (!$category) {
            //return success with Api Resource
            return new ResponseResource(false, 'CREATE CATEGORY FAILED', null);
        }

        return new ResponseResource(true, 'CREATE CATEGORY SUCCESS', $category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|unique:categories,name,' . $category->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //check image update
        if ($request->file('image')) {

            //remove old image
            Storage::disk('local')->delete('public/categories/' . basename($category->image));

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/categories', $image->hashName());

            //update category with new image
            $category->update([
                'image' => $image->hashName(),
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-'),
            ]);
        }

        //update category without image
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name, '-'),
        ]);

        if ($category) {
            //return success with Api Resource
            return new CategoryResource(true, 'Data Category Berhasil Diupdate!', $category);
        }

        //return failed with Api Resource
        return new CategoryResource(false, 'Data Category Gagal Diupdate!', null);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        //remove image
        Storage::disk('local')->delete('public/categories/' . basename($category->image));

        if ($category->delete()) {
            //return success with Api Resource
            return new CategoryResource(true, 'Data Category Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new CategoryResource(false, 'Data Category Gagal Dihapus!', null);
    }
}
