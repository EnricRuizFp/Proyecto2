<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with Eloquent filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Log para debugging
            Log::debug('Role sort params', [
                'order_column' => $request->input('order_column'),
                'order_direction' => $request->input('order_direction')
            ]);

            // Utilizar el modelo App\Models\Role en lugar del Spatie\Permission\Models\Role
            $query = \App\Models\Role::query();

            // Filter by ID
            if ($request->filled('search_id')) {
                $query->where('id', $request->search_id);
            }

            // Filter by title/name
            if ($request->filled('search_title')) {
                $query->where('name', 'like', '%' . $request->search_title . '%');
            }

            // Global search across multiple columns
            if ($request->filled('search_global')) {
                $search = $request->search_global;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('id', 'like', '%' . $search . '%')
                        ->orWhere('created_at', 'like', '%' . $search . '%');
                });
            }

            // Apply ordering only if specified in request
            if ($request->filled('order_column') && $request->filled('order_direction')) {
                $validColumns = ['id', 'name', 'created_at', 'guard_name'];
                $orderColumn = in_array($request->input('order_column'), $validColumns)
                    ? $request->input('order_column')
                    : 'id';

                $orderDirection = in_array(strtolower($request->input('order_direction')), ['asc', 'desc'])
                    ? $request->input('order_direction')
                    : 'asc';

                $query->orderBy($orderColumn, $orderDirection);
            }

            // Paginar con el número correcto de filas por página
            $perPage = $request->input('per_page', 10);

            $paginator = $query->paginate($perPage);

            // Asegurar que se devuelve la cantidad total de registros
            $paginator->appends($request->all());

            return $paginator;
        } catch (\Exception $e) {
            Log::error('Error in roles listing: ' . $e->getMessage());
            return response()->json(['message' => 'Error retrieving roles: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get a list of all roles without pagination.
     *
     * @return \Illuminate\Http\Response
     */
    public function getList()
    {
        try {
            $roles = Role::all();

            // Log para debug
            Log::debug('Roles list requested', [
                'count' => $roles->count(),
                'roles' => $roles->pluck('name')
            ]);

            return response()->json($roles);
        } catch (\Exception $e) {
            Log::error('Error getting roles list: ' . $e->getMessage());
            return response()->json(['message' => 'Error retrieving roles list'], 500);
        }
    }

    /**
     * Store a newly created role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:roles,name',
                'permissions' => 'nullable|array',
            ]);

            $role = Role::create(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json([
                'message' => 'Role created successfully',
                'data' => $role
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating role: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating role'], 500);
        }
    }

    /**
     * Display the specified role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);
            return response()->json([
                'data' => $role
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing role: ' . $e->getMessage());
            return response()->json(['message' => 'Role not found'], 404);
        }
    }

    /**
     * Update the specified role in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:roles,name,' . $id,
                'permissions' => 'nullable|array',
            ]);

            $role = Role::findOrFail($id);
            $role->name = $request->name;
            $role->save();

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json([
                'message' => 'Role updated successfully',
                'data' => $role
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating role: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating role'], 500);
        }
    }

    /**
     * Remove the specified role from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return response()->json([
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting role: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting role'], 500);
        }
    }
}
