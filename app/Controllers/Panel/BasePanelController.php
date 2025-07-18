<?php

namespace App\Controllers\Panel;

use App\config;
use App\Controllers\Controller;
use Database\DB;
use PDO;
use PDOException;

class BasePanelController extends PanelController
{

    protected string $table;
    protected string $title;
    protected string $reqAccess;
    protected array $validationRules;
    protected string $viewTemplate;
    protected array $viewParams = [];

    public function __construct(string $table, int $reqAccess, string $title, string $viewTemplate, array $validationRules, array $viewParams)
    {

        parent::__construct();

        if (!$this->isAccessValid($reqAccess)) $this->abort(401);

        $this->table = $table;
        $this->reqAccess = $reqAccess;
        $this->title = $title;
        $this->viewTemplate = $viewTemplate;
        $this->validationRules = $validationRules;
        $this->viewParams = array_merge([
            "title" => $title,
            "total" => DB::Connection()->Count("SELECT COUNT(*) FROM $table")
        ], $viewParams);

        if (!in_array($this->table, config::WHITE_LIST_TABLES)) $this->abort(404);
    }

    protected function processIndexData(array &$items): void
    {
        foreach ($items as &$item) {
            $item["Action"] = "<button id='btn-edit' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-edit pr-2'></i> Edit</button> <button id='btn-delete' class='btn btn-sm btn-default' data-id='{$item["id"]}'><i class='fa fa-trash pr-2'></i> Delete</button>";
        }
    }

    protected function prepareData(): array
    {
        $data = [];
        foreach ($this->validationRules as $field => $rules) {
            if ($field === "id") {
                $data[] = [":Id", $_POST["id"], PDO::PARAM_INT];
            } else {
                $paramType = in_array("int", $rules) ? PDO::PARAM_INT : PDO::PARAM_STR;

                if (isset($_POST[$field]))
                {
                    if (is_string($_POST[$field]))
                    {
                        $_POST[$field] = preg_replace('/\s+/', ' ', trim($_POST[$field]));
                    }

                    $data[] = [":" . $field, $_POST[$field], $paramType];
                }
                else
                {
                    $data[] = [":" . $field, 0, $paramType];
                }
            }
        }

        if (strtolower($_GET["type"]) === "update") {
            $data[] = [":OldId", $_GET["oldId"], PDO::PARAM_INT];
        }

        return $data;
    }

    protected function executeSaveQuery(string $type, array $data): void
    {
        $fields = array_map(fn($d) => explode(':', $d[0])[1], $data);
        if ($type === "update") {
            array_pop($fields);
        }
        $placeholders = implode(", ", array_map(fn($f) => ":$f", $fields));
        $setClause = implode(", ", array_map(fn($f) => "`$f` = :$f", $fields));

        if ($type === "insert") {
            if(isset($_POST["id"]) && DB::Connection()->Count("SELECT * FROM $this->table WHERE id = :Id", [":Id", $_POST["id"], PDO::PARAM_INT]) > 0)
            {
                $this->response_json(["msg" => ["id" => ["Id " . $_POST["id"] . " is already exists"]]], 403);
            }

            $escapedFields = array_map(function($field) {
                return "`$field`";
            }, $fields);

            $query = "INSERT INTO $this->table (" . implode(", ", $escapedFields) . ") VALUES ($placeholders)";
        } elseif ($type === "update") {
            $query = "UPDATE $this->table SET $setClause WHERE id = :OldId";
        } else {
            $this->abort(404);
        }

        DB::Connection()->Prepare($query, $data);
    }

    public function index(): void
    {
        if ($this->isAjax()) {
            $items = DB::Connection()->Get("SELECT * FROM {$this->table}");
            $this->processIndexData($items);
            $this->response_json(["data" => $items]);
        }

        $this->view($this->viewTemplate, $this->viewParams);
    }

    public function save(): void
    {
        if (!isset($_GET["type"]) || !$this->isAjax()) {
            $this->abort(404);
        }

        header('Content-Type: application/json');

        $validator = $this->validator($this->validationRules);

        if (count($validator) > 0) {
            $this->response_json(["msg" => $validator], 403);
        }

        DB::Connection()->Instance()->beginTransaction();

        try {
            $data = $this->prepareData();
            $type = strtolower($_GET["type"]);
            $this->executeSaveQuery($type, $data);

            DB::Connection()->Instance()->commit();
            $this->response_json(["msg" => ["You successfully " . ($type === "insert" ? "inserted" : "updated")]]);
        } catch (PDOException $PDOException) {
            if (DB::Connection()->Instance()->inTransaction()) {
                DB::Connection()->Instance()->rollBack();
            }
            $this->response_json(["msg" => config::DEBUG ? $PDOException->getMessage() . " | " . $PDOException->getTraceAsString() : "Failed transaction. Please try again."], 403);
        }
    }

    public function edit(): void
    {
        if (!isset($_GET["id"]) || !$this->isAjax()) {
            $this->abort(404);
        }

        header('Content-Type: application/json');
        $this->response_json(DB::Connection()->First("SELECT * FROM $this->table WHERE id = :id", [":id", $_GET["id"], PDO::PARAM_INT]));
    }

    public function delete(): void
    {
        if (!isset($_GET["id"]) || !$this->isAjax()) {
            $this->abort(404);
        }

        header('Content-Type: application/json');

        DB::Connection()->Instance()->beginTransaction();

        try {
            DB::Connection()->Prepare("DELETE FROM {$this->table} WHERE id = :Id", [":Id", $_GET["id"], PDO::PARAM_INT]);
            DB::Connection()->Instance()->commit();
            $this->response_json(["msg" => ["Item deleted successfully!"]]);
        } catch (PDOException $PDOException) {
            if (DB::Connection()->Instance()->inTransaction()) {
                DB::Connection()->Instance()->rollBack();
            }
            $this->response_json(["msg" => [config::DEBUG ? $PDOException->getMessage() : "Failed to delete the item. Please try again."]]);
        }
    }

}