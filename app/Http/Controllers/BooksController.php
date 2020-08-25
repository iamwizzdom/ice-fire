<?php

namespace App\Http\Controllers;

use App\Book;
use App\Http\Traits\Fetcher;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BooksController extends Controller
{
    use Fetcher;

    public function external_books(Request $request)
    {
        $query = ($request->name ? ("?" . http_build_query(['name' => $request->name])) : '');
        $books = $this->fetchBooks("https://www.anapioficeandfire.com/api/books/{$query}");

        $data = [];

        if ($books['status'] && is_array($books['response'])) {

            foreach ($books['response'] as $book) {
                $data[] = [
                    'name' => $book['name'],
                    'isbn' => $book['isbn'],
                    'authors' => $book['authors'],
                    'number_of_pages' => $book['numberOfPages'],
                    'publisher' => $book['publisher'],
                    'country' => $book['country'],
                    'release_date' => date('Y-m-d', strtotime($book['released'])),
                ];
            }

        }

        return [
            'status_code' => 200,
            'status' => "success",
            'data' => $data
        ];
    }

    public function create_book(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'isbn' => 'required|string|regex:/(\d{3})-(\d{10})/|unique:books,isbn',
            'authors' => 'required|array',
            'authors.*' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'number_of_pages' => 'required|numeric',
            'publisher' => 'required|string:max:50',
            'release_date' => 'required|date',
        ]);

        if ($validator->fails()) return [
            'status_code' => 417,
            'status' => "fail",
//            'message' => $validator->errors(),
            'data' => []
        ];

        $book = Book::create($validator->validated());

        if (!$book) return [
            'status_code' => 417,
            'status' => "fail",
//            'message' => "Couldn't create book at this time, please try again later",
            'data' => []
        ];

        return [
            'status_code' => 201,
            'status' => 'success',
            'data' => [
                'book' => $book->makeHidden('id')
            ]
        ];

    }

    public function all_books(Request $request)
    {
        $books = Book::when($request->name, function ($query, $name) {
            $query->whereRaw('name like ?', ["%{$name}%"]);
        })->when($request->country, function ($query, $country) {
            $query->whereRaw('country like ?', ["%{$country}%"]);
        })->when($request->publisher, function ($query, $publisher) {
            $query->whereRaw('publisher like ?', ["%{$publisher}%"]);
        })->when($request->release_date, function ($query, $release_date) {
            $query->whereYear('release_date', $release_date);
        });

        return [
            'status_code' => 200,
            'status' => 'success',
            'data' => $books->get()
        ];
    }

    public function update_book(Request $request)
    {

        $book = Book::where('id', $request->id)->first();

        if (empty($book)) return [
            'status_code' => 404,
            'status' => 'fail',
            'data' => []
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:50',
            'isbn' => ['nullable', 'string', 'regex:/(\d{3})-(\d{10})/', Rule::unique('books', 'isbn')->ignore($book->id)],
            'authors' => 'nullable|array',
            'authors.*' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'number_of_pages' => 'nullable|numeric',
            'publisher' => 'nullable|string:max:50',
            'release_date' => 'nullable|date',
        ]);

        if ($validator->fails()) return [
            'status_code' => 417,
            'status' => "fail",
//            'message' => $validator->errors(),
            'data' => []
        ];

        if (!$book->update($validator->validated())) return [
            'status_code' => 417,
            'status' => "fail",
//            'message' => "Couldn't update book at this time, please try again later",
            'data' => []
        ];

        return [
            'status_code' => 200,
            'status' => 'success',
            "message" => "The book {$book->name} was updated successfully",
            'data' => $book
        ];
    }

    public function delete_book(Request $request)
    {
        $book = Book::where('id', $request->id)->first();

        if (empty($book)) return [
            'status_code' => 404,
            'status' => 'fail',
            'data' => []
        ];

        if (!$book->delete()) return [
            'status_code' => 417,
            'status' => "fail",
            'message' => "Couldn't delete book at this time, please try again later",
            'data' => []
        ];

        return [
            'status_code' => 204,
            'status' => 'success',
            "message" => "The book {$book->name} was deleted successfully",
            'data' => []
        ];
    }

    public function show_book(Request $request)
    {
        $book = Book::where('id', $request->id)->first();

        if (empty($book)) return [
            'status_code' => 404,
            'status' => 'fail',
            'data' => []
        ];

        return [
            'status_code' => 200,
            'status' => 'success',
            'data' => $book
        ];
    }
}
