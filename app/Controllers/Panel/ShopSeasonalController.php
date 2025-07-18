<?php

namespace App\Controllers\Panel;

use Database\DB;
use PDO;

class ShopSeasonalController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "shops_seasonal",
            reqAccess: 40,
            title: "Shops Seasonal",
            viewTemplate: "panel/shops.seasonal.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "ShopID" => ["required", "int", "min:1"],
                "EndDate" => ["required", "string"],
            ],
            viewParams: [
                "now" => $this->getDateTime()->format('Y-m-d H:i:s')
            ]
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $shopObj = DB::Connection()->First("SELECT Name FROM shops WHERE id = :Id", [":Id", $item["ShopID"], PDO::PARAM_INT]);

            $item["ShopID"] = $shopObj == null ? $item["ShopID"] : "[" . $item["ShopID"] . "] " . $shopObj["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("shops_seasonal", "ShopID", $_POST["ShopID"]))
        {
            $this->response_json(["msg" => ["ShopID" => [$_POST["ShopID"] . " ShopID has already been taken."]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM shops WHERE id = :Id", [":Id", $_POST["ShopID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ShopID" => ["ShopID does not exists!"]]], 403);
        }

        return $data;
    }

}