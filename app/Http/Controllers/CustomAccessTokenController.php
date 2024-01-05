<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Psr\Http\Message\ServerRequestInterface;
use App\User;
class CustomAccessTokenController extends AccessTokenController
{
    public function login(ServerRequestInterface $request)
    {
        $response = parent::issueToken($request);
        // Convert a PSR-7 response to a Laravel response

        $response = json_decode($response->getContent(), true);

        if (isset($response['access_token'])) {
            // Obtenha o usuário com base no e-mail
            $user = User::where('email', $request->getParsedBody()['username'])->first();

            // Adicione o ID do usuário à resposta
            $response['user_id'] = $user->id;
        }

        return response()->json($response);
    }
}