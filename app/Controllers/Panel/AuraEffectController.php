<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class AuraEffectController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "auras_effects",
            reqAccess: 60,
            title: "Auras Effects",
            viewTemplate: "panel/effects.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "AuraID" => ["required", "int", "max:1"],
                "Stat" => ["required", "string", "min:1", "max:4"],
                "Value" => ["required", "decimal"],
                "Type" => ["required", "string", "min:1", "max:1"]
            ],
            viewParams: [
                "stats" => ["tha", "tdo", "thi", "tcr", "ap", "mp", "cao", "cai"],
                "auras" => DB::Connection()->Get("SELECT id, Name FROM auras")
            ]
        );
    }

    private function getAuras() : array
    {
        $auraArr = [];
        $auras = DB::Connection()->Get("SELECT id, Name FROM auras");

//        foreach ($auras as $aura)
//        {
//            $auraObj = new \stdClass();
//            $auraObj->id =
//            $auraArr[] = ;
//        }

        return $auraArr;
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $aura = DB::Connection()->First("SELECT Name FROM auras WHERE id = :Id", [":Id", $item["AuraID"], PDO::PARAM_INT]);

            $item["AuraID"] = $aura == null ? $item["AuraID"] : "[" . $item["AuraID"] . "] " . $aura["Name"];
            $item["Stat"] = $this->getStatFullName($item["Stat"]);
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    private function getStatFullName(string $stat) : string
    {
        return match ($stat) {
            "tha" => "[$stat] Haste",
            "tdo" => "[$stat] Evasion",
            "thi" => "[$stat] Hit",
            "tcr" => "[$stat] Critical Chance",
            "ap" => "[$stat] Attack Power",
            "mp" => "[$stat] Magic Power",
            "cai" => "[$stat] Damage Resistance",
            "cao" => "[$stat] Damage Boosts",
            default => "None",
        };

    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM auras WHERE id = :Id", [":Id", $_POST["AuraID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["AuraID" => ["AuraID does not exists!"]]], 403);
        }

        if ($_GET["type"] == "insert" && DB::Connection()->Count("SELECT COUNT(*) FROM auras_effects WHERE AuraID = :AuraID AND Stat = :Stat", [[":AuraID", $_POST["AuraID"], PDO::PARAM_INT], [":Stat", $_POST["Stat"], PDO::PARAM_INT]]) > 0)
        {
            $this->response_json(["msg" => ["AuraID" => ["AuraID and Stat are already exists."]]], 403);
        }

        return $data;
    }

}