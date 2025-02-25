<?php

namespace App\Http\Controllers\CompanySettings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompanySettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $data = CompanySetting::with('language')->get();
            return response()->json([
                'message' => 'Data get successfully',
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'company_name' => 'required|string',
                'company_email' => 'required|email',
                'company_phone' => 'required|string',
                'company_telephone' => 'nullable|string',
                'company_address' => 'required|string',
                'company_print_logo' => 'required',
                'company_logo' => 'required',
                'language_id' => 'required|exists:languages,id',

            ]);
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ]);
            }


            $data = $request->except(['company_print_logo', 'company_logo']);

            if ($request->hasFile('company_print_logo')) {
                $image = $request->file('company_print_logo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('company/printLogo'), $imageName);
                $data['company_print_logo'] = 'company/printLogo/' . $imageName;
            }

            if ($request->hasFile('company_logo')) {
                $image = $request->file('company_logo');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('company/logo'), $imageName);
                $data['company_logo'] = 'company/logo/' . $imageName;
            }

            $member = CompanySetting::create($data);
            return response()->json([
                'message' => 'Successfully created company',
                'data' => $member
            ]);
        } else {
            return response()->json([
                'errors' => 'Unauthorized',
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function userSetup(Request $request)
    {

        if (Auth::check()) {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'full_name' => 'required|string',
                'phone' => 'required|string',
                'description' => 'required|string',
                'employee_id' => 'nullable|integer|exists:employees,id',
                'branch_id' => 'nullable|integer|exists:branch_manages,id',
                'designation_id' => 'nullable|integer|exists:designations,id',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'user_image' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Prepare data for insertion
            $data = $request->except('user_image');
            $data['password'] = bcrypt($request->password);

            // Handle image upload
            if ($request->hasFile('user_image')) {
                $image = $request->file('user_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('userImage/userProfile'), $imageName);
                $data['user_image'] = 'userImage/userProfile/' . $imageName;
            }

            // Create the user
            $user = User::create($data);

            return response()->json([
                'message' => 'User created successfully!',
                'data' => $user,
            ], 201);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }

    public function userShowDetails()
    {
        if (Auth::check()) {
            $data = User::with(['employee', 'branch', 'designation'])
                ->whereNotIn('name', ['admin', 'superadmin'])
                ->get()->makeVisible('password');
            return response()->json([
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ]);
        }
    }


    public function userDelete($user_id)
    {
        if (Auth::check()) {
            $user = User::find($user_id);

            if ($user) {
                // Prevent deletion if the user's name is "admin" or "superadmin"
                if (in_array(strtolower($user->name), ['admin', 'superadmin'])) {
                    return response()->json([
                        'error' => 'Cannot delete admin or superadmin!',
                    ]);
                }

                $user->delete();

                return response()->json([
                    'message' => 'User deleted successfully!',
                ]);
            } else {
                return response()->json([
                    'error' => 'User not found!',
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ]);
        }
    }


    public function userUpdate(Request $request, $user_id)
    {
        if (Auth::check()) {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'full_name' => 'required|string',
                'phone' => 'required|string',
                'description' => 'required|string',
                'employee_id' => 'nullable|integer|exists:employees,id',
                'branch_id' => 'nullable|integer|exists:branch_manages,id',
                'designation_id' => 'nullable|integer|exists:designations,id',
                'email' => 'required|email|unique:users,email,' . $user_id,
                'password' => 'nullable|string',
                'user_image' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }


            $user = User::find($user_id);

            if (!$user) {
                return response()->json([
                    'error' => 'User not found!',
                ], 404);
            }


            if (in_array(strtolower($user->name), ['admin', 'superadmin'])) {
                return response()->json([
                    'error' => 'Cannot update admin or superadmin!',
                ], 403);
            }


            $data = $request->except('user_image');


            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }


            if ($request->hasFile('user_image')) {

                if ($user->user_image) {
                    unlink(public_path($user->user_image));
                }

                $image = $request->file('user_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('userImage/userProfile'), $imageName);
                $data['user_image'] = 'userImage/userProfile/' . $imageName;
            }


            $user->update($data);

            return response()->json([
                'message' => 'User updated successfully!',
                'data' => $user,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
        }
    }

    public function eyeViewUserShowDetails($user_id)
    {
        if (Auth::check()) {
            $user = User::with(['employee', 'branch', 'designation'])
                ->where('id', $user_id)
                ->first();

            if ($user) {

                if (in_array(strtolower($user->name), ['admin', 'superadmin'])) {
                    return response()->json([
                        'error' => 'Cannot Show admin or superadmin Details !',
                    ]);
                }

                return response()->json([
                    'data' => $user,
                ]);
            } else {
                return response()->json([
                    'error' => 'User not found!',
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ]);
        }
    }


    public function userAllSearch(Request $request)
    {
        if (Auth::check()) {
            $search = $request->input('search');
            $users = User::with(['employee', 'branch', 'designation'])
                ->whereNotIn('name', ['admin', 'superadmin'])
                ->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('full_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('email', 'LIKE', '%' . $search . '%');
                })
                ->paginate(10);
            return response()->json([
                'data' => $users,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ]);
        }
    }

    public function branchIdWishSearch($branch_Id)
    {
        if (Auth::check()) {
            $users = User::with(['employee', 'branch', 'designation'])
                ->where('branch_id', $branch_Id)
                ->whereNotIn('name', ['admin', 'superadmin'])
                ->paginate(10);
            return response()->json([
                'data' => $users,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ]);
        }
    }

    public function userListFilter(Request $request)
    {
        if (Auth::check()) {
            $full_name = $request->input('full_name');
            $name = $request->input('name');
            $phone = $request->input('phone');
            $email = $request->input('email');
            $branch_id = $request->input('branch_id');
            $designation_id = $request->input('designation_id');


            $users = User::with(['employee', 'branch', 'designation'])
                ->whereNotIn('name', ['admin', 'superadmin'])
                ->when($full_name, function ($query) use ($full_name) {
                    return $query->where('full_name', 'LIKE', '%' . $full_name . '%');
                })
                ->when($name, function ($query) use ($name) {
                    return $query->where('name', 'LIKE', '%' . $name . '%');
                })
                ->when($phone, function ($query) use ($phone) {
                    return $query->where('phone', 'LIKE', '%' . $phone . '%');
                })
                ->when($email, function ($query) use ($email) {
                    return $query->where('email', 'LIKE', '%' . $email . '%');
                })
                ->when($branch_id, function ($query) use ($branch_id) {
                    return $query->where('branch_id', $branch_id);
                })
                ->when($designation_id, function ($query) use ($designation_id) {
                    return $query->where('designation_id', $designation_id);
                })
                ->paginate(10);  // Paginate the results

            // Return the filtered list of users
            return response()->json([
                'data' => $users,
            ]);
        } else {
            return response()->json([
                'error' => 'Unauthorized',
            ], 401); // Unauthorized error with proper status code
        }
    }


    public function storeBasicInformation(Request $request)
    {
        if(Auth::check()){
            $validator = Validator::make($request->all(),[
                'full_name' => 'required|string',
                'phone' => 'required|string',
                'email' => 'required|string',
                'date_of_birth' => 'required|date_format:d/m/Y',
                'nid_number' => 'required|string',
                'nid_front_image' => 'required|string',
                'nid_back_image' => 'required|string',
                'present_address' => 'required|string',
                'image' => 'required|string',

            ]);

        }

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }


        if ($request->hasFile('nid_front_image')) {
            $image = $request->file('nid_front_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('basicInformation/nid_front_image'), $imageName);
            // $imageName = 'employee/profile'. $imageName;
            $data['nid_front_image'] = 'basicInformation/nid_front_image/' . $imageName;
        }


        if ($request->hasFile('nid_back_image')) {
            $image = $request->file('nid_back_image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('basicInformation/nid_back_image'), $imageName);
            // $imageName = 'employee/profile'. $imageName;
            $data['nid_back_image'] = 'basicInformation/nid_front_image/' . $imageName;
        }


        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('basicInformation/image/image'), $imageName);
            // $imageName = 'employee/profile'. $imageName;
            $data['image'] = 'basicInformation/image/' . $imageName;
        }

        else{
            return response()->json([
                'error' => 'Unauthorized',
            ], 401);
    }















}
