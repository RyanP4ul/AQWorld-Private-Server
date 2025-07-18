<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class MonsterBossController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "monsters_bosses",
            reqAccess: 60,
            title: "Monster Bosses",
            viewTemplate: "panel/monsters.bosses.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "MonsterID" => ["required", 'int', "min:1"],
                "MapID" => ["required", 'int'],
                "SpawnInterval" => ["required", 'int', "min:1"],
                "Description" => ["required", 'string']
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $mapObj = DB::Connection()->First("SELECT Name FROM maps WHERE id = :Id", [":Id", $item["MapID"], PDO::PARAM_INT]);
            $monObj = DB::Connection()->First("SELECT Name FROM monsters WHERE id = :Id", [":Id", $item["MonsterID"], PDO::PARAM_INT]);

            $item["MapID"] = $mapObj == null ? $item["MapID"] : "[" . $item["MapID"] . "] " . $mapObj["Name"];
            $item["MonsterID"] = $monObj == null ? $item["MonsterID"] : "[" . $item["MonsterID"] . "] " . $monObj["Name"];
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

        if (DB::Connection()->Count("SELECT COUNT(*) FROM monsters WHERE id = :Id", [":Id", $_POST["MonsterID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["MonsterID" => ["MonsterID does not exists!"]]], 403);
        }

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM monsters_bosses WHERE MonsterID = :MonsterID AND MapID = :MapID", [
                [":MonsterID", $_POST["MonsterID"], PDO::PARAM_INT],
                [":MapID", $_POST["MapID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["MapID" => ["MonsterID and MapID are already exists."]]], 403);
        }

        return $data;
    }

}