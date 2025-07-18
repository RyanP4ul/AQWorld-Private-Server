<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class MapMonsterController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "maps_monsters",
            reqAccess: 40,
            title: "Map Monsters",
            viewTemplate: "panel/maps.monsters.html.twig",
            validationRules: [
                "id" => ["required", 'int'],
                "MapID" => ["required", 'int'],
                "MonsterID" => ["required", 'int'],
                "MonMapID" => ["required", 'int'],
                "Frame" => ["required", "string"],
                "Aggresive" => ["string", "min:1", "max:2"],
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

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM maps_monsters WHERE MonsterID = :MonsterID AND MapID = :MapID AND MonMapID = :MonMapID", [
                [":MonsterID", $_POST["MonsterID"], PDO::PARAM_INT],
                [":MapID", $_POST["MapID"], PDO::PARAM_INT],
                [":MonMapID", $_POST["MonMapID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["MapID" => ["MapID and MonsterID and MonMapID are already exists."]]], 403);
        }

        return $data;
    }

}