<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BOIRAPIController extends Controller
{

    public function getToken()
    {
        $clientID = env("FINCEN_CLIENT_SECRET");
        $url = "https://iam.fincen.gov/am/oauth2/realms/root/realms/Finance/access_token";

        // create a http post request with authorization header of Basic $clientID and a request body of `grant_type=client_credentials` and a scope of BOSS-EFILE-SANDBOX and a content type of application/x-www-form-urlencoded
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $clientID,
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->post($url, [
            'grant_type' => 'client_credentials',
            'scope' => 'BOSS-EFILE-SANDBOX'
        ]);

        dd($response->json());
    }

    public function initiateProcess()
    {
        Http::get();
    }
}
