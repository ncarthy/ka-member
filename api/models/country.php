<?php
namespace Models;
use \PDO;
class Country{
    // database conn 
    private $conn;
    // table name
    private $table_name = "country";
    private $table_id = "id";

    // object properties
    protected int $id;
    protected string $name;
    protected string $code;

    /**
     * Id setter
     */
    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * ID getter.
     */
    public function getId(): int
    {
        return $this->id;
    }    

    /**
     * Name setter
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Name getter.
     */
    public function getName(): string
    {
        return $this->name;
    }  

    /**
     * ISO Code setter
     */
    public function setCode(string $code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * ISO Code getter.
     */
    public function getCode(): string
    {
        return $this->code;
    }  

    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * Static constructor / factory
     */
    public static function getInstance()
    {
        return new self();
    }    

    // used by select drop-down list
    public function read(){

        //select all data
        $query = "SELECT
                    " . $this->table_id ." as `id`, `name`, `ISO3166`
                FROM
                    " . $this->table_name . "
                ORDER BY ". $this->table_id;

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        $num = $stmt->rowCount();

        $item_arr=array();

        // check if more than 0 record found
        if($num>0){

            // retrieve our table contents
            // fetch() is faster than fetchAll()
            // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                // extract row
                // this will make $row['name'] to
                // just $name only
                extract($row);
            
                    $item=array(
                        "id" => $id,
                        "name" => $name,
                        "code" => $ISO3166
                    );

                    $item_arr[] = $item;
                }
        }

        return $item_arr;
    }
        
        public function readOne(){

            //select data for one item using PK of table
            $query = "SELECT
                        " . $this->table_id ." as `id`, `name`, `ISO3166`
                    FROM
                        " . $this->table_name . "
                        WHERE "; 

            // WHERE clause depends on parameters
            if(isset($this->name) && !empty($this->name)) {
                $query .= "LOWER(name) LIKE :name ";
            }
            else if (isset($this->code) && !empty($this->code)) {
                $query .= "ISO3166 = :code ";
            }        
            else {
                $query .= $this->table_id ." = :id ";
            }
            $query .= "LIMIT 0,1";
    
            // prepare query statement
            $stmt = $this->conn->prepare($query);      

            if(isset($this->name) && !empty($this->name)) {
                $name = htmlspecialchars(strip_tags($this->name)).'%';
                $stmt->bindParam (":name", $name, PDO::PARAM_STR);
            }
            else if (isset($this->code) && !empty($this->code)) {
                $id = htmlspecialchars(strip_tags($this->code));
                $stmt->bindParam (":code", $id, PDO::PARAM_STR);
            }
            else {
                $id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
                $stmt->bindParam (":id", $id, PDO::PARAM_INT);
            }
            
            $stmt->execute();

            // get retrieved row
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // set values to object properties
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->code = $row['ISO3166'];
            // create array
            $item = array(
                "id" => $this->id,
                "name" => $this->name,
                "code" => $this->code  
            );

            return $item;
        }
}