<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    /**
     * LISTAR NOTAS
     * 
     * Devuelve un listado de las notas.
     * 
     * Sin parámetros de entrada
     * 
     * @return JsonResponse|mixed
     * Respuesta: Lista de notas en formato JSON.
     */
    public function index (): JsonResponse
    {
        $notes = Note::all();
        return response()->json($notes, 200);
    }

    /**
     * ALMACENAR NOTA
     * 
     * Guarda una nueva nota con los datos proporcionados.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "title": string|required,
     *   "content": string|required
     * }
     * 
     * @return JsonResponse|mixed
     * Respuesta: Datos de la nota creada en formato JSON.
     */
    public function store (Request $request): JsonResponse
    {
        $note = Note::create($request->all());
        return response()->json([
            'success'=>true,
            'data' => $note
        ], 201);
    }

    /**
     * MOSTRAR NOTA
     * 
     * Muestra los detalles de la nota especificada.
     * 
     * @param mixed $id
     * Datos esperados: ID de la nota a mostrar.
     * 
     * @return JsonResponse|mixed
     * Respuesta: Datos de la nota a mostrar en formato JSON.
     */
    public function show ($id): JsonResponse
    {
        $note = Note::find($id);
        return response()->json($note, 200);
    }

    /**
     * ACTUALIZAR NOTA
     * 
     * Actualiza los datos de la nota especificada.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "title": string|optional,
     *   "content": string|optional
     * }
     * 
     * @param mixed $id
     * Datos esperados: ID de la nota a actualizar.
     * 
     * @return JsonResponse|mixed
     * Respuesta: Datos actualizados de la nota en formato JSON.
     */
    public function update (Request $request, $id): JsonResponse
    {
        $note = Note::find($id);
        $note -> update($request->all());
        return response()->json($note, 200);

    }

    /**
     * ELIMINAR NOTA
     * 
     * Elimina la nota especificada.
     * 
     * @param mixed $id
     * Datos esperados: ID de la nota a eliminar.
     * 
     * @return JsonResponse|mixed
     * Respuesta exitosa: Mensaje de success en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function destroy ($id): JsonResponse
    {
        if (!Note::find($id)) 
        return response()->json([
            'success' => false,
            'message' => 'Note not found'
        ], 404);

        $note = Note::find($id);
        $note -> delete();
        return response()->json([
            'success'=>true
        ], 204);
    }
}
