<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// 1. CONEXÃO COM O BANCO DE DADOS USANDO MYSQLI
$host = "localhost";
$db_name = "Urbani_bd";
$username = "root";
$password = "usbw";

$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Erro de conexão: " . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8");

// 2. CAPTURA DA AÇÃO E DOS DADOS RECEBIDOS
$action = isset($_GET["action"]) ? $_GET["action"] : "";
$inputData = json_decode(file_get_contents("php://input"), true);

if (!$inputData && $_SERVER["REQUEST_METHOD"] === "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Dados ausentes ou inválidos."
    ]);
    exit;
}

switch ($action) {
    case "register":
        handleRegister($conn, $inputData);
        break;

    case "login":
        handleLogin($conn, $inputData);
        break;

    default:
        echo json_encode([
            "success" => false,
            "message" => "Ação inválida."
        ]);
        break;
}

// 3. ROTINA DE CADASTRO
function handleRegister($conn, $data) {
    $nome = isset($data["nome"]) ? trim($data["nome"]) : "";
    $email = isset($data["email"]) ? trim($data["email"]) : "";
    $cpf = isset($data["cpf"]) ? preg_replace("/\D/", "", $data["cpf"]) : "";
    $dataNascimento = isset($data["data_nascimento"]) ? $data["data_nascimento"] : "";
    $cidade = isset($data["cidade"]) ? trim($data["cidade"]) : "";
    $bairro = isset($data["bairro"]) ? trim($data["bairro"]) : "";
    $telefone = isset($data["telefone"]) ? trim($data["telefone"]) : "";
    $senha = isset($data["senha"]) ? $data["senha"] : "";

    if (
        empty($nome) ||
        empty($email) ||
        empty($cpf) ||
        empty($dataNascimento) ||
        empty($cidade) ||
        empty($bairro) ||
        empty($senha)
    ) {
        echo json_encode([
            "success" => false,
            "message" => "Todos os campos obrigatórios devem ser preenchidos."
        ]);
        return;
    }

    if (strlen($cpf) !== 11) {
        echo json_encode([
            "success" => false,
            "message" => "O CPF deve conter exatamente 11 dígitos."
        ]);
        return;
    }

    // Verifica se CPF ou e-mail já existem
    $sqlVerifica = "SELECT id_usuario FROM usuarios WHERE cpf = ? OR email = ? LIMIT 1";
    $stmt = $conn->prepare($sqlVerifica);

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Erro ao preparar verificação: " . $conn->error
        ]);
        return;
    }

    $stmt->bind_param("ss", $cpf, $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Este CPF ou e-mail já está cadastrado."
        ]);
        $stmt->close();
        return;
    }

    $stmt->close();

    // Criptografa a senha
    $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

    // Insere o usuário
    $sqlInsert = "
        INSERT INTO usuarios (
            nome,
            email,
            cpf,
            data_nascimento,
            cidade,
            bairro,
            telefone,
            senha,
            tipo_usuario,
            status_usuario
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cidadao', 'ativo')
    ";

    $stmt = $conn->prepare($sqlInsert);

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Erro ao preparar cadastro: " . $conn->error
        ]);
        return;
    }

    $stmt->bind_param(
        "ssssssss",
        $nome,
        $email,
        $cpf,
        $dataNascimento,
        $cidade,
        $bairro,
        $telefone,
        $senhaHash
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Conta criada com sucesso!"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Erro ao cadastrar usuário: " . $stmt->error
        ]);
    }

    $stmt->close();
}

// 4. ROTINA DE LOGIN
function handleLogin($conn, $data) {
    $email = isset($data["email"]) ? trim($data["email"]) : "";
    $senha = isset($data["senha"]) ? $data["senha"] : "";

    if (empty($email) || empty($senha)) {
        echo json_encode([
            "success" => false,
            "message" => "Preencha todos os campos."
        ]);
        return;
    }

    $sql = "
        SELECT
            id_usuario,
            nome,
            email,
            senha,
            tipo_usuario,
            status_usuario
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "message" => "Erro ao preparar login: " . $conn->error
        ]);
        return;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode([
            "success" => false,
            "message" => "E-mail ou senha incorretos."
        ]);
        $stmt->close();
        return;
    }

    $user = $resultado->fetch_assoc();

    if ($user["status_usuario"] !== "ativo") {
        echo json_encode([
            "success" => false,
            "message" => "Usuário inativo."
        ]);
        $stmt->close();
        return;
    }

    if (!password_verify($senha, $user["senha"])) {
        echo json_encode([
            "success" => false,
            "message" => "E-mail ou senha incorretos."
        ]);
        $stmt->close();
        return;
    }

    unset($user["senha"]);

    echo json_encode([
        "success" => true,
        "user" => $user
    ]);

    $stmt->close();
}

$conn->close();
?>