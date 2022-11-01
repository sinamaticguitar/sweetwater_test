<html>
    <head>
        <style>
            #comments,
            #comments td {
                border-collapse: collapse;
            }

            .category {
                font-weight: bold;
                text-decoration: underline;
                font-size: 24px;
                padding-bottom: 10px;
                padding-top: 10px;
            }

            .order_id,
            .ship_date,
            .comment {
                padding: 5px;
            }

            .info_row {
                border: 1px solid #AAA;
            }

            .order_id {
            }

            .ship_date {
                text-align: right;
            }

            .comment {
                border: 1px solid #AAA;
            }

            .spacer {
                height: 10px;
            }
        </style>
    </head>
    <body>
<?php

// Require additional files
require 'database.php';

//Setup variables
$comments = array();

// Set DB Connection
$db = new DB();
$conn = $db->conn;

// Fix expected ship date records
$i = $db->getAllRecords($conn);
if($db->fixExpectedDate($i,$conn)) {

    // Hydrate all records
    $comments = $db->getAllRecordsWithCategory($conn);

    // Parse and display data
    echo "<table id='comments'>\n";
    foreach(parseResults($comments) as $category => $orders) {
        echo "<tr><td colspan='2' class='category'>" . DB::CATEGORIES[$category] . "</td></tr>";

        foreach($orders as $order_id => $details) {
            $ship_date = ($details['ship_date'] > 0) ? "Expected Ship Date: " . date_format(date_create($details['ship_date']),"Y-m-d") : "";
            echo "<tr class='info_row'><td class='order_id'>Order ID: {$order_id}</td><td class='ship_date'>{$ship_date}</td></tr>\n";
            echo "<tr><td colspan='2' class='comment'>" . nl2br(htmlentities($details['comment'])) . "</td></tr>\n";
            echo "<tr><td colspan='2' class='spacer'></td></tr>\n";
        }
    }

}

// Close connection
$db->close();

// Establish actions
function parseResults($results) {

    $records = array();

    foreach($results as $r) {
        $records[$r['category_id']][$r['orderid']] = array(
            'comment' => $r['comments'],
            'ship_date' => $r['shipdate_expected']
        );
    }

    ksort($records);
    return $records;
}
?>
    </body>
</html>