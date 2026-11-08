<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vi\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreRequest;
use App\Http\Requests\Api\V1\UpdateRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //一覧取得
    public function index(IndexContactRequest $request)
    {
        $query = Contact::with(['category', 'tags']);

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        //性別
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }
        //カテゴリ
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        //日付
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }
        //ページ
        $perPage = $request->input('per_page', 20);
        $contacts = $query->latest()->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        if (isset($data['tel'])) {
            $data['telephone'] = $data['tel'];
            unset($data['tel']);
        }

        $contact = Contact::create($data);

        if ($request->has('tag_ids')) {
            $contact->tags()->sync($request->input('tag_ids'));//
        }

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    //詳細取得
    public function show(string $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    //更新
    public function update(UpdateRequest $request, $id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json([
                'error' => 'お問い合わせが見つかりませんでした'
            ], 404);//
    }

        $data = $request->validated();
        if (isset($data['tel'])) {
            $data['telephone'] = $data['tel'];
        }

        $contact->update($data);

        if ($request->has('tag_ids')) {
            $contact->tags()->sync($request->input('tag_ids'));
        } else {
            $contact->tags()->sync([]);
        }

        $contact->load(['category', 'tags']);
        return new ContactResource($contact);
    }


    public function destroy($id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json([
                'error' => 'お問い合わせが見つかりませんでした'
            ], 404);//
    }
        $contact->tags()->detach();
        $contact->delete();

        return response()->noContent();
    }
}