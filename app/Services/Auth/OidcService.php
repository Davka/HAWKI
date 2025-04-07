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

    /**
     * Startet die Weiterleitung zum Identity Provider (IDP)
     */
    public function startAuthentication()
    {
        $this->oidc->authenticate();

        $userInfo = $this->oidc->requestUserInfo();

        dd($userInfo);
        exit;
    }

    public function handleCallback(): array
    {
        $userInfo = $this->oidc->requestUserInfo();

        dd($userInfo);
        $firstNameAttr = config('open_id_connect.attribute_map.firstname');
        $lastNameAttr = config('open_id_connect.attribute_map.lastname');
        $emailAttr = config('open_id_connect.attribute_map.email');
        $employeetypeAttr = config('open_id_connect.attribute_map.employeetype');

        $email = $this->oidc->requestUserInfo($emailAttr);
        $employeetype = $this->oidc->requestUserInfo($employeetypeAttr);
        $firstname = $this->oidc->requestUserInfo($firstNameAttr);
        $surname = $this->oidc->requestUserInfo($lastNameAttr);
        $name = trim("$firstname $surname");

        if (!empty($_SERVER['REMOTE_USER'])) {
            return [
                'username' => $_SERVER['REMOTE_USER'],
                'name' => $name,
                'email' => $email,
                'employeetype' => $employeetype,
            ];
        }

        throw new \RuntimeException('REMOTE_USER is not set.');
    }
}
