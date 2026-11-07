<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Http\Requests\ContactRequest;

class ContactController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('contact.index', compact('categories', 'tags'));
    }
    //フォーム入力
    public function confirm(ContactRequest $request)
    {

        $validated = $request->validated();


        $category = Category::find($validated['category_id']);

        $tags = collect();
        if (!empty($validated['tag_ids'])) {
            $tags = Tag::whereIn('id', $validated['tag_ids'])->get();
        }
        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }
    public function store(ContactRequest $request)
    {
        $validated = $request->validated();
        if (isset($validated['tel'])) {
            $validated['telephone'] = $validated['tel'];
            unset($validated['tel']);
        }

        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        $contact = Contact::create($validated);

        if (!empty($tagIds)) {
            $contact->tags()->attach($tagIds);
        }

        return redirect()->route('contact.thanks');
    }
}
