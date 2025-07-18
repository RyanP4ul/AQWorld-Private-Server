<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class ClassesController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "classes",
            reqAccess: 60,
            title: "Classes",
            viewTemplate: "panel/classes.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "ItemID" => ["required", "int", "min:1"],
                "Category" => ["required", "string", "min:1", "max:5"],
                "Description" => ["required", "string"],
                "ManaRegenerationMethods" => ["required", "string"],
                "StatsDescription" => ["required", "string"]
            ],
            viewParams: [
                "categories" => ["M1", "M2", "M3", "M4", "C1", "C2", "C3", "S1"]
            ]
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item)
        {
            $itemObj = DB::Connection()->First("SELECT Name FROM items WHERE id = :Id", [":Id", $item["ItemID"], PDO::PARAM_INT]);

            $item["ItemID"] = $itemObj == null ? $item["ItemID"] : "[" . $item["ItemID"] . "] " . $itemObj["Name"];
            $item["Category"] = $this->getCategory($item["Category"]);
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    private function getCategory(string $cat) : string
    {
        switch ($cat)
        {
            case "M1": return "[$cat] Tank Melee";
            case "M2": return "[$cat] Dodge Melee";
            case "M3": return "[$cat] Full Hybrid";
            case "M4": return "[$cat] Power Melee";
            case "C1": return "[$cat] Offensive Caster";
            case "C2": return "[$cat] Defensive Caster";
            case "C3": return "[$cat] Power Caster";
            case "S1": return "[$cat] Luck Hybrid";
            default: return "[$cat] Adventurer";
        }
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM items WHERE id = :Id", [":Id", $_POST["ItemID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["ItemID" => ["ItemID does not exists!"]]], 403);
        }

        if ($_GET["type"] == "insert" && DB::Connection()->First("SELECT * FROM classes WHERE ItemID = :ItemID AND Category = :Category", [
            [":ItemID", $_POST["ItemID"], PDO::PARAM_INT],
                    [":Category", $_POST["Category"], PDO::PARAM_INT],]
            ))
        {
            $this->response_json(["msg" => ["ItemID" => ["Same ItemID and Category are not allowed!"]]], 403);
        }

        return $data;
    }

}