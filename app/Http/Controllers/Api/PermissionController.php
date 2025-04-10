<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions with Eloquent filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Log para debugging
            Log::debug('Permission sort params', [
                'order_column' => $request->input('order_column'),
                'order_direction' => $request->input('order_direction')
            ]);

            // Utilizar el modelo App\Models\Permission en lugar del Spatie\Permission\Models\Permission
            $query = \App\Models\Permission::query();

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
            Log::error('Error in permissions listing: ' . $e->getMessage());
            return response()->json(['message' => 'Error retrieving permissions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get permissions associated with a specific role
     *
     * @param int $id Role ID
     * @return \Illuminate\Http\Response
     */
    public function getRolePermissions($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Get all permissions
            $permissions = Permission::all();

            // Get the permissions for this specific role
            $rolePermissions = $role->permissions->pluck('id')->toArray();

            // Mark permissions that belong to this role
            foreach ($permissions as $permission) {
                $permission->assigned = in_array($permission->id, $rolePermissions);
            }

            return response()->json([
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting role permissions: ' . $e->getMessage());
            return response()->json(['message' => 'Error retrieving role permissions'], 500);
        }
    }

    /**
     * Update permissions for a role
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateRolePermissions(Request $request)
    {
        try {
            $request->validate([
                'role_id' => 'required|exists:roles,id',
                'permissions' => 'array',
            ]);

            $roleId = $request->input('role_id');
            $permissions = $request->input('permissions', []);

            $role = Role::findOrFail($roleId);

            // Sync permissions for the role
            $role->syncPermissions($permissions);

            return response()->json([
                'message' => 'Permissions updated successfully'
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating role permissions: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating role permissions'], 500);
        }
    }

    /**
     * Store a newly created permission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:permissions,name',
            ]);

            $permission = Permission::create(['name' => $request->name]);

            return response()->json([
                'message' => 'Permission created successfully',
                'data' => $permission
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating permission: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating permission'], 500);
        }
    }

    /**
     * Display the specified permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            return response()->json([
                'data' => $permission
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing permission: ' . $e->getMessage());
            return response()->json(['message' => 'Permission not found'], 404);
        }
    }

    /**
     * Update the specified permission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:permissions,name,' . $id,
            ]);

            $permission = Permission::findOrFail($id);
            $permission->name = $request->name;
            $permission->save();

            return response()->json([
                'message' => 'Permission updated successfully',
                'data' => $permission
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating permission: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating permission'], 500);
        }
    }

    /**
     * Remove the specified permission from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();

            return response()->json([
                'message' => 'Permission deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting permission: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting permission'], 500);
        }
    }
}
