<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function home(){
        $data = 100;
        return response()->json(compact('data'));
    }

}
