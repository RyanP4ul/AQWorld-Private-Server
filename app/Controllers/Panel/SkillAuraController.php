<?php

namespace App\Controllers\Panel;

use Database\DB;
use PDO;

class SkillAuraController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "skills_auras",
            reqAccess: 60,
            title: "Skill Auras",
            viewTemplate: "panel/skills.auras.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "SkillID" => ["required", "int", "min:1"],
                "AuraID" => ["required", "int", "min:1"]
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item)
        {
            $skill = DB::Connection()->First("SELECT Name FROM skills WHERE id = :Id", [":Id", $item["SkillID"], PDO::PARAM_INT]);
            $auraObj = DB::Connection()->First("SELECT Name FROM auras WHERE id = :Id", [":Id", $item["AuraID"], PDO::PARAM_INT]);

            $item["SkillID"] = $item["SkillID"] . " [" . $skill["Name"] . "]";
            $item["AuraID"] = $item["AuraID"] . " [" . $auraObj["Name"] . "]";
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if (DB::Connection()->Count("SELECT COUNT(*) FROM skills WHERE id = :Id", [":Id", $_POST["SkillID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["SkillID" => ["SkillID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM auras WHERE id = :Id", [":Id", $_POST["AuraID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["AuraID" => ["AuraID does not exists!"]]], 403);
        }

        if ($_GET["type"] != "update" && DB::Connection()->Count("SELECT COUNT(*) FROM skills_auras WHERE SkillID = :SkillID AND AuraID = :AuraID", [
                [":SkillID", $_POST["SkillID"], PDO::PARAM_INT],
                [":AuraID", $_POST["AuraID"], PDO::PARAM_INT]
            ]) > 0)
        {
            $this->response_json(["msg" => ["SkillID" => ["SkillID and AuraID are already exists."]]], 403);
        }

        return $data;
    }

}