<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        return Book::all();
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'title' => 'required|string',
                'author' => 'required|string',
                'pdf' => 'required|file|mimes:pdf|max:2048',
            ]);
    
            $pdfPath = $request->file('pdf')->store('pdf_files');
    
            $book = Book::create([
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'pdf_path' => $pdfPath,
            ]);
    
            return response()->json($book, 201);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }


    public function show($id)
    {
        $book = Book::findOrFail($id);
        return $book;
    }

    public function update(Request $request, $id)
    {
        try {
        $this->validate($request, [
            'title' => 'string',
            'author' => 'string',
            'pdf' => 'file|mimes:pdf|max:2048',
        ]);

        $book = Book::findOrFail($id);
        $book->update([
            'title' => $request->input('title', $book->title),
            'author' => $request->input('author', $book->author),
        ]);

  
        if ($request->hasFile('pdf')) {
            if ($book->pdf_path) {
                Storage::delete($book->pdf_path);
            }
            $pdfPath = $request->file('pdf')->store('pdf_files');
            $book->update(['pdf_path' => $pdfPath]);
        }

        return response()->json($book, 200);

        } catch (ValidationException $e) {
            return response()->json(['error' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], 200);
    }
}
