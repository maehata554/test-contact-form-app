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
        $contactData = $request->validated();
        $category = Category::find($contactData['category_id']);
        $contactData['category_name'] = $category ? $category->name : '';

        return view('contact.confirm', compact('contactData'));
    }
    public function store(ContactRequest $request)
    {
        $contact = Contact::create($request->validated());
        if ($request->has('tags')) {
            $contact->tags()->attach($request->input('tags'));
        }
        return redirect()->route('contact.thanks');
    }
}
