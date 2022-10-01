<?php

namespace App\Http\Controllers;

use App\Services\MicroServiceToken\TokenIssuer;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'validateToken']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        try{
            $credentials = request(['email', 'password']);

            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            return $this->respondWithToken($token);
        }catch(\PDOException $exception){
            return response()->json(["message"=>$exception->getMessage()],500);
        }

    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json($this->accessTokenDataArray($token));
    }


    /**
     * @throws \Exception
     */
    public function validateToken(): JsonResponse
    {
        $authCheck = auth()->check();
        if (!$authCheck) {
            return response()->json(['valid' => false]);
        }
        $microServiceToken = (new TokenIssuer())->issue();
        return response()->json([
            'valid' => true,
            "token" => $this->accessTokenDataArray($microServiceToken)
        ]);

    }

    private function accessTokenDataArray(string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }
}
