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
        return view('home')->with(['test' => 'ah']);
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
        // $request = Request::create('https://bs.moselo.com/api/v5/discover2/get', 'POST');
        // $request->headers->set('API_TOKEN', 'NTIyYjNhMmUyYmZkYjllODMyNTcxNjcwMThmZDA4ZDFlNjMxZDU0ZmY3');
        // $request->headers->set('API_SECRET', 'moselo');
        // $response = app()->handle($request);
        // dd($response);
        $response = $client->request(
                $method,
                'http://127.0.0.1:8000/api/to-do',
                $request
        );

        dd($response);
    }
}
