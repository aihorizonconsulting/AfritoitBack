<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $user = \App\Models\User::findOrFail($request->route('idU'));
        try {

            if ($user->hasVerifiedEmail()) {
                return view('verification-success');
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            return view('verification-success');
        } catch (AuthorizationException $e) {
            Log::error('Email verification error - VerificationController : ' . $e->getMessage());
            return view('verify-email', ['user' => $user]);
        }
        return view('verify-email',['user' => $user]);
    }
    public function resend(Request $request)
    {
        try {
            $request->validate(['emailU' => 'required|email']);

            $user = User::where('emailU', $request->emailU)->first();

            if (!$user) {
                return back()->withErrors(['email' => 'Adresse email inconnue.']);
            }

            if ($user->hasVerifiedEmail()) {
                return back()->with('message', 'Cet email est déjà vérifié.');
            }

            $user->sendEmailVerificationNotification();
            return back()->with('status', 'verification-link-sent');
        } catch (\Exception $e) {
            Log::error('Email verification error - VerificationController : ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()] , 500);
        }
    }
}
