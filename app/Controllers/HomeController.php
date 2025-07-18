<?php

namespace App\Controllers;

use Database\DB;
use PDO;

class HomeController extends Controller
{

    public function index() : void
    {
        if ($this->isAjax() && isset($_GET["page"], $_GET["limit"]))
        {
            $page = $_GET["page"];
            $limit = $_GET["limit"];
            $offset = ($page - 1) * $limit;

            $total = DB::Connection()->Get("SELECT COUNT(*) FROM web_posts");
            $webPosts = DB::Connection()->Get("SELECT a.Title, a.Content, a.Image, a.CreatedDate, b.Name FROM web_posts as a INNER JOIN users as B ON a.UserID = b.id LIMIT :Offset, :Limit", [
                [":Offset", $offset, PDO::PARAM_INT],
                [":Limit", $limit, PDO::PARAM_INT]
            ]);

            $this->response_json([
                "posts" => $webPosts,
                "total" => $total,
                "page" => $page,
                "limit" => $limit
            ]);
        }

        $this->view('home.html.twig');
    }

}