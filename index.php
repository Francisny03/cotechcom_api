<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("function/function.php");

try {
    if (!empty($_GET['api'])) {
        // Traitement des requêtes GET
        $url = explode("/", filter_var($_GET['api'],FILTER_SANITIZE_URL));
        switch ($url[0]) {
            //---------------
            case 'Services':
                if (empty($url[1])) {
                    getServices();
                } else {
                    getService($url[1]);
                }
                break;
            //---------------
            case 'CountServices':
                if (empty($url[1])) {
                    getCountServices();
                } else {
                    getCountServices();
                }
                break;
            //---------------
            case 'Slider':
                if (empty($url[1])) {
                    getSlider();
                } else {
                    getCountServices();
                }
                break;
            //---------------
            case 'CountSlider':
                if (empty($url[1])) {
                    getCountSlider();
                } else {
                    getCountSlider();
                }
                break;
            //---------------
            case 'equipe':
                if (empty($url[1])) {
                    getEquipe();
                } else {
                    throw new Exception("La demande n'est pas valide");
                }
                break;
            //---------------
            case 'Realisation':
                if (empty($url[1])) {
                    getrealisation();
                } else {
                    getrealisationById($url[1]);
                }
                break;
            //---------------
            case 'CountRealisation':
                if (empty($url[1])) {
                    getCountRealisation();
                } else {
                    getCountRealisation();
                }
                break;
            //---------------
            case 'Partenaire':
                if (empty($url[1])) {
                    getSlider();
                } else {
                    getCountPartenaire();
                }
                break;
            //---------------
            case 'CountPartenaire':
                if (empty($url[1])) {
                    getCountPartenaire();
                } else {
                    getCountPartenaire();
                }
                break;
            //---------------
            default:
                throw new Exception("La demande n'est pas valide");
                break;
        }
    } 
    
    
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $inputJSON = file_get_contents('php://input');
        $postData = json_decode($inputJSON, true);

        // Loguer les données reçues
        error_log("POST data: " . print_r($postData, true));

    if (!empty($postData['api'])) {
        $url = explode("/", filter_var($postData['api'], FILTER_SANITIZE_URL));
        switch ($url[0]) {
            case 'PostService':
                if (!empty($postData['data'])) {
                    $result = createServices($postData['data']);
                    sendJSON($result);
                } else {
                    throw new Exception("Données manquantes pour la création d'un utilisateur");
                }
                break;
            //---------------
            case 'UpdateServices':
                if (!empty($postData['data'])) {
                    $result = UpdateServices($postData['data']);
                    sendJSON($result);
                } else {
                    throw new Exception("Données manquantes pour la création d'un utilisateur");
                }
                break;
            //---------------
            case 'Slider':
                if (!empty($postData['data'])) {
                    $result = createSleder($postData['data']);
                    sendJSON($result);
                } else {
                    throw new Exception("Données manquantes pour la création d'un utilisateur");
                }
                break;
            //---------------
            case 'PostRealisation':
                if (!empty($postData['data'])) {
                    $result = Postrealisation($postData['data']);
                    sendJSON($result);
                } else {
                    throw new Exception("Données manquantes pour la création d'un utilisateur");
                }
                break;
            //---------------
            case 'PostPartenaire':
                if (!empty($postData['data'])) {
                    $result = createPartenaire($postData['data']);
                    sendJSON($result);
                } else {
                    throw new Exception("Données manquantes pour la création d'un utilisateur");
                }
                break;
            //---------------
            default:
                throw new Exception("La demande POST n'est pas valide");
        }
    } else {
        throw new Exception("Problème de récupération de l'API");
    }
    
    } else {
        throw new Exception("Méthode non autorisée");
    }
} catch(Exception $e) {
    $erreur = [
        "message" => $e->getMessage(),
        "code" => $e->getCode()
    ];
    print_r($erreur);
}
?>