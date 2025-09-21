<?php
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('tls-login');
});

Route::get('/email/verify/{idU}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/telescope-login', function () {
    return view('tls-login');
})->name('telescope.login');

Route::post('/telescope-login', function (Request $request) {
    $request->validate([
        'emailU' => 'required|email',
        'mdpU' => 'required|string|min:8',
    ], [
        'emailU.required' => 'Veuillez saisir votre adresse e-mail.',
        'emailU.email' => 'L\'adresse e-mail n\'est pas valide.',
        'mdpU.required' => 'Veuillez saisir votre mot de passe.',
        'mdpU.string' => 'Le mot de passe doit être une chaîne de caractères.',
        'mdpU.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
    ]);
    $credentials = [
        'emailU' => $request->emailU,
        'password' => $request->mdpU
    ];
    if (auth()->attempt($credentials)) {
        return redirect()->intended('/telescope');
    }

    return back()->withErrors([
        'emailU' => 'Identifiants invalides.',
    ]);
});
Route::get('/password/reset/{token}', function ($token) {
    return redirect("https://afritoit.com/password/reset/$token");
})->name('password.reset');
Route::post('/email/resend', [VerificationController::class, 'resend']) 
    ->middleware(['throttle:6,1'])->name('verification.send');
Route::get('logs/login', function () {
    return view('log-login');
});
Route::post('logs/login', function (Request $request) {
    $request->validate([
        'emailU' => 'required|email',
        'mdpU' => 'required|string|min:8',
    ], [
        'emailU.required' => 'Veuillez saisir votre adresse e-mail.',
        'emailU.email' => 'L\'adresse e-mail n\'est pas valide.',
        'mdpU.required' => 'Veuillez saisir votre mot de passe.',
        'mdpU.string' => 'Le mot de passe doit être une chaîne de caractères.',
        'mdpU.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
    ]);
    $credentials = [
        'emailU' => $request->emailU,
        'password' => $request->mdpU
    ];
    if (auth()->attempt($credentials)) {
        return redirect()->route('logs.index');
    }

    return back()->withErrors([
        'emailU' => 'Identifiants invalides.',
    ]);
})->name('logs.login');
Route::get('xylogs', [LogViewerController::class, 'index'])
    ->middleware(['check.logviewer'])
    ->name('logs.index');
