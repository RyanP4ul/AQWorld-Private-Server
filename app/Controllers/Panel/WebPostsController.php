<?php

namespace App\Controllers\Panel;

use App\Controllers\AuthController;
use Database\DB;
use PDO;

class WebPostsController extends BasePanelController
{

    public function __construct()
    {
        parent::__construct(
            table: "web_posts",
            reqAccess: 40,
            title: "Web Posts",
            viewTemplate: "panel/web.posts.html.twig",
            validationRules: [
                "id" => ["required", 'int', "min:1"],
                "Title" => ["required", "string", "max:32"],
                "Content" => ["required", "string", "min:5"],
                "Image" => ["required", "string", "min:5"],
            ],
            viewParams: []
        );
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item)
        {
            $user = DB::Connection()->First("SELECT Name FROM users WHERE id = :Id", [":Id", $item["UserID"], PDO::PARAM_INT]);

            $item["Username"] = $user == null ? "Unknown" : $user["Name"];
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='" . $item["id"] . "'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    protected function prepareData(): array
    {
        $data = parent::prepareData();

        if ($_GET["type"] != "update" && DB::Connection()->Exists("web_posts", "Title", $_POST["Title"]))
        {
            $this->response_json(["msg" => ["Title" => [$_POST["Title"] . " Title has already been taken."]]], 403);
        }

        $data[] = [":UserID", AuthController::info()["id"], PDO::PARAM_INT];

        return $data;
    }

}