<?php

// Fonction pour se connecter à la base de données :
function dbConnect()
{
    $db = null;
    try {
        $db = new PDO('mysql:host=localhost;dbname=api_db', "root", "");
    } catch (PDOException $error) {
        $db = $error->getMessage();
    }
    return $db;
}

// Fonction pour enregistrer un utilisateur à la dase de données :
function register($nom, $prenom, $pseudo, $mdp)
{
    $mdpHash = password_hash($mdp, PASSWORD_DEFAULT);
    // Se connecter à la base de données : 
    $db = dbConnect();

    // Préparer la requête
    $request = $db->prepare('INSERT INTO users (pseudo,motdepasse,nom,prenom) VALUES (?,?,?,?)');

    // Executer la requête : 
    try {
        $request->execute(array($pseudo, $mdpHash, $nom, $prenom));
        echo json_encode([
            "status" => 201,
            "message" => "Tout s'est bien passé"
        ]);
    } catch (PDOException $error) {
        echo json_encode([
            "status" => 500,
            "message" => "internal server error"
        ]);
    }
}

function login($pseudo, $mdp)
{
    // se connecter à la base de données
    $db = dbConnect();

    // Préparer la requête : 
    $request = $db->prepare('SELECT * FROM users WHERE pseudo = ?');

    // Executer la requête : 
    try {
        $request->execute(array($pseudo));
        $user = $request->fetch(PDO::FETCH_ASSOC);

        // On vérifie si l'utilisateur existe 
        if (empty($user)) {
            echo json_encode([
                "status" => 404,
                "message" => "User not found"
            ]);
        } else {
            // Vérifier si le mdp est correct 
            if (password_verify($mdp, $user['motdepasse'])) {
                echo json_encode([
                    "status" => 200,
                    "message" => "Félicitations, vous êtes connecté(e)",
                    "data" => $user
                ]);
            } else {
                echo json_encode([
                    "status" => 404,
                    "message" => "Password incorect"
                ]);
            }
        }
    } catch (PDOException $error) {
        echo json_encode([
            "status" => 500,
            "message" => $error->getMessage()
        ]);
    }
}

function sendMessage($expediteur, $destinataire, $message)
{
    // Se connecter à la base de données :
    $db = dbConnect();

    // Préparer la requête : 
    $request = $db->prepare('INSERT INTO messages (message, expediteur_id, destinataire_id) VALUES (?,?,?)');

    // Executer la requête:
    try {
        $request->execute(array($message, $expediteur, $destinataire));
        echo json_encode([
            "status" => 201,
            "message" => "Your message is safely sent..."
        ]);
    } catch (PDOException $error) {
        echo json_encode([
            "status" => 500,
            "message" => $error->getMessage()
        ]);
    }
}

// fonction pour récuperer la liste des utilisateurs : 
function getListUsers()
{
    // Se connecter à la base de données : 
    $db = dbConnect();

    // Préparation de la requête :
    $request = $db->prepare('SELECT id_user,pseudo,nom,prenom FROM users');

    // Execution de la requête :
    try {
        $request->execute();
        $listUsers = $request->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            "status" => 200,
            "message" => "Voici la liste des utilisateurs",
            "users" => $listUsers
        ]);
    } catch (PDOException $error) {
        echo json_encode([
            "status" => 500,
            "message" => $error->getMessage()
        ]);
    }
}

// fonction pour récuperer la conversation entre 2 users 
function getListMessage($expediteur, $destinataire)
{
    // Se connecter à la base de données :
    $db = dbConnect();

    // Préparer la requête : 
    $request = $db->prepare("SELECT * FROM messages WHERE expediteur_id = ? AND destinataire_id	 = ? OR expediteur_id = ? AND destinataire_id = ?");

    // Executer la requête 
    try {
        $request->execute(array($expediteur, $destinataire, $destinataire, $expediteur));
        // Récuperer le résultat dans un tableau 
        $messages = $request->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            "status" => 200,
            "message" => "Voici les messages de vos discussions",
            "listMessage" => $messages
        ]);
    } catch (PDOException $error) {
        echo json_encode([
            "status" => 500,
            "message" => $error->getMessage()
        ]);
    }
}