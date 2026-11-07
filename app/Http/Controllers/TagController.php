<?php

namespace App\Http\Controllers;
use App\Models\Tag;
use App\Http\Requests\TagRequest;
class TagController extends Controller
{
    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function store(TagRequest $request)
    {
        $tag = Tag::create($request->validated());
        return redirect()->route('admin.index');
    }

    public function update(TagRequest $request, Tag $tag)
    {
        $this->authorize('manaTags', $tag);
        $tag->update($request->validated());
        return redirect()->route('tags.update');
    }
    public function destroy(Tag $tag)
    {
        $this->authorize('manaTags', $tag);
        $tag->delete();
        return redirect()->route('admin.index');
    }
}