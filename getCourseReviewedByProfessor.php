<?php
require 'connectDB.php';
require 'parseSQL.php';
require 'getData.php';

$SQL_SUCCESS_JSON = '{"success":true}';
$SQL_FAIL_JSON = '{"success":false}';

// connect to the database
$dbConnection = connectDB();
$connection = null;
$sql = "";
$variables = array();

if ($dbConnection['connect']) {
    // get the connection
    $connection = $dbConnection['connection'];

    $sql =
            "SELECT c.course_id FROM Course c " .
            "WHERE NOT EXISTS (" .
            "(SELECT p.professor_id FROM Professor p WHERE p.professor_id = c.professor_id) " .
            "MINUS " .
            "(SELECT r.professor_id FROM Review r WHERE r.course_id = c.course_id)" .
            ")";
    // echo $sql;
    // echo json_encode($variables);

    $statement = parseSQL($connection, $sql, $variables);
    $result = oci_execute($statement);
    if ($result) {
        $response = array(success => true);

        $data = getData($statement);

        $dataHtml = '<table id="course-reviewed-by-professor-summary-table" class="table table-responsive mdl-data-table mdl-js-data-table">';

        $dataHtml .= '<thead><tr><th class="mdl-data-table__cell--non-numeric">Course</th></tr></thead>';

        $dataHtml .= '<tbody>';
        foreach ($data as $row) {
            $dataHtml .= '<tr><td class="mdl-data-table__cell--non-numeric">' . $row['COURSE_ID'] .'</td>';
        }
        $dataHtml .= '</tbody>';

        $dataHtml .= '</table>';

        $response['dataHtml'] = $dataHtml;
        // echo $dataHtml;

        echo json_encode($response);
    } else {
        $error = array(success => false, error => oci_error($statement));
        echo json_encode($error);
    }

    // close the connection
    OCILogoff($connection);
} else {
    echo $SQL_FAIL_JSON;
}
?>
