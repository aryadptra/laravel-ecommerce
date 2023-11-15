<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResponseResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                'message' => "Empty data for category."
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => "Successfully get data category.",
            'data' => $category
        ], 200);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Display all data for the category
        $category = Category::where('id', $id)->first();

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => "Category not found.",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Successfully get data category.",
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
            'name.required' => 'Name is required.',
            'icon.required' => 'Icon is required.',
            'icon.image' => 'Icon must be a valid image.',
            'icon.max' => 'Maximal size of image must be at least 2MB.'
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
            return new ResponseResource(false, 'Failed to create category', null);
        }

        return new ResponseResource(true, 'Create category successfully.', $category);
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
        if ($request->file('icon')) {

            //remove old image
            Storage::disk('local')->delete('public/categories/' . basename($category->icon));

            //upload new icon
            $icon = $request->file('icon');
            $icon->storeAs('public/categories', $icon->hashName());

            //update category with new icon
            $category->update([
                'icon' => $icon->hashName(),
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
            return new ResponseResource(true, 'Successfully update category.', $category);
        }

        //return failed with Api Resource
        return new ResponseResource(false, 'Failed to update category.', null);
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
        Storage::disk('local')->delete('public/categories/' . basename($category->icon));

        if ($category->delete()) {
            //return success with Api Resource
            return new ResponseResource(true, 'Data Category Berhasil Dihapus!', null);
        }

        //return failed with Api Resource
        return new ResponseResource(false, 'Data Category Gagal Dihapus!', null);
    }
}
