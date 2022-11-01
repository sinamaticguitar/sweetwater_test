<?

class DB {

    // Establish variables
    const TABLE = 'sweetwater_test';

    const CAT_NAME_CANDY    = 'Candy';
    const CAT_NAME_CALL     = 'Call / Do Not Call';
    const CAT_NAME_REFER    = 'Referrals';
    const CAT_NAME_SIG      = 'Signatures';
    const CAT_NAME_MISC     = 'Miscellaneous';

    const CATEGORIES = array(
        1 => self::CAT_NAME_CANDY,
        2 => self::CAT_NAME_CALL,
        3 => self::CAT_NAME_REFER,
        4 => self::CAT_NAME_SIG,
        5 => self::CAT_NAME_MISC
    );

    private $hostname   = 'localhost';
    private $username   = 'sweetwater';
    private $secret     = 'Infinity1!';
    private $database   = 'sweetwater';

    // Establish functionality
    function __construct() {
        $this->conn = new mysqli(
            $this->hostname,
            $this->username,
            $this->secret,
            $this->database
        );

        if ($this->conn->connect_error) {
            die("Can't connect: " . $this->conn->connect_error);
        }
    }

    function close() {
        $this->conn->close();
    }


    // Establish actions
    function fixExpectedDate($records, $conn) {
        foreach($records as $record) {
            if(preg_match("/(?i)(\W|^)(Expected Ship Date:)(\W|$)/", $record['comments'])) {
                $d = preg_match_all('/(?<=(Expected Ship Date:))(\s\w*)(\W\w*)(\W\w*)/', $record['comments'], $m);

                $sql = "UPDATE sweetwater_test SET shipdate_expected = ? WHERE orderid = ?";
                $s = $conn->prepare($sql);
                $s->bind_param("si", $m[0][0],$record['orderid']);

                if(!$s->execute()) {
                    die("Expected date fix failed");
                }
            }
        }

        return true;
    }

    function getAllRecords($conn) {
        $results = array();

        $sql = "SELECT * FROM sweetwater_test";

        $r = $conn->query($sql);

        if ($r->num_rows > 0) {
            while($row = $r->fetch_assoc()) {
                array_push($results, $row);
            }
        }

        return $results;
    }

    function getAllRecordsWithCategory($conn) {
        $results = array();

        $sql = <<<EOF
            SELECT		orderid,
			            CASE
				            WHEN comments LIKE '%candy%' THEN 1
				            WHEN comments LIKE '%call%' THEN 2
				            WHEN comments LIKE '%referral%' OR comments LIKE '%referred%' THEN 3
				            WHEN comments LIKE '%signature%' THEN 4
				            ELSE 5
                        END as category_id,
			            comments,
			            shipdate_expected
            FROM		sweetwater_test
EOF;

        $r = $conn->query($sql);

        if ($r->num_rows > 0) {
            while($row = $r->fetch_assoc()) {
                array_push($results, $row);
            }
        }

        return $results;
    }
}