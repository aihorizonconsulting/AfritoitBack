<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

/**
 * @OA\Info(
 *     title="API AfriToit - Gestion des logements",
 *     version="1.0.0",
 *     description="Documentation de l'API pour la gestion des logements",
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Serveur local",
 * 
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Entrer le token d'accès sous la forme Bearer {token}",
 *     name="Authorization",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="bearerAuth",
 * )
 */
class SwaggerController extends Controller
{
}
