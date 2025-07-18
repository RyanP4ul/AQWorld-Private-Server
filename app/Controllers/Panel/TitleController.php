<?php

namespace App\Controllers\Panel;

use Database\DB;

class TitleController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "titles",
            reqAccess: 60,
            title: "Titles",
            viewTemplate: "panel/titles.html.twig",
            validationRules: [
                "id" => ["required", "int", "min:1"],
                "Name" => ["required", "string", "min:1", "max:55"],
                "Description" => ["required", "string", "min:1", "max:20"],
                "Strength" => ["required", "int", "min:1"],
                "Intellect" => ["required", "int", "min:1"],
                "Endurance" => ["required", "int", "min:1"],
                "Dexterity" => ["required", "int", "min:1"],
                "Wisdom" => ["required", "int", "min:1"],
                "Luck" => ["required", "int", "min:1"],
                "Color" => ["required", "string", "min:1", "max:55"]
            ],
            viewParams: []
        );
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("titles", "Name", $_POST["Name"]))
        {
            $this->response_json(["msg" => ["Name" => [$_POST["Name"] . " Name has already been taken."]]], 403);
        }

        return $data;
    }


}