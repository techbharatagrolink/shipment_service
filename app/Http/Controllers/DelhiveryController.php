<?php

namespace App\Http\Controllers;

use App\Services\DelhiveryService;
use Illuminate\Http\Request;

class DelhiveryController extends Controller
{
    public DelhiveryService $delhiveryService;

    public function __construct(DelhiveryService $delhiveryService){
        $this->delhiveryService = $delhiveryService;
    }

    public function index(Request $request){
        $data = $request->all();
        dd($data);
        $response = $this->delhiveryService->serviceability($data);
        if($response->successful()){
            return response()->json($response, 200);
        }
        return response()->json($response, 400);
    }
}
