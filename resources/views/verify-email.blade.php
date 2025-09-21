<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lien de vérification expiré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="card shadow p-4" style="max-width: 480px; width: 100%;">
        <div class="text-center mb-3">
            <h3 class="text-danger">Lien expiré</h3>
            <p class="text-muted">Votre lien de vérification a expiré.</p>
        </div>
        <p class="mb-4 text-center">
            Pas de panique, vous pouvez renvoyer un nouveau lien de vérification à l'adresse suivante :
            <strong>{{ $user->emailU }}</strong>
        </p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status') === 'verification-link-sent')
            <div class="alert alert-success text-center">
                Un nouveau lien de vérification a été envoyé à votre adresse email.
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <input type="hidden" name="emailU" value="{{ $user->emailU }}" />
            <button type="submit" class="btn btn-primary w-100">
                🔁 Renvoyer le mail de vérification
            </button>
        </form>
    </div>
</div>

</body>
</html>
