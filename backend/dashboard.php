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

try {
    // Resumo por status
    $resumo = [
    "aberta" => 0,
    "em_analise" => 0,
    "em_andamento" => 0,
    "resolvida" => 0,
    "cancelada" => 0,
    "total" => 0
];

    $sqlResumo = "
        SELECT 
            status_solicitacao,
            COUNT(*) AS total
        FROM solicitacoes
        GROUP BY status_solicitacao
    ";

    $resultadoResumo = $conn->query($sqlResumo);

    if ($resultadoResumo) {
        while ($row = $resultadoResumo->fetch_assoc()) {
            $status = $row["status_solicitacao"];
            $total = (int) $row["total"];

            if (isset($resumo[$status])) {
                $resumo[$status] = $total;
            }

            $resumo["total"] += $total;
        }
    }

    // Solicitações recentes
    $sqlRecentes = "
        SELECT 
            s.id_solicitacao,
            s.titulo,
            s.descricao,
            s.rua,
            s.bairro,
            s.cidade,
            s.uf,
            s.status_solicitacao,
            s.urgencia,
            s.criado_em,
            u.nome AS nome_usuario
        FROM solicitacoes s
        INNER JOIN usuarios u ON s.id_usuario = u.id_usuario
        ORDER BY s.criado_em DESC
        LIMIT 5
    ";

    $resultadoRecentes = $conn->query($sqlRecentes);

    $solicitacoes = [];

    if ($resultadoRecentes) {
        while ($row = $resultadoRecentes->fetch_assoc()) {
            $solicitacoes[] = $row;
        }
    }

    echo json_encode([
        "success" => true,
        "resumo" => $resumo,
        "solicitacoes" => $solicitacoes
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erro ao carregar dashboard: " . $e->getMessage()
    ]);
}

$conn->close();
?>