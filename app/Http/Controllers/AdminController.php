<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    //一覧
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

    //詳細取得
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

    //CSV
    public function export(Request $request): StreamedResponse
    {
        $query = Contact::with(['category', 'tags'])->orderBy('created_at', 'desc')->orderBy('id', 'desc');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $contacts = $query->get();

        $filename = 'contacts_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($contacts) {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'ID',
                '氏名',
                '性別',
                'メール',
                '電話',
                '住所',
                '建物',
                'カテゴリ',
                '内容',
                '作成日時',
            ]);

            foreach ($contacts as $contact) {
                $genderText = match ((int)$contact->gender) {
                    1 => '男性',
                    2 => '女性',
                    3 => 'その他',
                    default => '未設定',
                };

                $fullName = $contact->last_name . ' ' . $contact->first_name;

                $categoryName = $contact->category?->content ?? $contact->category?->name ?? '';

                fputcsv($output, [
                    $contact->id,
                    $fullName,
                    $genderText,
                    $contact->email,
                    $contact->telephone ?? $contact->tel,
                    $contact->address,
                    $contact->building,
                    $categoryName,
                    $contact->detail,
                    $contact->created_at,
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}