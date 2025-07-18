<?php

namespace App\Controllers\Panel;

use App\config;
use Database\DB;
use PDO;
use PDOException;

class PatternController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "enhancements_patterns",
            reqAccess: 40,
            title: "Enh Pattern",
            viewTemplate: "panel/pattern.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Name" => ["required", "string", "max:32"],
                "Desc" => ["required", "string", "min:2", "max:2"],
                "Wisdom" => ["required", "int", "min:0", "max:999"],
                "Strength" => ["required", "int", "min:0", "max:999"],
                "Luck" => ["required", "int", "min:0", "max:999"],
                "Dexterity" => ["required", "int", "min:0", "max:999"],
                "Endurance" => ["required", "int", "min:0", "max:999"],
                "Intelligence" => ["required", "int", "min:0", "max:999"],
            ],
            viewParams: []
        );
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("enhancements_patterns", "Name", $_POST["Name"]))
        {
            $this->response_json(["msg" => ["Name" => [$_POST["Name"] . " Name has already been taken."]]], 403);
        }

        return $data;
    }

}