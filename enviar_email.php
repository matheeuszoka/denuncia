<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//Load Composer's autoloader
require './phpmailer/vendor/autoload.php';
require './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coleta e sanitiza os dados do formulário
    $denuncia = filter_input(INPUT_POST, 'denuncia', FILTER_SANITIZE_STRING);
    $anonimo = isset($_POST['anonimo']) ? 'Sim' : 'Não';
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING) ?? 'Não informado';
    $telefone = filter_input(INPUT_POST, 'telefone', FILTER_SANITIZE_STRING) ?? 'Não informado';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? 'Não informado';
    $ipAddress = filter_input(INPUT_POST, 'ipAddress', FILTER_SANITIZE_STRING);

    // Validação dos campos
    if (empty($denuncia)) {
        echo "<script>
                alert('Por favor, descreva a denúncia.');
                window.location.href = 'index.html';
              </script>";
        exit();
    }

    if ($anonimo === 'Não' && (empty($nome) || empty($telefone) || empty($email))) {
        echo "<script>
                alert('Por favor, preencha todos os campos obrigatórios.');
                window.location.href = 'index.html';
              </script>";
        exit();
    }

    // Cria uma instância do PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST']; // Servidor SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER']; // Seu e-mail
        $mail->Password   = $_ENV['SMTP_PASS']; // Sua senha ou senha de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // TLS ou SSL
        $mail->Port       = $_ENV['SMTP_PORT']; // Porta do SMTP
        $mail->CharSet = 'UTF-8';
        // Remetente e destinatário
        $mail->setFrom($_ENV['SMTP_USER'], 'Central de Denúncias');
        $mail->addAddress('informatica@2rdigital.com.br', 'Destinatário'); // E-mail do destinatário
        

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->Subject = 'Nova Denúncia Recebida';

        $html = "
            <h1>Nova Denúncia Recebida</h1>
            <p><strong>Descrição da denúncia:</strong></p>
            <p>$denuncia</p>
            <p><strong>Informações do denunciante:</strong></p>
            <ul>
                <li><strong>Anônimo:</strong> $anonimo</li>
                <li><strong>Nome:</strong> $nome</li>
                <li><strong>Telefone:</strong> $telefone</li>
                <li><strong>E-mail:</strong> $email</li>
                <li><strong>IP do denunciante:</strong> $ipAddress</li>
            </ul>
        ";

        $mail->Body    = $html;
        $mail->AltBody = strip_tags($html); // Versão em texto puro

        // Envia o e-mail
        $mail->send();
        echo "<script>
                alert('Denúncia enviada com sucesso!');
                window.location.href = 'index.html';
              </script>";
    } catch (Exception $e) {
        echo "<script>
                alert('Erro ao enviar denúncia: {$mail->ErrorInfo}');
                window.location.href = 'index.html';
              </script>";
    }
} else {
    // Redireciona se o formulário não foi enviado
    header("Location: index.html");
    exit();
}
?>