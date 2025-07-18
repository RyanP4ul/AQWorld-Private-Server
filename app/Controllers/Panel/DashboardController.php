<?php

namespace App\Controllers\Panel;

use App\Controllers\Controller;
use Database\DB;
use PDO;

class DashboardController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->isAccessValid(40)) $this->abort(401);
    }

    public function index() : void
    {
        $this->view("panel/dashboard.html.twig", [
            "title" => "Dashboard",
            "totalRegisteredUsers" => DB::Connection()->Count("SELECT COUNT(*) FROM users"),
            "onlineUsers" => DB::Connection()->Count("SELECT COUNT(*) FROM users WHERE CurrentServer != 'Offline'"),
            "peakPlayerToday" => DB::Connection()->Count("SELECT COUNT(*) as total FROM users  WHERE DATE(LastLogin) = :LastLogin", [":LastLogin", date('Y-m-d'), PDO::PARAM_STR]),
            "totalItems" => DB::Connection()->Count("SELECT COUNT(*) FROM items"),
            "totalQuests" => DB::Connection()->Count("SELECT COUNT(*) FROM quests"),
            "totalMonsters" => DB::Connection()->Count("SELECT COUNT(*) FROM monsters"),
            "totalMaps" => DB::Connection()->Count("SELECT COUNT(*) FROM maps"),
            "totalTitles" => DB::Connection()->Count("SELECT COUNT(*) FROM titles"),
            "mostEquipped" => DB::Connection()->Get("SELECT b.Name, COUNT(*) as total_equipped FROM users_items AS a INNER JOIN items AS b ON a.ItemID = b.id WHERE a.Equipped = 1 GROUP BY a.ItemID ORDER BY total_equipped DESC LIMIT 5")
        ]);
    }

}