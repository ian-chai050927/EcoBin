<?php
namespace EcoBin\Services;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    public function __construct(private array $config) {}

    public function send(string $to, string $subject, string $html): bool
    {
        if (empty($this->config['enabled'])) {
            $line = sprintf(
                "[%s] TO=%s | SUBJECT=%s | %s\n",
                date('c'),
                $to,
                $subject,
                strip_tags($html)
            );
            file_put_contents(__DIR__ . '/../../storage/mail.log', $line, FILE_APPEND);
            return true;
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $this->config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->config['username'];
        $mail->Password = $this->config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int)$this->config['port'];
        $mail->setFrom($this->config['from_email'], $this->config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html;
        return $mail->send();
    }
}
