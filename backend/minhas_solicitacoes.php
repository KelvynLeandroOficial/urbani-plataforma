<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
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

$id_usuario = isset($_GET["id_usuario"]) ? intval($_GET["id_usuario"]) : 0;

if ($id_usuario <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Usuário inválido. Faça login novamente."
    ]);
    exit;
}

$resumo = [
    "total" => 0,
    "aberta" => 0,
    "em_analise" => 0,
    "em_andamento" => 0,
    "resolvida" => 0,
    "cancelada" => 0
];

$sqlResumo = "
    SELECT status_solicitacao, COUNT(*) AS total
    FROM solicitacoes
    WHERE id_usuario = ?
    GROUP BY status_solicitacao
";

$stmtResumo = $conn->prepare($sqlResumo);

if (!$stmtResumo) {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao preparar resumo: " . $conn->error
    ]);
    exit;
}

$stmtResumo->bind_param("i", $id_usuario);
$stmtResumo->execute();
$resultadoResumo = $stmtResumo->get_result();

while ($row = $resultadoResumo->fetch_assoc()) {
    $status = $row["status_solicitacao"];
    $total = intval($row["total"]);

    if (isset($resumo[$status])) {
        $resumo[$status] = $total;
    }

    $resumo["total"] += $total;
}

$stmtResumo->close();

$sqlSolicitacoes = "
    SELECT
        id_solicitacao,
        titulo,
        descricao,
        rua,
        bairro,
        cidade,
        uf,
        status_solicitacao,
        urgencia,
        criado_em
    FROM solicitacoes
    WHERE id_usuario = ?
    ORDER BY criado_em DESC
";

$stmtSolicitacoes = $conn->prepare($sqlSolicitacoes);

if (!$stmtSolicitacoes) {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao preparar solicitações: " . $conn->error
    ]);
    exit;
}

$stmtSolicitacoes->bind_param("i", $id_usuario);
$stmtSolicitacoes->execute();
$resultadoSolicitacoes = $stmtSolicitacoes->get_result();

$solicitacoes = [];

while ($row = $resultadoSolicitacoes->fetch_assoc()) {
    $solicitacoes[] = $row;
}

$stmtSolicitacoes->close();
$conn->close();

echo json_encode([
    "success" => true,
    "resumo" => $resumo,
    "solicitacoes" => $solicitacoes
]);
?>