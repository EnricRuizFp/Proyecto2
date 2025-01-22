<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    public function index()
    {
        $authors = Author::all();
        return response()->json(['status' => 405, 'susccess' => true, 'data' => $authors]);
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), 
        [
            'name' => ['required', 'max:255'],
            'surname' => ['required'],
            'email'=> ['required','unique:Authors']
        ]);

        $data = $validator->validated();
        $author = Author::create($data);
        return response()->json(['status' => 405, 'susccess' => true, 'data' => $author]);

        // Author::create(attributes: $request->all());
        // $author = new Author();
        // $author->name = $request->name;
        // $author->surname = $request->surname;
        // $author->email = $request->email;
        // $author->save();
        // return response()->json(['status' => 405, 'susccess' => true, 'data' => $author]);
    }

    public function destroy(Author $author){
        $author->delete();
        return response()->json(['status' => 405, 'susccess' => true, 'data'=> '']);
    }
    
    public function show(Author $author){
        return response()->json(['status'=> 405, ''=> true, 'data' => $author]);
    }
}
