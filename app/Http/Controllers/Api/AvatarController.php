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

    // Aquí podrías definir show, update y destroy según sea necesario.
}
