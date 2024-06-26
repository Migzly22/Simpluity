<?php
namespace Simpluity\Simpluity\Utils;

class BaseController {

    public $model;
    public $pdo;
    public $paranoid;
    public $pagination;
    public $page_number = 1;
    public $page_limit = 20;


    public function __construct($model, $pdo,  $pagination = false, $paranoid = false) {
        $this->model = $model;
        $this->pdo = $pdo;
        $this->pagination = $pagination;
        $this->paranoid = $paranoid;
    }

    // jsonData = json
    // columns = array
    // condition = string
    // associates = array [tablename, INNER || LEFT || RIGHT, condition, columns ]
    // order = array

    public function GET($condition = null, $order = null) {
        try {
            $currentpage = $this->page_limit * ($this->page_number - 1);
            $limit = $this->page_limit;

            $where = $condition ? "AND $condition": "";
            $orderby = $order ? "ORDER BY ".implode(",", $order) : "";
            $pages = $this->pagination ? "LIMIT $limit OFFSET $currentpage" : "";

            $sqlquery = "SELECT * FROM ".$this->model." WHERE deletedAt IS NULL $where $orderby $pages";
            $stmt = $this->pdo->prepare($sqlquery);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function GET_withASSOCIATES( $associates = null, $condition = null, $order = null) {
        try {
            $currentpage = $this->page_limit * ($this->page_number - 1);
            $limit = $this->page_limit;

            $where = $condition ? "AND $condition": "";
            $orderby = $order ? "ORDER BY ".implode(",", $order) : "";
            $pages = $this->pagination ? "LIMIT $limit OFFSET $currentpage" : "";

            $columns = $associates ? array_map( function($value){
                $targettable = $value[0];
                if (isset($value[3])) {
                    $value[3] = ["*"];
                }
                $columnlist = array_map( function($value) use ($targettable){
                    return "$targettable.$value";
                } , $value[3]);
                return implode(" ,", $columnlist);
            } , $associates): "";

            $assocTableData = $associates ? array_map( function($value){
                $typeofJoin = $value[1];
                $targettable = $value[0];
                $condition = $value[2];
                return "$typeofJoin JOIN $targettable $targettable ON $condition";
            } , $associates): "";

            $select = $columns ? "model.* ,".implode(",", $columns) : "model.*";

            $associateTable = $associates ? $this->model." model ".implode(" ", $assocTableData): $this->model." model" ;

            $sqlquery = "SELECT $select FROM $associateTable WHERE model.deletedAt IS NULL $where $orderby $pages";
            $stmt = $this->pdo->prepare($sqlquery);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function POST( $jsonData) {
        try {
            $data = $this->jsonTransform($jsonData);
            if(isset($data['password'])){
                $data['password'] = $this->encryption($data['password']);
            }
            $columns = array_keys($data);
            $values = array_values($data);
    
            $insert_column = implode(",", $columns);
            $insert_values = implode(",", array_fill(0, count($columns), "?"));
            
            $sqlquery = "INSERT INTO ".$this->model." ($insert_column) VALUES ($insert_values)";
            $stmt = $this->pdo->prepare($sqlquery);

            return $stmt->execute($values);

        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function UPDATE( $jsonData, $condition = null) {
        try {
            $data = $this->jsonTransform($jsonData);
            $where = $condition ? "WHERE $condition": "";
            $setvalue = [];
            $insert_values = [];
            foreach ($data as $key => $value) {
                $setvalue[] = "$key = ?";
                $insert_values[] = $value;
            }

            $sqlquery = "UPDATE ".$this->model." SET ".implode(",",$setvalue)." $where";
            $stmt = $this->pdo->prepare($sqlquery);

            return $stmt->execute($insert_values);

        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function DELETE( $condition = null) {
        try {
            $where = $condition ? "WHERE $condition": "";
            $sqlquery = "";
            if($this->paranoid){
                //this will create a new column deletedAt if it doesnt exist
                $this->Transaction();
                $sqlquery = "UPDATE ".$this->model." SET deletedAt = CURRENT_TIMESTAMP $where";
            }else{
                $sqlquery = "DELETE FROM ".$this->model." $where";
            }
            $stmt = $this->pdo->prepare($sqlquery);
            return $stmt->execute();
        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function AUTH($jsonData, $condition = null,$columns = null) {
        try {
            $data = $this->jsonTransform($jsonData);
            if(isset($data['password'])){
                $data['password'] = $this->encryption($data['password']);
            }
            $select = $columns ? implode(",", $columns) : "*";
            
            $sqlquery = "SELECT $select FROM ".$this->model." WHERE deletedAt IS NULL AND email = ? AND password = ?";
            $stmt = $this->pdo->prepare($sqlquery);
            $stmt->execute([$data["email"],$data['password']]);

            return $stmt->fetchAll();
        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function Transaction(){
        try {
            // Start a transaction
            $this->pdo->beginTransaction();

            $sql = "
                SET @query = (
                    SELECT IF(
                        COUNT(*) = 0,
                        CONCAT('ALTER TABLE ".$this->model." ADD COLUMN deletedAt VARCHAR(255)'),
                        'SELECT 1'
                    )
                    FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = '".$this->model."' 
                    AND COLUMN_NAME = 'deletedAt'
                );
            ";
            $this->pdo->exec($sql);
        
            $sql = "
                PREPARE stmt FROM @query;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;
            ";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $stmt->closeCursor(); // Ensure the cursor is closed

            $this->pdo->commit();
        } catch (PDOException $e) {
            // Rollback the transaction if something went wrong
            $this->pdo->rollBack();
            echo "Error: " . $e->getMessage();
        }
    }
    public function jsonTransform($jsonData){
        try {
            return json_decode($jsonData, true);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        
    }
    public function encryption($password) {
        try {
            return md5($password);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        
    }
}