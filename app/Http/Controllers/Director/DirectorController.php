<?php

namespace App\Http\Controllers\Director;

use App\Http\Controllers\Controller;
use App\Models\Director;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DirectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $director = Director::
                latest()
                ->paginate(10);
            return response()->json([
                'message' => 'All Directors',
                'data' => $director
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
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
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'father_name' => 'required|string',
            'mother_name' => 'required|string',
            'gender_id' => 'required|integer|exists:genders,id',
            'description' => 'required|string',
            'joining_data' => 'required|date_format:d/m/Y',
            'present_address' => 'required|string',
            'permanent_address' => 'required|string',
            'date_of_birth' => 'required|date_format:d/m/Y',
            'national_id' => 'required|string',
            'religion_id' => 'required|integer|exists:regions,id',
            'blood_group_id' => 'required|integer|exists:blood_groups,id',
            'education_id' => 'required|integer|exists:education,id',
            'marital_id' => 'required|integer|exists:marital_statuses,id',
            'email' => 'required|email|unique:directors,email',
            'phone' => 'required|string|unique:directors,phone',

        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Fix date formatting
        $request->merge([
            'joining_data' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->joining_data)->format('Y-m-d'),
            'date_of_birth' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d'),
        ]);

        // Save Director
        $director = Director::create($request->all());

        return response()->json([
            'message' => 'Successfully created employee',
            'data' => $director
        ], 201);
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
    public function update(Request $request, $director_id)
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Find the existing Director
    $director = Director::find($director_id);
    if (!$director) {
        return response()->json(['error' => 'Director not found'], 404);
    }

    // Validation Rules
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'father_name' => 'required|string',
        'mother_name' => 'required|string',
        'gender_id' => 'required|integer|exists:genders,id',
        'description' => 'required|string',
        'joining_data' => 'required|date_format:d/m/Y',
        'present_address' => 'required|string',
        'permanent_address' => 'required|string',
        'date_of_birth' => 'required|date_format:d/m/Y',
        'national_id' => 'required|string',
        'religion_id' => 'required|integer|exists:regions,id',
        'blood_group_id' => 'required|integer|exists:blood_groups,id',
        'education_id' => 'required|integer|exists:education,id',
        'marital_id' => 'required|integer|exists:marital_statuses,id',
        'email' => 'required|email|unique:directors,email,' . $director_id,
        'phone' => 'required|string|unique:directors,phone,' . $director_id,

    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    // Fix date formatting
    $updatedData = $request->all();

    if ($request->has('joining_data')) {
        $updatedData['joining_data'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->joining_data)->format('Y-m-d');
    }

    if ($request->has('date_of_birth')) {
        $updatedData['date_of_birth'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date_of_birth)->format('Y-m-d');
    }

    // Update the Director
    $director->update($updatedData);

    return response()->json([
        'message' => 'Director details updated successfully',
        'data' => $director
    ], 200);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($director_id)
    {
        if (Auth::check()) {
            $director = Director::find($director_id);

            if ($director) {
                $director->delete();
                return response()->json([
                    'message' => 'Director deleted successfully'
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Director not found'
                ], 404);
            }
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }

    public function eyeViewShowDetails($director_id)
    {
        if (Auth::check()) {
            $director = Director::with(['education', 'bloodGroup', 'religion', 'gender', 'maritalStatus'])
                ->where('id', $director_id)
                ->first();

            if ($director) {
                return response()->json([
                    'message' => 'Director Details',
                    'data' => $director
                ], 200);
            } else {
                return response()->json([
                    'error' => 'Director not found'
                ], 404);
            }
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }
    }


    public function allSearch(Request $request){
        if (Auth::check()) {
            $search = $request->input('search');

            $directors = Director::with(['designation'])
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('phone', 'like', '%'.$search.'%')
                ->latest()
                ->paginate(10);

            return response()->json([
                'data' => $directors
            ], 200);
        } else {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

    }










}
