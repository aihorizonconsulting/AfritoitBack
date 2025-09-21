<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Auth\Events\Registered;

class ProcessNewUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;
    protected ?string $photoPath;

    public function __construct(array $data, ?string $photoPath = null)
    {
        $this->data = $data;
        $this->photoPath = $photoPath;
    }

    public function handle(): void
    {
        $utilisateur = new User();
        $utilisateur->fill(collect($this->data)->except(['urlPhotoU', 'mdpU'])->toArray());

        if ($this->photoPath) {
            $utilisateur->urlPhotoU = $this->photoPath;
        }

        $utilisateur->mdpU = bcrypt($this->data['mdpU']);
        $utilisateur->save();

        event(new Registered($utilisateur));
    }
}
