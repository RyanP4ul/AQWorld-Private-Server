<?php

namespace App\Controllers;

use Database\DB;
use PDO;
use stdClass;

class StoreController extends Controller
{

    public function index() : void
    {
        if ($this->isAjax())
        {
            $page = $_GET["page"];
            $limit = $_GET["limit"];
            $offset = ($page - 1) * $limit;

            $total = DB::Connection()->Get("SELECT COUNT(*) FROM stores");
            $items = DB::Connection()->Get("SELECT * FROM stores LIMIT :Offset, :Limit", [
                [":Offset", $offset, PDO::PARAM_INT],
                [":Limit", $limit, PDO::PARAM_INT]
            ]);

            $this->response_json([
                "items" => $items,
                "total" => $total,
                "page" => $page,
                "limit" => $limit
            ]);
        }

        $this->view("store.html.twig", [
            "categories" => [
                "All",
                "Special",
                "Pack"
            ]
        ]);
    }

    public function checkout() : void {
        if (!isset($_GET["id"]))
        {
            $this->abort(404);
        }

        $store = DB::Connection()->First("SELECT * FROM stores WHERE id = :Id", [":Id", $_GET["id"], PDO::PARAM_INT]);

        if ($store == null) $this->abort(404);

        $items = [];

        foreach (explode(",", $store["Items"]) as $item)
        {
            $itemSplit = explode(":", $item);
            $itemId = $itemSplit[0];
            $qty =  $itemSplit[1];

            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $itemId, PDO::PARAM_INT]);

            if ($itemObj == null) continue;

            $obj = new stdClass();
            $obj->Name = $itemObj["Name"];
            $obj->Quantity = $qty;

            $items[] = $obj;
        }

        $this->view("store.checkout.html.twig", [
            "store" => $store,
            "items" => $items
        ]);
    }

}