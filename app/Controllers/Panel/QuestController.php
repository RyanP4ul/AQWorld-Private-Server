<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class QuestController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "quests",
            reqAccess: 40,
            title: "Quests",
            viewTemplate: "panel/quests.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Name" => ["required", "string", "max:32"],
                "Description" => ["required", "string", "max:32"],
                "EndText" => ["required", "string", "max:32"],
                "Experience" => ["required", "int"],
                "Gold" => ["required", "int"],
                "Coins" => ["required", "int"],
                "Reputation" => ["required", "int"],
                "ClassPoints" => ["required", "int"],
                "RewardType" => ["required", "string"],
                "Level" => ["required", "int"],
                "Slot" => ["required", "int"],
                "Value" => ["required", "int"],
                "Field" => ["string"],
                "Index" => ["required", "int"],

                "WarID" => ["null", "int"],
                "AchievementID" => ["null", "int"],
                "TitleID" => ["null", "int"],
                "FactionID" => ["null", "int"],
                "WarMega" => ["int"],
                "ReqReputation" => ["int"],
                "ReqClassID" => ["int"],
                "ReqClassPoints" => ["int"],

                "Upgrade" => ["string", "min:1", "max:2"],
                "Once" => ["string", "min:1", "max:2"],
            ],
            viewParams: []
        );
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("quests", "Name", $_POST["Name"]))
        {
            $this->response_json(["msg" => ["Name" => [$_POST["Name"] . " Name has already been taken."]]], 403);
        }

        if (!empty($_POST["AchievementID"]) && DB::Connection()->Count("SELECT COUNT(*) FROM achievements WHERE id = :Id", [":Id", $_POST["AchievementID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["AchievementID" => ["AchievementID does not exists!"]]], 403);
        }

        if (DB::Connection()->Count("SELECT COUNT(*) FROM factions WHERE id = :Id", [":Id", $_POST["FactionID"], PDO::PARAM_INT]) < 1)
        {
            $this->response_json(["msg" => ["FactionID" => ["FactionID does not exists!"]]], 403);
        }

        return $data;
    }

}