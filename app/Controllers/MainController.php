<?php

namespace App\Controllers;

use App\config;
use Database\DB;

class MainController extends Controller
{

    public function get_next_id() : void
    {
        if (!$this->isAjax() || !isset($_GET["t"]) || !in_array($_GET["t"], config::WHITE_LIST_TABLES)) $this->abort(404);

        header('Content-Type: application/json');

        $tableName = trim($_GET["t"]);
        $result = DB::Connection()->First("SHOW TABLE STATUS LIKE '$tableName'");

        $this->response_json($result["Auto_increment"] == null ? 1 :  $result["Auto_increment"]);
    }

}