<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAvatar;
use Illuminate\Http\Request;

class UserAvatarController extends Controller
{
    /**
     * Retorna un listado paginado de asignaciones de avatares a usuarios.
     */
    public function index(Request $request)
    {
        // Cargamos las relaciones 'user' y 'avatar' para obtener datos completos.
        $userAvatars = UserAvatar::with(['user', 'avatar'])->paginate(10);
        return response()->json($userAvatars);
    }

    /**
     * Almacena una nueva asignación de avatar a un usuario.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'avatar_id' => 'required|exists:avatars,id',
        ]);

        // Si no se envía la fecha de actualización, usamos la fecha actual.
        if (!isset($validatedData['updated'])) {
            $validatedData['updated'] = date('Y-m-d');
        }

        $userAvatar = UserAvatar::create($validatedData);

        return response()->json([
            'message' => 'User avatar assigned successfully',
            'data'    => $userAvatar,
        ], 201);
    }

    /**
     * Muestra una asignación específica.
     */
    public function show($id)
    {
        $userAvatar = UserAvatar::with(['user', 'avatar'])->findOrFail($id);
        return response()->json($userAvatar);
    }

    /**
     * Actualiza una asignación existente.
     */
    public function update(Request $request, $id)
    {
        $userAvatar = UserAvatar::findOrFail($id);

        $validatedData = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'avatar_id' => 'required|exists:avatars,id',
        ]);

        if (!isset($validatedData['updated'])) {
            $validatedData['updated'] = date('Y-m-d');
        }

        $userAvatar->update($validatedData);

        return response()->json([
            'message' => 'User avatar assignment updated successfully',
            'data'    => $userAvatar,
        ]);
    }

    /**
     * Elimina una asignación de avatar.
     */
    public function destroy($id)
    {
        $userAvatar = UserAvatar::findOrFail($id);
        $userAvatar->delete();

        return response()->json([
            'message' => 'User avatar assignment deleted successfully',
        ]);
    }
}
