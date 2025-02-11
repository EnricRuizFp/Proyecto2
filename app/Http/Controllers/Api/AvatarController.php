<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avatar;
use Illuminate\Http\Request;

class AvatarController extends Controller
{
    /**
     * Muestra una lista paginada de avatares.
     */
    public function index(Request $request)
    {
        $avatars = Avatar::paginate(10);
        return response()->json($avatars);
    }

    public function show($id)
    {
        $avatar = Avatar::findOrFail($id);
        return response()->json($avatar);
    }

    /**
     * Almacena un nuevo avatar.
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
    public function destroy($id)
    {
        $avatar = Avatar::findOrFail($id);
        $avatar->delete(); // Esto elimina el registro y, por configuración, los medios asociados

        return response()->json([
            'message' => 'Avatar deleted successfully'
        ]);
    }


}
