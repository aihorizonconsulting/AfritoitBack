<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessNewUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Enregistrer un nouvel utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nomU","prenomU","emailU","mdpU"},
     *             @OA\Property(property="nomU", type="string", example="Doe"),
     *             @OA\Property(property="prenomU", type="string", example="John"),
     *             @OA\Property(property="emailU", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="mdpU", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé. Vérifiez votre email.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte créé. Vérifiez votre email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de l'enregistrement",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Erreur lors de l'enregistrement"),
     *             @OA\Property(property="message", type="string", example="Détails de l'erreur")
     *         )
     *     )
     * )
     *
     * @OA\Post(
     *     path="/api/login",
     *     summary="Connexion utilisateur",
     *     tags={"Authentification"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"emailU","mdpU"},
     *             @OA\Property(property="emailU", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="mdpU", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Identifiants invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Identifiants invalides")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Adresse email non vérifiée",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Adresse email non vérifiée")
     *         )
     *     )
     * )
     *
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Rafraîchir le token JWT",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Nouveau token généré",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token non rafraîchissable",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Token non rafraîchissable")
     *         )
     *     )
     * )
     *
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Déconnexion utilisateur",
     *     tags={"Authentification"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur lors de la déconnexion",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Détails de l'erreur")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        try {
            DB::beginTransaction();

            $existingUser = User::where('emailU', $request->emailU)->first();

            if ($existingUser) {
                if ($existingUser->hasVerifiedEmail()) {
                    throw ValidationException::withMessages([
                        'emailU' => 'Cet email est déjà utilisé.',
                    ]);
                } else {
                    // Renvoyer juste l'email de vérification via un job
                    ProcessNewUser::dispatch(
                        $existingUser->toArray(),
                        null
                    )->afterCommit();

                    DB::commit();
                    return response()->json([
                        'message' => 'Ce compte existe déjà mais n\'a pas encore été vérifié. Un nouvel email de vérification a été envoyé.',
                    ], 200);
                }
            }

            // Création via job → photo incluse si présente
            $photoPath = null;
            if ($request->hasFile('urlPhotoU')) {
                $photo = $request->file('urlPhotoU');
                $prefix = 'profil_' . time() . '_';
                $filename = $prefix . $photo->getClientOriginalName();
                $photoPath = $photo->storeAs('imgsProfils', $filename, 'public');
            } else {
                $photoPath = null;
            }
            $data = $request->except(['urlPhotoU']); 
            ProcessNewUser::dispatch(
                $data,
                $photoPath 
            )->afterCommit();

            DB::commit();

            return response()->json([
                'message' => 'Compte créé avec succès. Veuillez vérifier votre email pour activer votre compte.'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registering user - AuthController : ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors de l\'enregistrement'
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = [
                'emailU' => $request->emailU,
                'password' => $request->mdpU
            ];

            if (!$token = auth('api')->attempt($credentials)) {
                return response()->json(['error' => 'Identifiants invalides'], 401);
            }

            $user = auth('api')->user();

            if (!$user->hasVerifiedEmail()) {
                return response()->json(['error' => 'Adresse email non vérifiée'], 403);
            }

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            Log::error('Error logging in - AuthController : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la connexion'], 500);
        }
    }

    public function refresh()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            return $this->respondWithToken($newToken);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['error' => 'Token non rafraîchissable'], 401);
        }
    }

    public function logout()
    {
        try {
            auth('api')->logout();
            return response()->json(['message' => 'Déconnexion réussie']);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            Log::error('Error logging out - AuthController : ' . $e->getMessage());
            return response()->json(['error' => "erreur lors de la deconnexion"], 500);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
    public function sendResetLinkEmail(Request $request)
    {
        try {
            $request->validate(['emailU' => 'required|email']);

            $status = Password::sendResetLink(
                ['emailU' => $request->emailU]
            );

            if ($status === Password::RESET_LINK_SENT)
                return response()->json(['message' => 'Email de réinitialisation envoyé.']);
        } catch (\Exception $th) {
            Log::error('Error sending reset link - AuthController : ' . $th->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'envoi de l\'email de réinitialisation'], 500);
        }
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'emailU' => 'required|email',
            'token' => 'required',
            'mdpU' => 'required|confirmed|min:6',
            'mdpU_confirmation' => 'required|min:6',
        ]);
        try {

            $status = Password::reset(
                [
                    'emailU' => $request->emailU,
                    'password' => $request->mdpU,
                    'password_confirmation' => $request->mdpU_confirmation,
                    'token' => $request->token,
                ],
                function ($user, $password) {
                    $user->mdpU = Hash::make($password);
                    $user->save();
                }
            );
            if ($status !== Password::PASSWORD_RESET) {
                return response()->json(['error' => 'Erreur  de la réinitialisation du mot de passe', 'status' => $status], 500);
            }
            return response()->json(['message' => 'Mot de passe réinitialisé.']);
        } catch (\Exception $e) {
            Log::error('Error resetting password - AuthController : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la réinitialisation du mot de passe'], 500);
        }
    }
}
