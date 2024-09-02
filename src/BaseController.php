<?php
namespace Simpluity\Simpluity;

class BaseController {

    public $model;
    public $pdo;
    public $paranoid;
    public $pagination;
    public $page_number = 1;
    public $page_limit = 20;


    public function __construct($model, $pdo, $paranoid = false, $pagination = false) {
        $this->model = $model;
        $this->pdo = $pdo;
        $this->pagination = $pagination;
        $this->paranoid = $paranoid;
    }

    // data = associative array
    // columns = array
    // condition = string [string, [datas]] condition = ?, [1]
    // associates = array [tablename, INNER || LEFT || RIGHT, condition, columns ]
    // order = array

    public function GET($condition = null, $order = null) {
        try {
            $currentpage = $this->page_limit * ($this->page_number - 1);
            $limit = $this->page_limit;

            $orderby = $order ? "ORDER BY ".implode(",", $order) : "";
            $pages = $this->pagination ? "LIMIT $limit OFFSET $currentpage" : "";
            $deletedAt = $this->paranoid ? "WHERE deletedAt IS NULL " : ($condition ? "WHERE " : "");
            $where = $condition ? $deletedAt.$condition[0]: $deletedAt;

            $sqlquery = "SELECT * FROM ".$this->model." $deletedAt $where $orderby $pages";
            $stmt = $this->pdo->prepare($sqlquery);
   
            if($condition){
                if($condition[1] == null){
                    $stmt->execute();
                }else{
                    $stmt->execute($condition[1]);
                }
            }else{
                $stmt->execute();
            }
  

            return $stmt->fetchAll();
        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function COUNT( $associates = null, $condition = null, $order = null) {
        try {
            $currentpage = $this->page_limit * ($this->page_number - 1);
            $limit = $this->page_limit;

            $orderby = $order ? "ORDER BY ".implode(",", $order) : "";
            $pages = $this->pagination ? "LIMIT $limit OFFSET $currentpage" : "";

            $columns = $associates ? array_map( function($value){
                $targettable = $value[0];
                if (!in_array(3, $value)) {
                    // Add a new element with the key 3 and the value ["*"]
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

            $select = $columns ? "model.* , COUNT(model.createdAt) as Count, ".implode(",", $columns) : "model.*, COUNT(model.createdAt) as Count";

            $associateTable = $associates ? $this->model." model ".implode(" ", $assocTableData): $this->model." model" ;
            $deletedAt = $this->paranoid ? "WHERE model.deletedAt IS NULL " : ($condition ? "WHERE " : "");
            $where = $condition ? $deletedAt.$condition[0]: $deletedAt;

            $sqlquery = "SELECT $select FROM $associateTable $where $orderby $pages";

            $stmt = $this->pdo->prepare($sqlquery);

            if($condition == null){
                $stmt->execute();
            }else{
                $stmt->execute($condition[1]);
            }

            return $stmt->fetchAll();
        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function GET_withASSOCIATES( $associates = null, $condition = null, $order = null) {
        try {
            $currentpage = $this->page_limit * ($this->page_number - 1);
            $limit = $this->page_limit;

            $orderby = $order ? "ORDER BY ".implode(",", $order) : "";
            $pages = $this->pagination ? "LIMIT $limit OFFSET $currentpage" : "";

            $columns = $associates ? array_map( function($value){
                $targettable = $value[0];
                if (!in_array(3, $value)) {
                    // Add a new element with the key 3 and the value ["*"]
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
            $deletedAt = $this->paranoid ? "WHERE model.deletedAt IS NULL " : ($condition ? "WHERE " : "");
            $where = $condition ? $deletedAt.$condition[0]: $deletedAt;


            $sqlquery = "SELECT $select FROM $associateTable $where $orderby $pages";

            $stmt = $this->pdo->prepare($sqlquery);

            if($condition[1] == null){
                $stmt->execute();
            }else{
                $stmt->execute($condition[1]);
            }

            return $stmt->fetchAll();
        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function POST( $data, $password = null) {
        try {
            if(isset($password)){
                $data[$password] = $this->encryption($data[$password]);
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
    public function UPDATE( $data, $condition = null) {
        try {
            $where = $condition ? "WHERE ".$condition[0]: "";
            $setvalue = [];
            $insert_values = [];
            foreach ($data as $key => $value) {
                $setvalue[] = "$key = ?";
                $insert_values[] = $value;
            }
            $values = array_merge($insert_values, $condition[1]);

            $sqlquery = "UPDATE ".$this->model." SET ".implode(",",$setvalue)." $where";
            $stmt = $this->pdo->prepare($sqlquery);

            return $stmt->execute($values);

        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function DELETE( $condition = null) {
        try {
            $where = $condition ? "WHERE ".$condition[0]: "";
            $sqlquery = "";
            if($this->paranoid){
                //this will create a new column deletedAt if it doesnt exist
                $this->Transaction();
                $sqlquery = "UPDATE ".$this->model." SET deletedAt = CURRENT_TIMESTAMP $where";
            }else{
                $sqlquery = "DELETE FROM ".$this->model." $where";
            }
            $stmt = $this->pdo->prepare($sqlquery);
            return $stmt->execute($condition[1]);
        } catch (\Throwable $th) {
            return $th->getMessage();//getTraceAsString()
        }
    }
    public function AUTH($data, $password,$columns = null) {
        try {
            if(isset($password)){
                $data[$password] = $this->encryption($data[$password]);
            }
            $select = $columns ? implode(",", $columns) : "*";

            $deletedAt = $this->paranoid ? "WHERE deletedAt IS NULL AND " : "WHERE ";
            $sqlquery = "SELECT $select FROM ".$this->model." $deletedAt email = ? AND password = ?";
   

            $stmt = $this->pdo->prepare($sqlquery);
            $stmt->execute([$data["email"],$data[$password]]);

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
    public function encryption($password) {
        try {
            return md5($password);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
        
    }
}