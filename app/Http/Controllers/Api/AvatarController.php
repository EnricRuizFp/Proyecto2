<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use App\Http\Resources\AvatarResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AvatarController extends Controller
{
    /**
     * LISTAR AVATARES
     * 
     * Devuelve el listado de avatares disponibles.
     * Sólo para usuarios autenticados.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * Usuario autenticado.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * Respuesta exitosa: Listado de avatares en formato JSON.
     * Respuesta error: error y mensaje de error en formato JSON.
     */
    public function index(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }

            // 1. Obtener avatares predeterminados
            $defaultAvatars = Avatar::where('type', 'default')->get();

            // 2. Obtener avatar personalizado del usuario
            $customAvatar = Avatar::where('type', 'custom')
                ->whereHas('users', function($query) use ($user) {
                    $query->where('users.id', $user->id);
                })
                ->first();

            // 3. Combinar resultados
            $avatars = $defaultAvatars;
            if ($customAvatar) {
                $avatars->push($customAvatar);
            }

            // Debug
            Log::info('Avatares encontrados:', [
                'user_id' => $user->id,
                'total' => $avatars->count(),
                'default_count' => $defaultAvatars->count(),
                'has_custom' => !is_null($customAvatar)
            ]);

            return AvatarResource::collection($avatars);

        } catch (\Exception $e) {
            Log::error('Error en AvatarController@index: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'error' => 'Error al obtener avatares',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * MOSTRAR AVATAR
     * 
     * Devuelve los detalles de un avatar específico.
     * 
     * @param mixed $id
     * Datos esperados: ID del avatar a mostrar.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Datos del avatar en formato JSON.
     */
    public function show($id)
    {
        $avatar = Avatar::findOrFail($id);
        return response()->json($avatar);
    }

    /**
     * ALMACENAR AVATAR
     * 
     * Crea un nuevo avatar con la imagen proporcionada.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *    "name": string|required|max:50,
     *    "image": file|required|image|mimes:webp,png,jpeg
     * }
     * 
     * @param mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Mensaje de éxito y datos del avatar creado en formato JSON.
     */
    public function store(Request $request)
    {
        // Validamos que se envíen el nombre y la imagen
        $request->validate([
            'name'  => 'required|string|max:50',
            'image' => 'required|image|mimes:webp,png,jpeg'
        ]);

        // Creamos el registro del avatar (sin campo image_path, ya que Spatie gestiona la imagen)
        $avatar = Avatar::create([
            'name' => $request->name,
        ]);

        // Si se envía un archivo, se adjunta a la colección 'avatars'
        if ($request->hasFile('image')) {
            $avatar->addMediaFromRequest('image')
                   ->preservingOriginal()
                   ->toMediaCollection('avatars');
        }

        return response()->json([
            'message' => 'Avatar created successfully',
            'data'    => $avatar,
        ], 201);
    }

    /**
     * ACTUALIZAR AVATAR
     * 
     * Actualiza el nombre y/o imagen de un avatar existente.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *    "name": string|required|max:50,
     *    "image": file|nullable|image|mimes:webp,png,jpeg
     * }
     * 
     * @param mixed $id
     * Datos esperados: ID del avatar a actualizar.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Mensaje de éxito y datos del avatar actualizado en formato JSON.
     */
    public function update(Request $request, $id)
    {
        $avatar = Avatar::findOrFail($id);

        // Validamos: la imagen es opcional en actualización
        $request->validate([
            'name'  => 'required|string|max:50',
            'image' => 'nullable|image|mimes:webp,png,jpeg'
        ]);

        // Actualizamos el nombre
        $avatar->update([
            'name' => $request->name,
        ]);

        // Si se envía una nueva imagen, borramos la anterior y la agregamos
        if ($request->hasFile('image')) {
            // Opcional: eliminar los medios existentes en la colección 'avatars'
            $avatar->clearMediaCollection('avatars');

            $avatar->addMediaFromRequest('image')
                ->preservingOriginal()
                ->toMediaCollection('avatars');
        }

        return response()->json([
            'message' => 'Avatar updated successfully',
            'data'    => $avatar,
        ]);
    }

    /**
     * ELIMINAR AVATAR
     * 
     * Elimina el avatar especificado y sus archivos asociados.
     * 
     * @param mixed $id
     * Datos esperados: ID del avatar a eliminar.
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta: Mensaje de éxito en formato JSON.
     */
    public function destroy($id)
    {
        $avatar = Avatar::findOrFail($id);
        $avatar->delete(); // Esto elimina el registro y, por configuración, los medios asociados

        return response()->json([
            'message' => 'Avatar deleted successfully'
        ]);
    }


}
