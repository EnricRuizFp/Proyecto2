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

class UserController extends Controller
{
    public function index()
    {
        $orderColumn = request('order_column', 'created_at');
        if (!in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'created_at';
        }
        $orderDirection = request('order_direction', 'desc');
        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }
        $users = User::
        when(request('search_id'), function ($query) {
            $query->where('id', request('search_id'));
        })
            ->when(request('search_title'), function ($query) {
                $query->where('name', 'like', '%'.request('search_title').'%');
            })
            ->when(request('search_global'), function ($query) {
                $query->where(function($q) {
                    $q->where('id', request('search_global'))
                        ->orWhere('name', 'like', '%'.request('search_global').'%');

                });
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(100);

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $role = Role::find($request->role_id);
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->surname1 = $request->surname1;
        $user->surname2 = $request->surname2;

        $user->password = Hash::make($request->password);

        if ($user->save()) {
            if ($role) {
                $user->assignRole($role);
            }
            return new UserResource($user);
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
        $user->load('roles')->get();
        return new UserResource($user);
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
        $role = Role::find($request->role_id);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->surname1 = $request->surname1;
        $user->surname2 = $request->surname2;

        if(!empty($request->password)) {
            $user->password = Hash::make($request->password) ?? $user->password;
        }

        if ($user->save()) {
            if ($role) {
                $user->syncRoles($role);
            }
            return new UserResource($user);
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

}
