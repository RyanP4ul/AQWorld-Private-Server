<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class AuraController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "auras",
            reqAccess: 60,
            title: "Auras",
            viewTemplate: "panel/auras.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Name" => ["required", "string", "max:32"],
                "Duration" => ["required", "int", "min:1", "max:100"],
                "Category" => ["required", "string"],
                "Chance" => ["required", "decimal"],
                "DamageIncrease" => ["required", "int"],
                "DamageTakenDecrease" => ["required", "int"],
                "MaxStack" => ["required", "int", "min:1", "max:999"],
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item)
        {
            $skills = [];
            $skillAuras = DB::Connection()->Get("SELECT a.AuraID, a.SkillID, b.Name FROM skills_auras AS a INNER JOIN skills AS b ON a.SkillID = b.id WHERE a.AuraID = :AuraID", [":AuraID", $item["id"], PDO::PARAM_INT]);

            if ($skillAuras != null) {
                foreach ($skillAuras as $skillAura) {
                    $skills[] = "<a class='pl-1' href='#aura-" . $skillAura["AuraID"] . "'>[" . $skillAura["SkillID"] . "] " . $skillAura["Name"] . "</a>";
                }
            }

            $item["Duration"] = $item["Duration"] . " second" . ($item["Duration"] > 1 ? 's' : '');
            $item["Chance"] = $item["Chance"] * 100 . "%";
            $item["Skills"] = count($skills) < 1 ? "None" : $skills;
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("auras", "Name", $_POST["Name"]))
        {
            $this->response_json(["msg" => ["Name" => [$_POST["Name"] . " Name has already been taken."]]], 403);
        }

        return $data;
    }

}