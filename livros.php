<?php
header("Content-type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "biblioteca"; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Falha na conexão: " . $conn->connect_error]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        if (isset($_GET['pesquisa'])) {
            $pesquisa = "%" . $_GET['pesquisa'] . "%";
            $stmt = $conn->prepare("SELECT * FROM livros WHERE Titulo LIKE ? OR Autor LIKE ?");
            $stmt->bind_param("ss", $pesquisa, $pesquisa);
            $stmt->execute();
            $result = $stmt->get_result();
        } else if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM livros WHERE ID=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT * FROM livros ORDER BY ID DESC");
        }

        $retorno = [];
        while ($linha = $result->fetch_assoc()) {
            $retorno[] = $linha;
        }

        echo json_encode($retorno);
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("INSERT INTO livros (Titulo, Autor, Disponivel) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $data['Titulo'], $data['Autor'], $data['Disponivel']);
        $stmt->execute();

        echo json_encode(["status" => "ok", "insert_id" => $stmt->insert_id]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $conn->prepare("UPDATE livros SET Titulo=?, Autor=?, Disponivel=? WHERE ID=?");
        $stmt->bind_param("ssii", $data['Titulo'], $data['Autor'], $data['Disponivel'], $data['ID']);
        $stmt->execute();

        echo json_encode(["status" => "ok"]);
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("DELETE FROM livros WHERE ID=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode(["status" => "ok"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ID não fornecido"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Método não permitido"]);
        break;
}

$conn->close();
?>
