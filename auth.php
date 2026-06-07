<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// 1. CONEXÃO COM O BANCO DE DADOS
$host = "localhost";
$db_name = "Urbani_BD";
$username = "root"; 
$password = ""; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erro de conexão: " . $e->getMessage()]);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$inputData = json_decode(file_get_contents("php://input"), true);

if (!$inputData && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode(["success" => false, "message" => "Dados ausentes ou inválidos."]);
    exit;
}

switch ($action) {
    case 'register':
        handleRegister($pdo, $inputData);
        break;
    case 'login':
        handleLogin($pdo, $inputData);
        break;
    default:
        echo json_encode(["success" => false, "message" => "Ação inválida."]);
        break;
}

// 2. ROTINA DE CRIAÇÃO DE CONTA (CADASTRO)
function handleRegister($pdo, $data) {
    $nome     = isset($data['Nome']) ? trim($data['Nome']) : '';
    $email    = isset($data['e_mail']) ? trim($data['e_mail']) : '';
    $cpf      = isset($data['CPF']) ? preg_replace('/\D/', '', $data['CPF']) : '';
    $dataNasc = isset($data['Data_nascimento']) ? $data['Data_nascimento'] : '';
    $cidade   = isset($data['Cidade']) ? trim($data['Cidade']) : '';
    $bairro   = isset($data['Bairro']) ? trim($data['Bairro']) : '';
    $senha    = isset($data['senha']) ? $data['senha'] : '';

    // Validação de campos obrigatórios conforme as restrições NOT NULL do banco
    if (empty($nome) || empty($email) || empty($cpf) || empty($dataNasc) || empty($cidade) || empty($bairro) || empty($senha)) {
        echo json_encode(["success" => false, "message" => "Todos os campos do formulário são obrigatórios."]);
        return;
    }

    try {
        // Verifica duplicidade de CPF ou E-mail antes de inserir
        $stmt = $pdo->prepare("SELECT id_cidadao FROM Cidadao WHERE CPF = :cpf OR e_mail = :email LIMIT 1");
        $stmt->execute([':cpf' => $cpf, ':email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode(["success" => false, "message" => "Este CPF ou E-mail já está cadastrado."]);
            return;
        }

        // Criptografia segura da senha (Blowfish/BCRYPT)
        $senhaHash = password_hash($senha, PASSWORD_BCRYPT);

        $sql = "INSERT INTO Cidadao (CPF, Nome, e_mail, Data_nascimento, Cidade, Bairro, senha) 
                VALUES (:cpf, :nome, :email, :dataNasc, :cidade, :bairro, :senha)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cpf'      => $cpf,
            ':nome'     => $nome,
            ':email'    => $email,
            ':dataNasc' => $dataNasc,
            ':cidade'   => $cidade,
            ':bairro'   => $bairro,
            ':senha'    => $senhaHash
        ]);

        echo json_encode(["success" => true, "message" => "Conta criada com sucesso!"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Erro no banco de dados: " . $e->getMessage()]);
    }
}

// 3. ROTINA DE AUTENTICAÇÃO (LOGIN)
function handleLogin($pdo, $data) {
    $email = isset($data['e_mail']) ? trim($data['e_mail']) : '';
    $senha = isset($data['senha']) ? $data['senha'] : '';

    if (empty($email) || empty($senha)) {
        echo json_encode(["success" => false, "message" => "Preencha todos os campos."]);
        return;
    }

    try {
        // Captura o usuário e o hash da senha associado ao e-mail informado
        $stmt = $pdo->prepare("SELECT id_cidadao, Nome, senha FROM Cidadao WHERE e_mail = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // Valida o hash da senha de forma criptograficamente segura
        if ($user && password_verify($senha, $user['senha'])) {
            unset($user['senha']); // Remove o hash por motivos de segurança antes do retorno
            echo json_encode(["success" => true, "user" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "E-mail ou senha incorretos."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Erro no banco de dados: " . $e->getMessage()]);
    }
}
?>