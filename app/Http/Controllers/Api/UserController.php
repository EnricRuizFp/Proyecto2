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
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    /**
     * LISTAR USUARIOS PAGINADO A 10
     * 
     * Devuelve un listado paginado y filtrable de los usuarios.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados: (opcionales)
     * {
     *   "order_column": "id"|"username"|"name"|"surname1"|"surname2"|"email"|"nationality"|"created_at",
     *   "order_direction": "asc"|"desc",
     *   "search_id": int,
     *   "search_title": string,
     *   "search_global": string
     * }
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * Respuesta: Colección paginada de usuarios.
     */
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

        // Aplicar ordenamiento
        $query->orderBy($orderColumn, $orderDirection);

        // Paginar con 10 elementos por página (igual que en roles)
        $users = $query->paginate(10);

        // Añadir parámetros de consulta a los links de paginación
        $users->appends(request()->all());

        return UserResource::collection($users);
    }

    /**
     * LISTAR USUARIOS
     * 
     * Devuelve un listado filtrable de los usuarios.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados: (opcionales)
     * {
     *   "order_column": "id"|"username"|"name"|"surname1"|"surname2"|"email"|"nationality"|"created_at",
     *   "order_direction": "asc"|"desc",
     *   "search_id": int,
     *   "search_title": string,
     *   "search_global": string
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Colección completa de usuarios.
     */
    public function getAllUsers()
    {
        try {
            // Obtener todos los usuarios
            $users = User::all();
            
            // Devolver la colección de usuarios
            return response()->json($users);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * CREAR USUARIO
     * 
     * Crea un nuevo usuario con los datos proporcionados.
     * -> No permite insertar si están duplicados y crea el ranking inicial a 0.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "username": string|required|unique,
     *   "name": string|required,
     *   "email": string|required|email|unique,
     *   "password": string|required|min:8,
     *   "role_id": array|required,
     *   "surname1": string|required,
     *   "surname2": string|nullable,
     *   "avatar_id": int|nullable|exists:avatars,id,
     *   "nationality": string|required|in:africa,america,asia,europe,oceania
     * }
     * 
     * @return mixed|\Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos del usuario creado en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function store(Request $request)
    {
        try {
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

            // Validar los datos introducidos por request
            $validatedData = $request->validate([
                'username' => 'required|unique:users',
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:8',
                'role_id' => 'required|array',
                'surname1' => 'required',
                'surname2' => 'nullable',
                'avatar_id' => 'nullable|exists:avatars,id',
                'nationality' => 'required|string|in:africa,america,asia,europe,oceania',
            ]);

            DB::beginTransaction();

            try {

                // Crear el usuario
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

                // Crear un ranking inicial para el usuario solo si no existe
                $existingRanking = $user->ranking()->first();
                if (!$existingRanking) {
                    $user->ranking()->create([
                        'wins' => 0,
                        'losses' => 0,
                        'draws' => 0,
                        'points' => 0
                    ]);
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Usuario creado exitosamente',
                    'data' => [
                        'user' => $user,
                        'ranking_created' => true
                    ]
                ], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor',
                'debug_message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * MOSTRAR USUARIO
     * 
     * Devuelve los datos del usuario especificado.
     * -> Además devuelve los roles que tiene el usuario.
     *
     * @param \App\Models\User $user
     * Datos esperados: El usuario a mostrar.
     * 
     * @return \App\Http\Resources\UserResource
     * Respuesta: Datos del usuario en formato JSON.
     */
    public function show(User $user)
    {
        $user->load('roles');
        $userData = new UserResource($user);

        return $userData;
    }

    /**
     * ACTUALIZAR USUARIO
     * 
     * Actualiza los datos del usuario especificado.
     *
     * @param UpdateUserRequest $request
     * Datos esperados del request:
     * {
     *   "username": string|required,
     *   "name": string|required,
     *   "email": string|required|email,
     *   "password": string|nullable,
     *   "role_id": array|nullable,
     *   "surname1": string|required,
     *   "surname2": string|nullable,
     *   "nationality": string|required|in:africa,america,asia,europe,oceania
     * }
     * 
     * @param User $user
     * Datos esperados: El usuario a actualizar.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos actualizados en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
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
                throw $e;
            }
        } catch (\Exception $e) {

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

    /**
     * ACTUALIZAR IMAGEN DE PERFIL
     * 
     * Crea o actualiza el avatar personalizado del usuario.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "id": int|required,
     *   "picture": file|required|image
     * }
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos del usuario y avatar en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
    public function updateimg(Request $request)
    {
        try {
            // Verificar si se le ha pasado una imagen
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
            return response()->json([
                'error' => 'Error updating avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ELIMINAR USUARIO
     * 
     * Eliminar el usuario especificado.
     * Requere el permiso: 'user-delete'
     * 
     * @param \App\Models\User $user
     * Datos esperados: El usuario a eliminar.
     * 
     * @return \Illuminate\Http\Response
     * Respuesta: No devuelve nada.
     */
    public function destroy(User $user)
    {
        $this->authorize('user-delete');
        $user->delete();

        return response()->noContent();
    }

    /**
     * ASIGNAR AVATAR
     * 
     * Asigna un avatar específico a un usuario.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "avatar_id": int|required|exists:avatars,id
     * }
     * 
     * @param int $id
     * Datos esperados: ID del usuario a actualizar.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta exitosa: Mensaje de éxito y datos actualizados del usuario y avatar en formato JSON.
     * Respuesta error: Mensaje de error en formato JSON.
     */
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
            return response()->json([
                'error' => 'Error al asignar el avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * OBTENER AVATARES DISPONIBLES
     * 
     * Devuelve los avatares disponibles para el usuario.
     * 
     * @param \Illuminate\Http\Request $request
     * Datos esperados del request:
     * {
     *   "user": {
     *     "id": int|required
     *   }
     * }
     * 
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * Respuesta: Colección de avatares disponibles en formato JSON.
     */
    public function getAvailableAvatars(Request $request)
    {
        $userId = $request->user()->id;
        $avatars = Avatar::availableFor($userId)->get();
        return AvatarResource::collection($avatars);
    }

    /**
     * LISTAR AVATARES DE USUARIOS
     * 
     * Devuelve un listado paginado de usuarios con sus avatares asignados.
     * 
     * Sin parámetros de entrada.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Listado paginado de usuarios con sus avatares en formato JSON.
     */
    public function getUserAvatars()
    {
        $userAvatars = User::with('avatares')->paginate(10);
        return response()->json($userAvatars);
    }

    /**
     * OBTENER AVATAR DE USUARIO
     * 
     * Devuelve los avatares asignados a un usuario específico.
     * 
     * @param int $userId
     * Datos esperados: ID del usuario.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Avatares del usuario en formato JSON.
     */
    public function getUserAvatar($userId)
    {
        $user = User::with('avatares')->findOrFail($userId);
        return response()->json($user->avatares);
    }

    /**
     * ELIMINAR AVATAR DE USUARIO
     * 
     * Elimina la asignación de un avatar específico a un usuario.
     * 
     * @param int $userId
     * Datos esperados: ID del usuario.
     * 
     * @param int $avatarId
     * Datos esperados: ID del avatar a desvincular.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Respuesta: Mensaje de éxito en formato JSON.
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
     * OBTENER NOMBRE DE USUARIO
     * 
     * Devuelve el nombre de usuario (username) correspondiente a un ID.
     * 
     * @param int $userId
     * Datos esperados: ID del usuario.
     * 
     * @return string
     * Respuesta exitosa: Username del usuario.
     * Respuesta error: Mensaje de que no se ha encontrado el usuario.
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
