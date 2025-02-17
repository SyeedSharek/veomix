<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

use Validator;

class AuthController extends Controller
{


    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register() {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        // return $this->sendResponse($user, 'User register successfully.');
        return response()->json([
            'message' => 'User register successfully.',
            'data' => $user
        ], 201);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            // return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
            return response()->json([
                'error' => 'Credentials not valid'
            ], 401);
        }

        $success = $this->respondWithToken($token);

        // return $this->sendResponse($success, 'User login successfully.');
        return response()->json([
            'token' => $token,
            'user' => auth()->user()
        ], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $success = auth()->user();

        // return $this->sendResponse($success, 'Refresh token return successfully.');
        return response()->json([
            'user' => auth()->user()
        ], 200);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        // return $this->sendResponse([], 'Successfully logged out.');
        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $success = $this->respondWithToken(auth()->refresh());

        // return $this->sendResponse($success, 'Refresh token return successfully.');
        return response()->json([
            'token' => $token,
            'user' => auth()->user()
        ], 200);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }

}
