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
        $user = User::findOrFail($request->id);
        
        if ($request->hasFile('picture')) {
            // Limpia la colección de imágenes personalizadas del usuario
            $user->clearMediaCollection('users-avatars');
            
            // Añade la nueva imagen a la colección "users-avatars"
            $user->addMediaFromRequest('picture')
                ->preservingOriginal()
                ->toMediaCollection('users-avatars');
        }
        
        // Retornamos el usuario actualizado
        dd($user->getMedia('users-avatars'));
        return new UserResource($user->fresh());
    }

    public function destroy(User $user)
    {
        $this->authorize('user-delete');
        $user->delete();

        return response()->noContent();
    }

    public function assignAvatar(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->hasFile('picture')) {
            // Opción: Avatar personalizado

            // Desasocia cualquier avatar asignado previamente
            $user->avatares()->detach();

            // Crea un nuevo registro para el avatar personalizado.
            // (Opcional: si quieres distinguirlo, podrías agregar un campo 'custom' en la tabla avatars)
            $avatar = new Avatar(['name' => 'Avatar personalizado']);
            $avatar->save();

            // Sube la imagen al registro del avatar usando la colección definida en el modelo Avatar
            $avatar->addMediaFromRequest('picture')
                ->preservingOriginal()
                ->toMediaCollection('avatars');

            // Asocia el nuevo avatar al usuario mediante la tabla pivot
            $user->avatares()->sync([$avatar->id]);

            // Retorna la respuesta con el avatar creado usando el recurso (más adelante crearemos AvatarResource)
            return response()->json([
                'message' => 'Avatar personalizado asignado exitosamente',
                'avatar'  => new AvatarResource($avatar)
            ], 200);
        } else {
            // Opción: Selección de avatar predeterminado.
            $request->validate([
                'avatar_id' => 'required|exists:avatars,id'
            ]);

            // Asocia el avatar predeterminado al usuario
            $user->avatares()->sync([$request->avatar_id]);

            // Obtiene el avatar asignado (ahora es el predeterminado)
            $avatar = $user->avatares()->first();

            return response()->json([
                'message' => 'Avatar predeterminado asignado exitosamente',
                'avatar'  => new AvatarResource($avatar)
            ], 200);
        }
    }

}
