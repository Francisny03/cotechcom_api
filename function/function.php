<?php
// le lien des image 
define("URL", str_replace("index.php","",(isset($_SERVER['HTTPS'])? "https" : "http").
"://".$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"]));

// cconnexion à la basse de donner en ligne
function getcom(){
    return new PDO("mysql:host=localhost;dbname=cotechcom;charset=utf8","root","");
}

function sendJSON($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit; // Assurez-vous que rien d'autre n'est envoyé après la réponse JSON
}

function saveProfileImage($imageData) {
    if (strpos($imageData, 'data:image/') !== 0 || !preg_match('#^data:image/\w+;base64,#i', $imageData)) {
        throw new Exception("Format d'image non valide");
    }

    $extension = explode('/', mime_content_type($imageData))[1];
    $fileName = uniqid('profile_') . '.' . $extension;
    $targetDir = "image/";
    $targetFile = $targetDir . $fileName;

    $imageData = str_replace(' ', '+', $imageData);
    $base64Str = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
    $decodedData = base64_decode($base64Str);

    if ($decodedData === false) {
        throw new Exception("Décodage de l'image échoué");
    }

    if (file_put_contents($targetFile, $decodedData) === false) {
        throw new Exception("Échec de la sauvegarde de l'image");
    }

    return $targetFile;
}

function saveImage($imageData) {
    if (strpos($imageData, 'data:image/') !== 0) {
        throw new Exception("Format d'image non valide");
    }

    $extension = explode('/', mime_content_type($imageData))[1];
    $fileName = uniqid('services_') . '.' . $extension;
    $targetDir = "image/";
    $targetFile = $targetDir . $fileName;

    if (file_put_contents($targetFile, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)))) {
        return $targetFile;
    } else {
        throw new Exception("Erreur lors de l'enregistrement du fichier");
    }
}

//-----------------------Service-----------------------\\
//------------- Post
function createServices($data) {
    $pdo = getcom();

    try {
        error_log("Données reçues pour createServices: " . print_r($data, true));
        // Vérifiez que les données nécessaires sont présentes
        if (!isset($data['title']) || !isset($data['descriptions']) || !isset($data['points']) || !isset($data['images'])) {
            throw new Exception("Informations requises manquantes");
        }

        // Enregistrez les deux images
        $targetFile = saveImage($data['images']);

        // Préparez la requête d'insertion avec quatre paramètres de liaison
        $sql = "INSERT INTO services (titre, descriptions, images, points) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $data['title']);
        $stmt->bindParam(2, $data['descriptions']);
        $stmt->bindParam(3, $data['points']);
        $stmt->bindParam(4, $targetFile);

        $stmt->execute();

        $response = [
            "status" => "success",
            "message" => "service créé avec succès"
        ];
        return $response;
    } catch (Exception $e) {
        error_log("Erreur dans createServices: " . $e->getMessage());
        $response = [
            "status" => "error",
            "message" => $e->getMessage()
        ];
        return $response;
    }
}

//------------- Get
function getServices() {
    $pdo = getcom();
    $req = "SELECT * FROM `services` ";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Réstructurer le tableau pour inclure les informations de valu
    $formattedResults = [];
    foreach ($results as $row) {
        $formattedResults[] = [
            "id_services" => $row["id_service"],
            "title" => $row["title"],
            "description" => $row["descriptions"],
            "image" => URL."".$row["images"],
            "points" => $row["points"],
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"]
        ];
    }

    sendJSON($formattedResults);
}

//-----------------------Slider-----------------------\\
//------------- Post
function createSleder($data) {
    $pdo = getcom();

    try {
        error_log("Données reçues pour createServices: " . print_r($data, true));
        // Vérifiez que les données nécessaires sont présentes
        if (!isset($data['title']) || !isset($data['description']) || !isset($data['images'])) {
            throw new Exception("Informations requises manquantes");
        }

        // Enregistrez les deux images
        $targetFile = saveImage($data['images']);

        // Préparez la requête d'insertion avec quatre paramètres de liaison
        $sql = "INSERT INTO slider (title, descriptions, images) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $data['title']);
        $stmt->bindParam(2, $data['descriptions']);
        $stmt->bindParam(4, $targetFile);

        $stmt->execute();

        $response = [
            "status" => "success",
            "message" => "service créé avec succès"
        ];
        return $response;
    } catch (Exception $e) {
        error_log("Erreur dans createServices: " . $e->getMessage());
        $response = [
            "status" => "error",
            "message" => $e->getMessage()
        ];
        return $response;
    }
}

//------------- Get
function getSlider() {
    $pdo = getcom();
    $req = "SELECT * FROM `slider` ";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Réstructurer le tableau pour inclure les informations de valu
    $formattedResults = [];
    foreach ($results as $row) {
        $formattedResults[] = [
            "id_slider" => $row["id_slider"],
            "title" => $row["title"],
            "description" => $row["descriptions"],
            "image" => URL."".$row["images"],
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"]
        ];
    }

    sendJSON($formattedResults);
}

//-----------------------Equipe-----------------------\\
//------------- Post
function createEquipe($data) {
    $pdo = getcom();

    try {
        error_log("Données reçues pour createServices: " . print_r($data, true));
        // Vérifiez que les données nécessaires sont présentes
        if (!isset($data['descriptions']) || !isset($data['images'])) {
            throw new Exception("Informations requises manquantes");
        }

        // Enregistrez les deux images
        $targetFile = saveImage($data['images']);

        // Préparez la requête d'insertion avec quatre paramètres de liaison
        $sql = "INSERT INTO equipe (descriptions, images) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(2, $data['descriptions']);
        $stmt->bindParam(4, $targetFile);

        $stmt->execute();

        $response = [
            "status" => "success",
            "message" => "service créé avec succès"
        ];
        return $response;
    } catch (Exception $e) {
        error_log("Erreur dans createServices: " . $e->getMessage());
        $response = [
            "status" => "error",
            "message" => $e->getMessage()
        ];
        return $response;
    }
}

//------------- Get
function getEquipe() {
    $pdo = getcom();
    $req = "SELECT * FROM `equipe` ";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Réstructurer le tableau pour inclure les informations de valu
    $formattedResults = [];
    foreach ($results as $row) {
        $formattedResults[] = [
            "id_equipe" => $row["id_equipe"],
            "description" => $row["descriptions"],
            "image" => URL."".$row["images"],
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"]
        ];
    }

    sendJSON($formattedResults);
}
?>