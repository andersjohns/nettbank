
<?php
function logg_aktivitet($conn, $bruker_id, $handling) {
    $query = "INSERT INTO aktivitetslogger (bruker_id, handling) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $bruker_id, $handling);
    $stmt->execute();
}
