<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
    	return redirect()->route('admin.auth.login');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust validation rules as needed
        ]);

        // Handle the file upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = $this->uploadFile($image, 'editor');
            // Return the URL of the uploaded image
            $imageUrl = asset(uploadsDir('front').$name);
            // You can also save the image's URL to a database if needed
            return response()->json(['location' => $imageUrl]);
        }

        return response()->json(['error' => 'Image upload failed'], 400);
    }
}
