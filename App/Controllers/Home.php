<?php

namespace App\Controllers;

class Home extends Controller
{
    public function index()
    {
        $this->view('home/index');
    }
}