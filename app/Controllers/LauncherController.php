<?php

namespace App\Controllers;

class LauncherController extends Controller
{

    public function index() : void
    {
        $this->view("launcher.html.twig");
    }

}