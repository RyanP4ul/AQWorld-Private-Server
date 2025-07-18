<?php

namespace App\Controllers\Panel;

use Database\DB;
use PDO;

class ShopItemController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "shops_items",
            reqAccess: 40,
            title: "Shops Items",
            viewTemplate: "panel/shops.items.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "ShopID" => ["required", "int", "min:1"],
                "ItemID" => ["required", "int", "min:1"],
                "QuantityRemain" => ["required", "int"],
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $shopObj = DB::Connection()->First("SELECT Name FROM shops WHERE id = :Id", [":Id", $item["ShopID"], PDO::PARAM_INT]);
            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $item["ItemID"], PDO::PARAM_INT]);

            $item["ShopID"] = $shopObj == null ? $item["ShopID"] : "[" . $item["ShopID"] . "] " . $shopObj["Name"];
            $item["ItemID"] = $itemObj == null ? $item["ItemID"] : "[" . $item["ItemID"] . "] " . $itemObj["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }


    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM shops WHERE id = :Id", [":Id", $_POST["ShopID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ShopID" => ["ShopID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID does not exists!"]]], 403);
        }

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM shops_items WHERE ShopID = :ShopID AND ItemID = :ItemID", [
                [":ShopID", $_POST["ShopID"], PDO::PARAM_INT],
                [":ItemID", $_POST["ItemID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["ShopID" => ["ShopID and ItemID are already exists."]]], 403);
        }

        return $data;
    }

}