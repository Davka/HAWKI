<?php

namespace App\Services\Auth;

use Jumbojett\OpenIDConnectClient;
use Illuminate\Support\Facades\Log;

class OidcService
{
    protected $oidc;

    public function __construct()
    {
        $idp = config('open_id_connect.oidc_idp');
        $clientId = config('open_id_connect.oidc_client_id');
        $clientSecret = config('open_id_connect.oidc_client_secret');

        if (empty($idp) || empty($clientId) || empty($clientSecret)) {
            throw new \InvalidArgumentException('OIDC configuration variables are not set properly.');
        }

        $this->oidc = new OpenIDConnectClient($idp, $clientId, $clientSecret);
        $this->oidc->setRedirectURL(route('oidc.callback'));

        $scopes = config('open_id_connect.oidc_scopes');
        $this->oidc->addScope($scopes);
    }

    public function handleCallback(): array
    {
        $this->oidc->authenticate();

        $userInfo = $this->oidc->requestUserInfo();
        return [
            'username' => $userInfo['preferred_username'],
            'name' => $userInfo['name'],
            'email' => $userInfo['email'],
            'employeetype' => '',
        ];
    }
}
