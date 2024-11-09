<?php
// le lien des image 
define("URL", str_replace("index.php","",(isset($_SERVER['HTTPS'])? "https" : "http").
"://".$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"]));

// cconnexion à la basse de donner en ligne
function getcom(){
    return new PDO("mysql:host=localhost;dbname=cotechcom;charset=utf8","root","");
}

// function important
function sendJSON($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
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

function saveProfileImages($imagesData) {
    $fichiersEnregistres = [];

    foreach ($imagesData as $imageData) {
        if (strpos($imageData, 'data:image/') !== 0 || !preg_match('#^data:image/\w+;base64,#i', $imageData)) {
            throw new Exception("Format d'image non valide");
        }

        $extension = explode('/', mime_content_type($imageData))[1];
        $nomFichier = uniqid('image_') . '.' . $extension;
        $dossierCible = "image/";
        $fichierCible = $dossierCible . $nomFichier;

        $imageData = str_replace(' ', '+', $imageData);
        $base64Str = preg_replace('#^data:image/\w+;base64,#i', '', $imageData);
        $donneesDecodees = base64_decode($base64Str);

        if ($donneesDecodees === false) {
            throw new Exception("Décodage de l'image échoué");
        }

        if (file_put_contents($fichierCible, $donneesDecodees) === false) {
            throw new Exception("Échec de la sauvegarde de l'image");
        }

        $fichiersEnregistres[] = $fichierCible;
    }

    return $fichiersEnregistres;
}

function saveImage($imageData) {
    if (strpos($imageData, 'data:image/') !== 0) {
        throw new Exception("Format d'image non valide");
    }

    $extension = explode('/', mime_content_type($imageData))[1];
    $fileName = uniqid('services_') . '.' . $extension;
    $targetDir = "images/";
    $targetFile = $targetDir . $fileName;

    if (file_put_contents($targetFile, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData)))) {
        return $targetFile;
    } else {
        throw new Exception("Erreur lors de l'enregistrement du fichier");
    }
}


function saveImages($base64Image) {
    if ($base64Image === null || !is_string($base64Image)) {
        throw new Exception('Format d\'image invalide');
    }

    if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
        // Supprimer la partie 'data:image/type;base64,' pour ne garder que les données
        $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);

        // Obtenir le type de l'image
        $type = strtolower($type[1]);

        // Vérification du type de l'image
        if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
            throw new Exception('Type d\'image non supporté : ' . $type);
        }

        // Décoder l'image encodée en base64
        $base64Image = base64_decode($base64Image);

        // Vérification du décodage
        if ($base64Image === false) {
            throw new Exception('Échec du décodage de l\'image');
        }

        // Générer un nom de fichier unique
        $fileName = uniqid() . '.' . $type;
        $filePath = 'images/' . $fileName;

        // Sauvegarder l'image sur le serveur
        if (file_put_contents($filePath, $base64Image) === false) {
            throw new Exception('Échec de l\'enregistrement de l\'image');
        }

        return $filePath;
    } else {
        throw new Exception('Format d\'image invalide');
    }
}

//-----------------------Service-----------------------\\
//------------- Post
function createServices($data) {
    $pdo = getcom(); // Connexion à la base de données

    try {
        error_log("Données reçues pour createServices: " . print_r($data, true));
        
        // Vérifiez que les données nécessaires sont présentes
        if (!isset($data['title']) || !isset($data['descriptions']) || !isset($data['points']) || !isset($data['images'])) {
            throw new Exception("Informations requises manquantes");
        }

        // Enregistrez l'image (et vérifiez si saveImage renvoie un chemin ou une URL valide)
        $targetFile = saveImage($data['images']);
        if (!$targetFile) {
            throw new Exception("L'enregistrement de l'image a échoué");
        }

        // Préparez la requête d'insertion
        $sql = "INSERT INTO services (title, descriptions, points, images) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Liaison des paramètres dans l'ordre correct
        $stmt->bindParam(1, $data['title']);
        $stmt->bindParam(2, $data['descriptions']);
        $stmt->bindParam(3, $data['points']);
        $stmt->bindParam(4, $targetFile); // Chemin ou URL de l'image

        // Exécutez la requête
        $stmt->execute();

        // Retournez une réponse JSON en cas de succès
        $response = [
            "status" => "success",
            "message" => "Service créé avec succès"
        ];
        return $response;

    } catch (Exception $e) {
        // En cas d'erreur, enregistrez le message d'erreur dans les logs et retournez une réponse d'erreur
        error_log("Erreur dans createServices: " . $e->getMessage());
        $response = [
            "status" => "error",
            "message" => $e->getMessage()
        ];
        return $response;
    }
}

// Update
function UpdateServices($data) {
    $pdo = getcom(); // Connexion à la base de données

    try {
        error_log("Données reçues pour UpdateServices: " . print_r($data, true));
        
        // Vérifiez que l'identifiant du service à modifier est présent
        if (!isset($data['id_service'])) {
            throw new Exception("ID du service manquant");
        }

        $fields = [];
        $params = [];

        // Vérifiez chaque champ et ajoutez-le à la requête s'il est présent
        if (isset($data['title'])) {
            $fields[] = "title = ?";
            $params[] = $data['title'];
        }

        if (isset($data['descriptions'])) {
            $fields[] = "descriptions = ?";
            $params[] = $data['descriptions'];
        }

        if (isset($data['points'])) {
            $fields[] = "points = ?";
            $params[] = $data['points'];
        }

        if (isset($data['images'])) {
            // Enregistrez l'image si elle est présente
            $targetFile = saveImage($data['images']);
            if (!$targetFile) {
                throw new Exception("L'enregistrement de l'image a échoué");
            }
            $fields[] = "images = ?";
            $params[] = $targetFile;
        }

        // Si aucun champ n'est fourni, on retourne une erreur
        if (empty($fields)) {
            throw new Exception("Aucune donnée à mettre à jour");
        }

        // Ajoutez l'ID à la fin des paramètres
        $params[] = $data['id_service'];

        // Préparez la requête SQL avec les champs dynamiques
        $sql = "UPDATE services SET " . implode(", ", $fields) . " WHERE id_service = ?";
        $stmt = $pdo->prepare($sql);

        // Exécutez la requête avec les paramètres dynamiques
        $stmt->execute($params);

        // Retournez une réponse JSON en cas de succès
        $response = [
            "status" => "success",
            "message" => "Service mis à jour avec succès"
        ];
        return $response;

    } catch (Exception $e) {
        // En cas d'erreur, enregistrez le message d'erreur dans les logs et retournez une réponse d'erreur
        error_log("Erreur dans UpdateServices: " . $e->getMessage());
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

function getService($id) {
    $pdo = getcom();
    $req = "SELECT * FROM `services` WHERE id_service = :id";
    $stmt = $pdo->prepare($req);
    $stmt->bindValue(":id", $id, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($result) {
        $formattedResult = [
            "id_services" => $result["id_service"],
            "title" => $result["title"],
            "description" => $result["descriptions"],
            "image" => URL . $result["images"],
            "points" => $result["points"],
            "created_at" => $result["created_at"],
            "updated_at" => $result["updated_at"]
        ];

        sendJSON($formattedResult);
    } 
}

function getCountServices() {
    $pdo = getcom();
    $req = "SELECT COUNT(*) AS count FROM services";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    $stmt->closeCursor();

    sendJSON(['count' => $count]);
}

//-----------------------Slider-----------------------\\
//------------- Post
function createSleder($data) {
    $pdo = getcom();

    try {
        error_log("Données reçues pour createSleder: " . print_r($data, true));

        // Vérifiez que les données nécessaires sont présentes
        if (!isset($data['title']) || !isset($data['descriptions']) || !isset($data['images'])) {
            throw new Exception("Informations requises manquantes");
        }

        // Enregistrez l'image
        $targetFile = saveImage($data['images']);

        // Préparez la requête d'insertion avec trois paramètres de liaison
        $sql = "INSERT INTO slider (title, descriptions, images) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        // Lier correctement les paramètres (3 au total)
        $stmt->bindParam(1, $data['title']);
        $stmt->bindParam(2, $data['descriptions']);
        $stmt->bindParam(3, $targetFile);  // Correction: 3ème paramètre

        $stmt->execute();

        $response = [
            "status" => "success",
            "message" => "Slider créé avec succès"
        ];
        return $response;
    } catch (Exception $e) {
        error_log("Erreur dans createSleder: " . $e->getMessage());
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
    // $req = "SELECT * FROM `slider` ";
    $req = "SELECT * FROM `slider` ORDER BY id_slider DESC LIMIT 5";
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

function getCountSlider() {
    $pdo = getcom();
    $req = "SELECT COUNT(*) AS count FROM slider";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    $stmt->closeCursor();

    sendJSON(['count' => $count]);
}

//-----------------------partenaire-----------------------\\
//------------- Post
function createPartenaire($data) {
    $pdo = getcom();

    try {
        error_log("Données reçues pour createSleder: " . print_r($data, true));

        // Vérifiez que les données nécessaires sont présentes
        if (!isset($data['title']) || !isset($data['images'])) {
            throw new Exception("Informations requises manquantes");
        }

        // Enregistrez l'image
        $targetFile = saveImage($data['images']);

        // Préparez la requête d'insertion avec trois paramètres de liaison
        $sql = "INSERT INTO partenaire (title, images) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);

        // Lier correctement les paramètres (3 au total)
        $stmt->bindParam(1, $data['title']);
        $stmt->bindParam(3, $targetFile);  // Correction: 3ème paramètre

        $stmt->execute();

        $response = [
            "status" => "success",
            "message" => "Slider créé avec succès"
        ];
        return $response;
    } catch (Exception $e) {
        error_log("Erreur dans createSleder: " . $e->getMessage());
        $response = [
            "status" => "error",
            "message" => $e->getMessage()
        ];
        return $response;
    }
}


//------------- Get
function getPartenaire() {
    $pdo = getcom();
    // $req = "SELECT * FROM `slider` ";
    $req = "SELECT * FROM `partenaire`";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Réstructurer le tableau pour inclure les informations de valu
    $formattedResults = [];
    foreach ($results as $row) {
        $formattedResults[] = [
            "id_partenaire" => $row["id_partenaire"],
            "title" => $row["title"],
            "image" => URL."".$row["images"],
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"]
        ];
    }

    sendJSON($formattedResults);
}

function getCountPartenaire() {
    $pdo = getcom();
    $req = "SELECT COUNT(*) AS count FROM partenaire";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    $stmt->closeCursor();

    sendJSON(['count' => $count]);
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

//-----------------------Real-----------------------\\
//------------- Post

function PostRealisation($data) {
    $pdo = getcom();

    try {
        // Vérifiez que les données nécessaires sont présentes
        if (!isset($data['title']) || !isset($data['descriptions']) || !isset($data['dates']) || !isset($data['imagess']) || !isset($data['images'])) {
            throw new Exception("Informations requises manquantes");
        }

        // Si $data['imagess'] n'est pas un tableau, lever une exception
        if (!is_array($data['imagess'])) {
            throw new Exception("Le champ imagess doit être un tableau");
        }

        // Enregistrez chaque image et générer le chemin
        $savedImagePaths = [];
        foreach ($data['imagess'] as $image) {
            $targetFile = saveImages($image);  // Fonction saveImages pour enregistrer chaque image
            $savedImagePaths[] = $targetFile;
        }
        // Enregistrez l'image
        $targetFile = saveImage($data['images']);
        $imagesJson = json_encode($savedImagePaths);

        $sql = "INSERT INTO realisation (title, descriptions, imagess, dates, images) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(1, $data['title']);
        $stmt->bindParam(2, $data['descriptions']);
        $stmt->bindParam(3, $imagesJson);
        $stmt->bindParam(4, $data['dates']);
        $stmt->bindParam(5, $data['images']);
        $stmt->execute();

        // Réponse JSON
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "success",
            "message" => "Réalisation créée avec succès"
        ]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
}

//------------- Get
function getrealisation() {
    $pdo = getcom();
    $req = "SELECT * FROM `realisation`";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Réstructurer le tableau pour inclure les informations de valeur
    $formattedResults = [];
    foreach ($results as $row) {
        // Vérifier si la colonne 'images' est non nulle et non vide avant de la décoder
        $images = [];
        if (!empty($row['imagess'])) {
            $images = json_decode($row['imagess'], true);  // Décoder uniquement si non vide
            if (!is_array($images)) {
                $images = [];  // Si la chaîne décodée n'est pas un tableau, initialiser un tableau vide
            }
        }

        // Ajouter l'URL de base pour chaque image
        $imageUrls = [];
        foreach ($images as $image) {
            $imageUrls[] = URL . $image;  // Ajouter l'URL de base
        }

        // Créer une entrée formatée
        $formattedResults[] = [
            "id_realisation" => $row["id_realisation"],
            "title" => $row["title"],
            "descriptions" => $row["descriptions"],
            "dates" => $row["dates"],
            "images" => URL .$row["images"],
            "imagess" => $imageUrls,  // Tableau des URLs d'images
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"]
        ];
    }

    sendJSON($formattedResults);
}

function getrealisationById($id) {
    $pdo = getcom();
    $req = "SELECT * FROM `realisation` WHERE id_realisation= $id";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Réstructurer le tableau pour inclure les informations de valeur
    if($row) {
        // Vérifier si la colonne 'images' est non nulle et non vide avant de la décoder
        $images = [];
        if (!empty($row['imagess'])) {
            $images = json_decode($row['imagess'], true);  // Décoder uniquement si non vide
            if (!is_array($images)) {
                $images = [];  // Si la chaîne décodée n'est pas un tableau, initialiser un tableau vide
            }
        }

        // Ajouter l'URL de base pour chaque image
        $imageUrls = [];
        foreach ($images as $image) {
            $imageUrls[] = URL . $image;  // Ajouter l'URL de base
        }

        // Créer une entrée formatée
        $formattedResults[] = [
            "id_realisation" => $row["id_realisation"],
            "title" => $row["title"],
            "descriptions" => $row["descriptions"],
            "dates" => $row["dates"],
            "images" => URL . $row["images"],
            "imagess" => $imageUrls,  // Tableau des URLs d'images
            "created_at" => $row["created_at"],
            "updated_at" => $row["updated_at"]
        ];
    }

    sendJSON($formattedResults);
}

function getCountRealisation() {
    $pdo = getcom();
    $req = "SELECT COUNT(*) AS count FROM realisation";
    $stmt = $pdo->prepare($req);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    $stmt->closeCursor();

    sendJSON(['count' => $count]);
}

?>