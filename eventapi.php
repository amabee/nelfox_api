<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
include ("conn.php");
class Eventapi
{

    function login($json)
    {
        include ("conn.php");
        $json = json_decode($json, true);
        $sql = "SELECT `username`, `password` FROM `users` WHERE username = :username AND password = :password";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(":username", $json['username']);
        $stmt->bindParam(":password", $json['password']);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }

    function getEvent($json)
    {
        include ("conn.php");
        $json = json_decode($json, true);
        $select_sql = "SELECT * FROM `event_details` WHERE creator = :user";
        $select_stmt = $conn->prepare($select_sql);
        $select_stmt->bindParam(":user", $json["user"]);
        $select_stmt->execute();
        $row = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($row);
    }
    function addEvent($json)
    {
        include ("conn.php");
        $json = json_decode($json, true);

        $insert_sql = "INSERT INTO `event_details`(`event_name`, `event_venue`, `event_date`, `creator`) VALUES (:event_name, :event_venue, :event_date, :creator)";
        $insert_stmt = $conn->prepare($insert_sql);

        $insert_stmt->bindParam(":event_name", $json["event_name"]);
        $insert_stmt->bindParam(":event_venue", $json["event_venue"]);
        $insert_stmt->bindParam(":event_date", $json["event_date"]);
        $insert_stmt->bindParam(":creator", $json["creator"]);

        if ($insert_stmt->execute()) {
            echo json_encode("1");
        } else {
            echo json_encode("0");
        }
    }

    function getParticipants($json)
    {
        include ("conn.php");
        $json = json_decode($json, true);
        $select_sql = "SELECT * FROM `participants` WHERE event_id = :event_id";
        $select_stmt = $conn->prepare($select_sql);
        $select_stmt->bindParam(":event_id", $json["event_id"]);
        $select_stmt->execute();
        $row = $select_stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($row);
    }
    function addParticipant($json)
    {
        include ("conn.php");
        $json = json_decode($json, true);

        try {
            $sql = "INSERT INTO `participants`(`participant_name`, `participant_car`, `car_type`, `car_model`, `release_year`, `event_id`, `car_image`) ";
            $sql .= "VALUES (:participant_name, :participant_car, :car_type, :car_model, :release_year, :event_id, :car_image)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":participant_name", $json["participant_name"], PDO::PARAM_STR);
            $stmt->bindParam(":participant_car", $json["participant_car"], PDO::PARAM_STR);
            $stmt->bindParam(":car_type", $json["car_type"], PDO::PARAM_STR);
            $stmt->bindParam(":car_model", $json["car_model"], PDO::PARAM_STR);
            $stmt->bindParam(":release_year", $json["release_year"], PDO::PARAM_STR);
            $stmt->bindParam(":event_id", $json["event_id"], PDO::PARAM_STR);
            
            if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] === UPLOAD_ERR_OK) {
                $imagePath = "cars_folder/";
                $imageName = uniqid() . '.jpg';
                move_uploaded_file($_FILES['car_image']['tmp_name'], $imagePath . $imageName);
                $imageUrl = $imagePath . $imageName;
                $json["car_image"] = $imageUrl;
                $stmt->bindParam(":car_image", $json["car_image"], PDO::PARAM_STR);
            } else {
                // Handle error if file upload failed
                echo json_encode("File upload failed");
                return;
            }

            $res = $stmt->execute();
            if ($res) {
                echo json_encode("1");
            } else {
                echo json_encode("0");
            }
        } catch (PDOException $e) {
            echo json_encode(array("error" => $e->getMessage()));
        }
    }




}

$api = new Eventapi();

if ($_SERVER["REQUEST_METHOD"] == "GET" || $_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_REQUEST['operation']) && isset($_REQUEST['json'])) {
        $operation = $_REQUEST['operation'];
        $json = $_REQUEST['json'];

        switch ($operation) {
            case 'login':
                echo $api->login($json);
                break;
            case 'getevents':
                echo $api->getEvent($json);
                break;
            case 'addevent':
                echo $api->addEvent($json);
                break;
            case 'getparticipants':
                echo $api->getParticipants($json);
                break;
            case 'addparticipants':
                echo $api->addParticipant($json);
                break;

            default:
                echo json_encode(["error" => "Invalid operation"]);
                break;
        }
    } else {
        echo json_encode(["error" => "Missing parameters"]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>