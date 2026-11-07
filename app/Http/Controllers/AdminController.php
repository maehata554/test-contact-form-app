<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Contact::class);
        $query = Contact::query()->with('category', 'tags');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(first_name, last_name) LIKE ?", ["%{$keyword}%"]);
            });
        }

        if ($request->filled('gender') && $request->input('gender') !== '0' && $request->input('gender') !== '') {
            $gender = $request->input('gender');
            $query->where('gender', (int) $gender);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }


        $contacts = $query->latest()->paginate(7)->appends($request->except('page'));
        $tags = Tag::all();


        $categories = Category::all();


        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    public function show(Contact $contact)
    {
        $this->authorize('view', $contact);
        $contact->load('category', 'tags');
        return view('admin.show', compact('contact'));
    }
    public function destroy(Contact $contact)
    {
        $this->authorize('delete', $contact);
        $contact->delete();
        return redirect()->route('admin.index');
    }
    }

