<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class ItemReqController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "items_requirements",
            reqAccess: 40,
            title: "Item Req",
            viewTemplate: "panel/items.requirements.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "ItemID" => ["required", "int"],
                "ReqItemID" => ["required", "int"],
                "Quantity" => ["required", "int"]
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $item["ItemID"], PDO::PARAM_INT]);
            $reqItemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $item["ReqItemID"], PDO::PARAM_INT]);

            $item["ItemID"] = $itemObj == null ? $item["ItemID"] : "[" . $item["ItemID"] . "] " . $itemObj["Name"];
            $item["ReqItemID"] = $reqItemObj == null ? $item["ReqItemID"] : "[" . $item["ReqItemID"] . "] " . $reqItemObj["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ReqItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ReqItemID" => ["ReqItemID does not exists!"]]], 403);
        }

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM items_requirements WHERE ItemID = :ItemID AND ReqItemID = :ReqItemID", [
            [":ItemID", $_POST["ItemID"], PDO::PARAM_INT],
            [":ReqItemID", $_POST["ReqItemID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID and ReqItemID are already exists."]]], 403);
        }

        return $data;
    }

}