<?php
namespace Models;
use \PDO;

class Mandate {
    // database conn
    private $conn;
    // table name
    private $table_name = "gocardless_mandate";

    // object properties
    public $id;
    public $idmember;
    public $gc_mandate_id;
    public $gc_customer_id;
    public $gc_subscriptionid;
    public $created_at;
    public $updated_at;

    public function __construct(){
        $this->conn = \Core\Database::getInstance()->conn;
    }

    /**
     * Create a new mandate record
     * @return bool
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET member_idmember = :idmember,
                      gc_mandate_id = :gc_mandate_id,
                      gc_customer_id = :gc_customer_id,
                      gc_subscriptionid = :gc_subscriptionid";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->idmember = filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $this->gc_mandate_id = htmlspecialchars(strip_tags($this->gc_mandate_id));
        $this->gc_customer_id = htmlspecialchars(strip_tags($this->gc_customer_id));
        $this->gc_subscriptionid = htmlspecialchars(strip_tags($this->gc_subscriptionid));

        if ($this->idmember <= 0) {
            return false; // must be non-zero, not-negative
        }

        // bind values
        $stmt->bindParam(":idmember", $this->idmember, PDO::PARAM_INT);
        $stmt->bindParam(":gc_mandate_id", $this->gc_mandate_id);
        $stmt->bindParam(":gc_customer_id", $this->gc_customer_id);
        $stmt->bindParam(":gc_subscriptionid", $this->gc_subscriptionid);

        // execute query
        if($stmt->execute()){
            $this->id = $this->conn->lastInsertId();
            if($this->id) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Find mandate by GoCardless subscription ID
     * @param string $gc_subscriptionid
     * @return bool
     */
    public function findByGCSubscriptionId($gc_subscriptionid) {
        $query = "SELECT idmandate, member_idmember, gc_mandate_id,
                         gc_customer_id, gc_subscriptionid, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE gc_subscriptionid = :gc_subscriptionid
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $gc_subscriptionid = htmlspecialchars(strip_tags($gc_subscriptionid));
        $stmt->bindParam(":gc_subscriptionid", $gc_subscriptionid);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['idmandate'];
            $this->idmember = $row['member_idmember'];
            $this->gc_mandate_id = $row['gc_mandate_id'];
            $this->gc_customer_id = $row['gc_customer_id'];
            $this->gc_subscriptionid = $row['gc_subscriptionid'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    /**
     * Find mandate by member ID, return 'false' if no mandate exists for that member
     * @param int $member_id
     * @return array|false Array of mandates or false
     */
    public function findByMemberId($member_id) {
        $query = "SELECT idmandate, member_idmember, gc_mandate_id,
                         gc_customer_id, gc_subscriptionid, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE member_idmember = :member_id";

        $stmt = $this->conn->prepare($query);
        $member_id = filter_var($member_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(":member_id", $member_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $subscriptions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subscriptions[] = [
                    'id' => $row['idmandate'],
                    'idmember' => $row['member_idmember'],
                    'gc_mandate_id' => $row['gc_mandate_id'],
                    'gc_customer_id' => $row['gc_customer_id'],
                    'gc_subscriptionid' => $row['gc_subscriptionid'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
            }
            return $subscriptions;
        }

        return false;
    }

    /**
     * Find mandate by mandate ID, return 'false' if no mandate exists for that member
     * @param string $gc_mandate_id
     * @return bool
     */
    public function findByMandateId($gc_mandate_id) {
        $query = "SELECT idmandate, member_idmember, gc_mandate_id,
                         gc_customer_id, gc_subscriptionid, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE gc_mandate_id = :gc_mandate_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $gc_mandate_id = htmlspecialchars(strip_tags($gc_mandate_id));
        $stmt->bindParam(":gc_mandate_id", $gc_mandate_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['idmandate'];
            $this->idmember = $row['member_idmember'];
            $this->gc_mandate_id = $row['gc_mandate_id'];
            $this->gc_customer_id = $row['gc_customer_id'];
            $this->gc_subscriptionid = $row['gc_subscriptionid'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }

        return false;
    }

    /**
     * Update mandate record
     * @return bool
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET member_idmember = :idmember,
                      gc_mandate_id = :gc_mandate_id,
                      gc_customer_id = :gc_customer_id,
                      gc_subscriptionid = :gc_subscriptionid
                  WHERE idmandate = :id";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $this->idmember = filter_var($this->idmember, FILTER_SANITIZE_NUMBER_INT);
        $this->gc_mandate_id = htmlspecialchars(strip_tags($this->gc_mandate_id));
        $this->gc_customer_id = htmlspecialchars(strip_tags($this->gc_customer_id));
        $this->gc_subscriptionid = htmlspecialchars(strip_tags($this->gc_subscriptionid));

        // bind values
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":idmember", $this->idmember, PDO::PARAM_INT);
        $stmt->bindParam(":gc_mandate_id", $this->gc_mandate_id);
        $stmt->bindParam(":gc_customer_id", $this->gc_customer_id);
        $stmt->bindParam(":gc_subscriptionid", $this->gc_subscriptionid);

        return $stmt->execute();
    }

    /**
     * Delete subscription record
     * @return bool
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . "
                  WHERE idmandate = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = filter_var($this->id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Check if a mandate exists by GoCardless mandate ID
     * @param string $gc_mandate_id
     * @return bool
     */
    public function exists($gc_mandate_id) {
        $query = "SELECT idmandate
                  FROM " . $this->table_name . "
                  WHERE gc_mandate_id = :gc_mandate_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $gc_mandate_id = htmlspecialchars(strip_tags($gc_mandate_id));
        $stmt->bindParam(":gc_mandate_id", $gc_mandate_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['idmandate'];
            return true;
        }

        return false;
    }
}
