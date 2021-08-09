<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $accessToken = Auth::user()->access_token;
        $list = self::callApiList($accessToken);
        $reminders = self::callApiReminder($accessToken);

        return view('home')->with([
            'user_name' => Auth::user()->name,
            'reminders' => $reminders,
            'items' => $list->data->list
        ]);
    }

    public function callApiList($accessToken) {
        $baseurl = env('API_BASEURL');
        $endpoint = '/api/to-do';
        $method = 'GET';
        $url = $baseurl . $endpoint;

        $headers = [
            'Authorization' => base64_encode($accessToken)
        ];

        $client = new Client([
                'http_errors' => false,
                'verify' => false,
                'timeout' => 5
        ]);

        $request = [
            'headers' => $headers
        ];

        $response = $client->request(
                $method,
                $url,
                $request
        );

        $result = json_decode($response->getBody()->getContents());
        return $result;
    }

    public function callApiReminder($accessToken) {
        $baseurl = env('API_BASEURL');
        $endpoint = '/api/reminder';
        $method = 'GET';
        $url = $baseurl . $endpoint;

        $headers = [
            'Authorization' => base64_encode($accessToken)
        ];

        $client = new Client([
                'http_errors' => false,
                'verify' => false,
                'timeout' => 5
        ]);

        $request = [
            'headers' => $headers
        ];

        $response = $client->request(
                $method,
                $url,
                $request
        );

        $result = json_decode($response->getBody()->getContents());
        return $result;
    }
}
