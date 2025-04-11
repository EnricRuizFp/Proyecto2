<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\AvatarResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Models\Avatar;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $orderColumn = request('order_column', 'created_at');
        if (!in_array($orderColumn, ['id', 'username', 'name', 'surname1', 'surname2', 'email', 'nationality', 'created_at'])) {
            $orderColumn = 'created_at';
        }
        $orderDirection = request('order_direction', 'desc');
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        // Iniciar la consulta base - ELOQUENT
        $query = User::query();

        // Buscar por ID específico - ELOQUENT FILTERING
        if (request('search_id')) {
            $query->where('id', request('search_id'));
        }

        // Buscar por nombre - ELOQUENT FILTERING
        if (request('search_title')) {
            $query->where('name', 'like', '%' . request('search_title') . '%');
        }

        // Búsqueda global en múltiples columnas - ELOQUENT FILTERING
        if (request('search_global')) {
            $searchTerm = request('search_global');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('id', 'like', '%' . $searchTerm . '%')
                    ->orWhere('username', 'like', '%' . $searchTerm . '%')
                    ->orWhere('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('surname1', 'like', '%' . $searchTerm . '%')
                    ->orWhere('surname2', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('nationality', 'like', '%' . $searchTerm . '%')
                    ->orWhere('created_at', 'like', '%' . $searchTerm . '%');
            });
        }

        // Añadir dentro del método index() para depuración:
        Log::debug('Filtros de búsqueda aplicados', [
            'search_global' => request('search_global'),
            'search_id' => request('search_id'),
            'search_title' => request('search_title'),
            'count_after_filter' => $query->count(), // Número de registros tras el filtro
        ]);

        // Aplicar ordenamiento
        $query->orderBy($orderColumn, $orderDirection);

        // Paginar con 10 elementos por página (igual que en roles)
        $users = $query->paginate(10);

        // Añadir parámetros de consulta a los links de paginación
        $users->appends(request()->all());

        return UserResource::collection($users);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Request data:', $request->all());  // Log para debug

            // Validación personalizada para verificar duplicados
            $existingUser = User::where('username', $request->username)
                ->orWhere('email', $request->email)
                ->first();

            if ($existingUser) {
                $errors = [];
                if ($existingUser->username === $request->username) {
                    $errors['username'] = ['Este nombre de usuario ya está en uso'];
                }
                if ($existingUser->email === $request->email) {
                    $errors['email'] = ['Este correo electrónico ya está registrado'];
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $errors
                ], 422);
            }

            $validatedData = $request->validate([
                'username' => 'required|unique:users',
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'role_id' => 'required|array',
                'surname1' => 'required',
                'surname2' => 'nullable',
                'avatar_id' => 'nullable|exists:avatars,id',
                'nationality' => 'required|string|in:africa,america,asia,europe,oceania', // Modificar esta línea
            ]);

            Log::info('Validated data:', $validatedData);  // Log para debug

            DB::beginTransaction();

            try {
                $user = User::create([
                    'username' => $validatedData['username'],
                    'name' => $validatedData['name'],
                    'email' => $validatedData['email'],
                    'password' => Hash::make($validatedData['password']),
                    'surname1' => $validatedData['surname1'],
                    'surname2' => $validatedData['surname2'] ?? null,
                    'nationality' => $validatedData['nationality'],
                ]);

                if (!empty($validatedData['role_id'])) {
                    $user->syncRoles($validatedData['role_id']);
                }

                if (!empty($validatedData['avatar_id'])) {
                    $user->avatares()->sync([$validatedData['avatar_id']]);
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Usuario creado exitosamente',
                    'user' => $user
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Database error: ' . $e->getMessage());
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return UserResource
     */
    public function show(User $user)
    {
        $user->load('roles');
        $userData = new UserResource($user);

        // Log para debug
        Log::info('User nationality:', ['nationality' => $user->nationality]);

        return $userData;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return UserResource
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            Log::info('Update request data:', $request->all());

            DB::beginTransaction();

            try {
                // Procesar la nacionalidad
                $nationality = is_array($request->nationality)
                    ? $request->nationality['value']
                    : $request->nationality;

                // Validar la nacionalidad
                if (!in_array($nationality, ['africa', 'america', 'asia', 'europe', 'oceania'])) {
                    throw new \InvalidArgumentException('Nacionalidad inválida');
                }

                // Actualizar datos básicos
                $user->fill([
                    'username' => $request->username,
                    'name' => $request->name,
                    'email' => $request->email,
                    'surname1' => $request->surname1,
                    'surname2' => $request->surname2,
                    'nationality' => $nationality
                ]);

                // Actualizar contraseña si se proporcionó
                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                }

                // Guardar cambios
                if (!$user->save()) {
                    throw new \Exception('Error al guardar los datos del usuario');
                }

                // Actualizar roles
                if (!empty($request->role_id)) {
                    $user->syncRoles($request->role_id);
                }

                DB::commit();

                // Recargar el modelo con sus relaciones
                $user->load('roles');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Usuario actualizado correctamente',
                    'data' => new UserResource($user)
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error en la transacción:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error actualizando usuario:', [
                'user_id' => $user->id,
                'message' => $e->getMessage()
            ]);

            $statusCode = $e instanceof \InvalidArgumentException ? 422 : 500;

            return response()->json([
                'status' => 'error',
                'message' => 'Error al actualizar usuario',
                'errors' => [
                    'general' => [$e->getMessage()]
                ]
            ], $statusCode);
        }
    }

    public function updateimg(Request $request)
    {
        try {
            if (!$request->hasFile('picture')) {
                return response()->json([
                    'error' => 'No image file provided'
                ], 400);
            }

            $user = User::findOrFail($request->id);

            // Buscar si el usuario ya tiene un avatar personalizado a través de la relación
            $existingCustomAvatar = $user->avatares()
                ->where('type', 'custom')
                ->first();

            // Obtener el created_at existente
            $existingPivot = $user->avatares()
                ->latest()
                ->first();

            // Crear o actualizar avatar
            $avatar = $existingCustomAvatar ?? new Avatar([
                'name' => 'Avatar personalizado de ' . $user->name,
                'type' => 'custom'
            ]);

            if ($existingCustomAvatar) {
                // Actualizar el avatar existente
                $avatar = $existingCustomAvatar;
                $avatar->clearMediaCollection('avatars');
            } else {
                // Crear nuevo avatar personalizado
                $avatar = new Avatar([
                    'name' => 'Avatar personalizado de ' . $user->name,
                    'type' => 'custom'
                ]);
                $avatar->save();
            }

            // Subir imagen al avatar
            $media = $avatar->addMediaFromRequest('picture')
                ->preservingOriginal()
                ->toMediaCollection('avatars');

            // Asignar el avatar manteniendo el created_at original
            $user->avatares()->sync([
                $avatar->id => [
                    'created_at' => $existingPivot ? $existingPivot->pivot->created_at : now(),
                    'updated_at' => now()
                ]
            ]);

            return response()->json([
                'message' => 'Avatar updated successfully',
                'user' => new UserResource($user->fresh()),
                'avatar' => new AvatarResource($avatar),
                'media_url' => $media->getUrl()
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating user avatar: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error updating avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        $this->authorize('user-delete');
        $user->delete();

        return response()->noContent();
    }

    public function assignAvatar(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $request->validate([
                'avatar_id' => 'required|exists:avatars,id'
            ]);

            // Obtener el created_at existente si existe
            $existingPivot = $user->avatares()
                ->latest()
                ->first();

            $pivotData = [
                'created_at' => $existingPivot ? $existingPivot->pivot->created_at : now(),
                'updated_at' => now()
            ];

            // Asignar el nuevo avatar manteniendo el created_at original
            $user->avatares()->sync([$request->avatar_id => $pivotData]);

            // Recargar el usuario con sus relaciones
            $user->load('avatares');
            $avatar = Avatar::findOrFail($request->avatar_id);

            return response()->json([
                'message' => 'Avatar asignado exitosamente',
                'user' => new UserResource($user),
                'avatar' => new AvatarResource($avatar)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error asignando avatar: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error al asignar el avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    // Modificar el método que obtiene los avatares disponibles
    public function getAvailableAvatars(Request $request)
    {
        $userId = $request->user()->id;
        $avatars = Avatar::availableFor($userId)->get();
        return AvatarResource::collection($avatars);
    }

    /**
     * Obtiene todos los avatares asignados a usuarios
     */
    public function getUserAvatars()
    {
        $userAvatars = User::with('avatares')->paginate(10);
        return response()->json($userAvatars);
    }

    /**
     * Obtiene los avatares asignados a un usuario específico
     */
    public function getUserAvatar($userId)
    {
        $user = User::with('avatares')->findOrFail($userId);
        return response()->json($user->avatares);
    }

    /**
     * Elimina la asignación de un avatar a un usuario
     */
    public function removeAvatar($userId, $avatarId)
    {
        $user = User::findOrFail($userId);
        $user->avatares()->detach($avatarId);

        return response()->json([
            'message' => 'Avatar removed successfully from user'
        ]);
    }

    /**
     * Obtiene el username de un usuario por su ID
     */
    public function getUsernameById($userId)
    {
        try {

            // Obtener el ID por parámetro
            $user = User::find($userId);
            return $user ? $user->username : 'Desconocido';
        } catch (\Exception $e) {
            return 'No se ha encontrado el usuario';
        }
    }
}
