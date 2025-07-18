<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class MonsterDropController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "monsters_drops",
            reqAccess: 40,
            title: "Monster Drops",
            viewTemplate: "panel/monsters.drops.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "MonsterID" => ["required", 'int', "min:1"],
                "ItemID" => ["required", 'int', "min:1"],
                "Chance" => ["required", 'decimal', "min:1"],
                "Quantity" => ["required", 'int', "min:1"]
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $monObj = DB::Connection()->First("SELECT Name FROM monsters WHERE id = :Id", [":Id", $item["MonsterID"], PDO::PARAM_INT]);
            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $item["ItemID"], PDO::PARAM_INT]);

            $item["MonsterID"] = $monObj == null ? $item["MonsterID"] : "[" . $item["MonsterID"] . "] " . $monObj["Name"];
            $item["ItemID"] = $itemObj == null ? $item["ItemID"] : "[" . $item["ItemID"] . "] " . $itemObj["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }


    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM monsters WHERE id = :Id", [":Id", $_POST["MonsterID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["MonsterID" => ["MonsterID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID does not exists!"]]], 403);
        }

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM monsters_drops WHERE MonsterID = :MonsterID AND ItemID = :ItemID", [
                [":MonsterID", $_POST["MonsterID"], PDO::PARAM_INT],
                [":ItemID", $_POST["ItemID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["MonsterID" => ["MonsterID and ItemID are already exists."]]], 403);
        }

        return $data;
    }

}