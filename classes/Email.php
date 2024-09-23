<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {
    protected $email;
    protected $nombre;
    protected $token;
    
    public function __construct($email, $nombre, $token)
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion(){
        $mail = new PHPMailer(); //Todas estas credenciales nos las da mailtrap
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];  
        
        $mail -> setFrom('cuentas@uptask.com'); //Todo esto es información que le llega al usuario
        $mail -> addAddress('cuentas@uptask.com', 'uptask.com');
        $mail -> Subject = 'Confirma tu cuenta'; //El asunto del mail

        $mail->isHTML(TRUE); //Para decir que el mail se va a enviar en HTML
        $mail->CharSet = 'UTF-8'; //Por el tema de acentos, ñ y demás caracteres extraños

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has creado una cuenta en UpTask. Solo tienes que confirmarla en el siguiente enlace </p>";
        $contenido .= "<p>Presiona aquí: <a href='http://localhost:3000/confirmar?token=". $this->token . "'>Confirmar cuenta</a></p>";
        $contenido .= "<p> Si no creaste una cuenta, puedes ignorar este mensaje </p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;

        //Enviamos el email
        $mail->send();

    }

    public function enviarInstrucciones(){
        $mail = new PHPMailer(); //Todas estas credenciales nos las da mailtrap
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];   
        
        $mail -> setFrom('cuentas@uptask.com'); //Todo esto es información que le llega al usuario
        $mail -> addAddress('cuentas@uptask.com', 'uptask.com');
        $mail -> Subject = 'Reestablece tu contraseña'; //El asunto del mail

        $mail->isHTML(TRUE); //Para decir que el mail se va a enviar en HTML
        $mail->CharSet = 'UTF-8'; //Por el tema de acentos, ñ y demás caracteres extraños

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Parece que has olvidado tu contraseña. Sigue el siguiente enlace para conseguir una nueva </p>";
        $contenido .= "<p>Presiona aquí: <a href='http://localhost:3000/reestablecer?token=". $this->token . "'>Reestablecer tu contraseña</a></p>";
        $contenido .= "<p> Si no creaste una cuenta, puedes ignorar este mensaje </p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;

        //Enviamos el email
        $mail->send();

    }
}