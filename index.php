<?php
header("Access-Control-Allow-Origin: *");
// Inclure functions.php
require_once('./functions.php');

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $url = $_SERVER["REQUEST_URI"];
    $url = trim($url, "\/");
    $url = explode("/", $url);
    $action = $url[1];

    if ($action == "getuserlist") {
        getListUsers();
    }
} else {
    // ce que l'utilisateur envoi via un formulaire , on le récupère dans la variable $data
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data["action"] == "login") {
        // Appel de la fonction login
        login($data["pseudo"], $data["motdepasse"]);
    } else if ($data["action"] == "register") {
        // On fait appel à la fonction register pour enregistrer le user
        register($data["nom"], $data["prenom"], $data["pseudo"], $data["motdepasse"]);
    } else if ($data["action"] == "send message") {
        // Appel de la fonction sendMessage
        sendMessage($data["expediteur"], $data["destinataire"], $data["message"]);
    } else {
        json_encode([
            "status" => 404,
            "message" => "Service not found"
        ]);
    }
}