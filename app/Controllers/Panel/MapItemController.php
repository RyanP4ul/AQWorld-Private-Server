<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class MapItemController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "maps_items",
            reqAccess: 40,
            title: "Map Items",
            viewTemplate: "panel/maps.items.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "MapID" => ["required", 'int', "min:1"],
                "ItemID" => ["required", "int", "min:1"]
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $mapObj = DB::Connection()->First("SELECT Name FROM maps WHERE id = :Id", [":Id", $item["MapID"], PDO::PARAM_INT]);
            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $item["ItemID"], PDO::PARAM_INT]);

            $item["MapID"] = $mapObj == null ? $item["MapID"] : "[" . $item["MapID"] . "] " . $mapObj["Name"];
            $item["ItemID"] = $itemObj == null ? $item["ItemID"] : "[" . $item["ItemID"] . "] " . $itemObj["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }


    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM maps WHERE id = :Id", [":Id", $_POST["MapID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["MapID" => ["MapID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID does not exists!"]]], 403);
        }

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM maps_items WHERE ItemID = :ItemID AND MapID = :MapID", [
                [":ItemID", $_POST["ItemID"], PDO::PARAM_INT],
                [":MapID", $_POST["MapID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["MapID" => ["ItemID and MapID are already exists."]]], 403);
        }

        return $data;
    }

}