<?php

namespace App\Http\Controllers;

use App\Http\Resources\Image as ImageResource;
use App\Http\Resources\ImageCollection;
use App\Models\Image;

class ImageController extends Controller
{
    public function index()
    {
        return new ImageCollection(request()->user()->images);
    }

    public function show(Image $image)
    {
        $this->authorize('touch', $image);

        return new ImageResource($image);
    }

    public function store()
    {
        $data = request()->validate([
            'image' => 'required',
            'body' => 'required',
        ]);

        $image = $data['image']->store('images', 'public');

        $image = request()->user()->images()->create([
            'body' => $data['body'],
            'image' => $image
        ]);

        return new ImageResource($image);
    }

    public function update(Image $image)
    {
        $this->authorize('touch', $image);

        $data = request()->validate([
            'body' => '',
            'image' => 'required'
        ]);

        $image->update([
            'body' => $data['body'],
            'image' => $data['image']
        ]);

        return new ImageResource($image->fresh());
    }

    public function destroy(Image $image)
    {
        $this->authorize('touch', $image);

        $image->delete();

        return response([], 200);
    }
}
