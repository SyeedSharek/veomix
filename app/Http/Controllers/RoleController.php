<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\AppResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function allrole()
    {
        // dd('role');
        if(Auth::check()){
            $roles = Role::with('permissions')->get();
            return response()->json(['message' => 'All Roles', 'roles' => $roles]);
        }else{
            return response()->json(['message' => 'Unauthorized Access'], 401);
        }


    }

    public function allpermisisions(){

        if(Auth::check()){
            $permissions = Permission::all();
            return response()->json(['message' => 'All Permissions', 'permissions' => $permissions ]);

        }else{
            return response()->json(['message' => 'Unauthorized Access'], 401);
        }


    }

    public function addRole(Request $request){
        dd('role');

        if(Auth::check()){
            $validation= Validator::make($request->all(),[
                'name'=>'required|integer',
                'branch_id'=>'',
                'permissions' => 'required|array',

            ]);
           if($validation->fails()){
            return $this->errorResponse('Validation Fail',400,$validation->errors()->toArray());
           }

            $role = Role::create(['name'=>$request->name,'guard_name'=>'api']);
            // $permission = Permission::create(['name' => $request->name]);

            $role->syncPermissions($request->permissions);


            return $this->successResponse($role, 'Role created successfully', 201);
        }
        else{
            return $this->errorResponse('Unauthorized', 401);
        }



    }



}
