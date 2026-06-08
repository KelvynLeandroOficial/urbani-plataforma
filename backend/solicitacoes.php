<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

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

$inputData = json_decode(file_get_contents("php://input"), true);

if (!$inputData) {
    echo json_encode([
        "success" => false,
        "message" => "Dados ausentes ou inválidos."
    ]);
    exit;
}

$id_usuario = isset($inputData["id_usuario"]) ? intval($inputData["id_usuario"]) : 0;
$id_categoria = isset($inputData["id_categoria"]) ? intval($inputData["id_categoria"]) : 0;
$titulo = isset($inputData["titulo"]) ? trim($inputData["titulo"]) : "";
$descricao = isset($inputData["descricao"]) ? trim($inputData["descricao"]) : "";
$cep = isset($inputData["cep"]) ? trim($inputData["cep"]) : "";
$rua = isset($inputData["rua"]) ? trim($inputData["rua"]) : "";
$bairro = isset($inputData["bairro"]) ? trim($inputData["bairro"]) : "";
$cidade = isset($inputData["cidade"]) ? trim($inputData["cidade"]) : "";
$uf = isset($inputData["uf"]) ? strtoupper(trim($inputData["uf"])) : "";
$urgencia = isset($inputData["urgencia"]) ? trim($inputData["urgencia"]) : "media";

if (
    $id_usuario <= 0 ||
    $id_categoria <= 0 ||
    empty($titulo) ||
    empty($descricao) ||
    empty($bairro) ||
    empty($cidade)
) {
    echo json_encode([
        "success" => false,
        "message" => "Preencha todos os campos obrigatórios."
    ]);
    exit;
}

if (!in_array($urgencia, ["baixa", "media", "alta"])) {
    $urgencia = "media";
}

$sqlUsuario = "SELECT id_usuario FROM usuarios WHERE id_usuario = ? LIMIT 1";
$stmtUsuario = $conn->prepare($sqlUsuario);

if (!$stmtUsuario) {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao preparar verificação de usuário: " . $conn->error
    ]);
    exit;
}

$stmtUsuario->bind_param("i", $id_usuario);
$stmtUsuario->execute();
$resultadoUsuario = $stmtUsuario->get_result();

if ($resultadoUsuario->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Sessão inválida. Faça login novamente."
    ]);
    $stmtUsuario->close();
    exit;
}

$stmtUsuario->close();

$sql = "
    INSERT INTO solicitacoes (
        id_usuario,
        id_categoria,
        titulo,
        descricao,
        cep,
        rua,
        bairro,
        cidade,
        uf,
        status_solicitacao,
        urgencia
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aberta', ?
    )
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao preparar solicitação: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "iissssssss",
    $id_usuario,
    $id_categoria,
    $titulo,
    $descricao,
    $cep,
    $rua,
    $bairro,
    $cidade,
    $uf,
    $urgencia
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Solicitação cadastrada com sucesso!"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao cadastrar solicitação: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>