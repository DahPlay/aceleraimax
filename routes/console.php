<?php

declare(strict_types=1);

use App\Services\YouCast\Concerns\YouCastClient;
use App\Services\YouCast\Customer\CustomerAuthenticate;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Motv\Connector\Mw\AdminConnector;

Artisan::command('play', function () {
    // $customer = (new App\Services\YouCast\Customer\CustomerSearch)->handle("teste180");

    // dd($customer["response"]);

    // $customer = (new App\Services\YouCast\Customer\CustomerFind)->handle("21101");

    // dd($customer["response"]);

    // $data = (new CustomerAuthenticate)->handle('teste154', 'teste');

    // dd($data);

    // $customers_token = $data['customers_token'];
    // $profiles_id = $data['profiles'][0]['profiles_id'];
    // $devices_type = 'web player';

    // $base64ProfilesId = base64_encode((string) $profiles_id);
    // $base64DevicesType = base64_encode('web player');

    // $response = Http::withHeaders([
    //     'Authorization' => 'Bearer ' . $customers_token,
    //     'profilesId'    => $base64ProfilesId,
    //     'devicesType'   => $base64DevicesType,
    //     'Accept'        => 'application/json',
    // ])->post('https://mw.dahplay.com.br/public/channel/getSubscribedChannels');

    // $data = $response->json();

    // dd($data);

    $mwAdminConnector = new AdminConnector(
        'https://mw.dahplay.com.br',
        'alexandre@rsggroup.com.br',      // ex: 'admin@agroplus.tv'
        '5v1etq9bipm6col2oldgei4tfdku0571rbg5zyzd'      // fornecido pela moTV/DahPlay
    );

    // Obter dados de um cliente (pelo customers_id do MW, **não** viewers_id do CSMS)
    $customer = $mwAdminConnector->Customer()->getData(121016);
    dd($customer);
});
