<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\AppResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function allrole()
    {
        // dd('role');
        if (Auth::check()) {
            $roles = Role::with('permissions')->get();
            return response()->json(['message' => 'All Roles', 'roles' => $roles]);
        } else {
            return response()->json(['message' => 'Unauthorized Access'], 401);
        }
    }

    public function allpermisisions()
    {

        if (Auth::check()) {
            $permissions = Permission::all();
            return response()->json(['message' => 'All Permissions', 'permissions' => $permissions]);
        } else {
            return response()->json(['message' => 'Unauthorized Access'], 401);
        }
    }

    // public function addRole(Request $request){


    //     if(Auth::check()){
    //         $validation= Validator::make($request->all(),[
    //             'name'=>'required|integer',
    //             'branch_id'=>'',
    //             'permissions' => 'required|array',

    //         ]);
    //        if($validation->fails()){

    //          return response()->json([
    //             'message' => 'Validation Fail',
    //             'errors' => $validation->errors()->toArray()], 400);
    //        }

    //         $role = Role::create(['name'=>$request->name,'guard_name'=>'api']);

    //         $role->syncPermissions($request->permissions);



    //         return response()->json([
    //             'message' => 'Role created successfully',
    //             'role' => $role
    //         ], 201);
    //     }
    //     else{

    //         return response()->json([
    //             'message' => 'Unauthorized Access'
    //         ], 401);
    //     }



    // }


    // public function addRole(Request $request)
    // {



    //     if (Auth::check()) {
    //         // Validate the incoming request
    //         $validation = Validator::make($request->all(), [
    //             'name' => 'required|string',
    //             'user_id' => 'required|integer|exists:users,id',
    //             'branch_id' => 'required|integer|exists:branch_manages,id',
    //             'permissions' => 'required|array',
    //         ]);

    //         if ($validation->fails()) {
    //             return response()->json([
    //                 'message' => 'Validation Fail',
    //                 'errors' => $validation->errors()->toArray()
    //             ], 400);
    //         }

    //         // Create the role with the branch and user information
    //         $role = Role::create([
    //             'name' => $request->name,
    //             'guard_name' => 'api',
    //             'user_id' => $request->user_id,
    //             'branch_id' => $request->branch_id,
    //         ]);



    //         // Sync permissions to the role
    //         $role->syncPermissions($request->permissions);

    //         // Assign the role to the user (assuming user is the owner of the role)
    //         $user = User::find($request->user_id);
    //         if ($user) {
    //             $user->assignRole($role);
    //         } else {
    //             return response()->json([
    //                 'message' => 'User not found'
    //             ], 404);
    //         }



    //         return response()->json([
    //             'message' => 'Role created and assigned successfully',
    //             'role' => $role,
    //         ], 201);
    //     } else {
    //         return response()->json([
    //             'message' => 'Unauthorized Access'
    //         ], 401);
    //     }
    // }


    public function addRole(Request $request)
    {
        if (Auth::check()) {
            // Validate the request
            $validation = Validator::make($request->all(), [
                'name' => 'required|string',
                'user_id' => 'required|integer|exists:users,id',
                'branch_id' => 'required|integer|exists:branch_manages,id',
                'permissions' => 'required|array',
            ]);

            if ($validation->fails()) {
                return response()->json([
                    'message' => 'Validation Fail',
                    'errors' => $validation->errors()->toArray()
                ], 400);
            }


            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
                'user_id' => $request->user_id,
                'branch_id' => $request->branch_id,
            ]);


            $permissions = $request->permissions;


            if (is_numeric($permissions[0])) {

                $permissions = Permission::whereIn('id', $permissions)->pluck('name')->toArray();
            } else {

                foreach ($permissions as $permission) {
                    Permission::firstOrCreate($permission);
                }
            }

            $role->syncPermissions($permissions);

          
            $user = User::find($request->user_id);
            if ($user) {
                $user->assignRole($role);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Role created and assigned successfully',
                'role' => $role,
            ], 201);
        } else {
            return response()->json([
                'message' => 'Unauthorized Access'
            ], 401);
        }
    }
}
