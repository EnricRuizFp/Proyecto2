<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Support\Facades\Validator;

class AuthorController extends Controller
{
    /**
     * LISTAR AUTORES
     * 
     * Devuelve una lista de todos los autores en formato JSON.
     * 
     * Sin parámetros de entrada.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Listado de los autores en formato JSON.
     */
    public function index()
    {
        $authors = Author::all();
        return response()->json(['status' => 405, 'susccess' => true, 'data' => $authors]);
    }

    /**
     * ALMACENAR AUTOR
     * 
     * Valida los datos de entrada y crea un nuevo autor con los datos proporcionados.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *    "name": string|required|max:255,
     *    "surname": string|required,
     *    "email": string|required|unique:Authors
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Los datos del autor almacenado en formato JSON.
     */
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

    /**
     * ELIMINAR AUTOR
     * 
     * Elimina el autor especificado por el ID.
     * 
     * @param \App\Models\Author $author
     * Datos esperados: El ID del autor a eliminar.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Mensaje de éxito en formato JSON.
     */
    public function destroy(Author $author){
        $author->delete();
        return response()->json(['status' => 405, 'susccess' => true, 'data'=> '']);
    }
    
    /**
     * MOSTRAR AUTOR
     * 
     * Devuelve los datos del autor especificador por ID.
     * 
     * @param \App\Models\Author $author
     * Datos esperados: El ID del autor a mostrar.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Los datos del autor en formato JSON.
     */
    public function show(Author $author){
        return response()->json(['status'=> 405, ''=> true, 'data' => $author]);
    }

    /**
     * ACTUALIZAR AUTOR
     * 
     * Actualiza los datos de un autor.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *    "name": string|required|max:255,
     *    "surname": string|required,
     *    "email": string|required|unique:Authors
     * } 
     * 
     * @param \App\Models\Author $author
     * Datos esperados: El ID del autor a actualizar.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Los datos del autor actualizado en formato JSON.
     */
    public function update(Request $request, Author $author){
        $validator = Validator::make($request->all(), [
            'name'=> ['required', 'max:255'],
            'surname' => ''
        ]
        );

        $data = $validator->validated();
        $author->update($data);
        return response()->json(['status'=> 405, ''=> true, 'data'=> $author]);
    }

}
