<?php
//Valida que los campso no esten en blanco
function textoblanco($texto){
  $conv= array(" " => "");
  //Guardamos el resultado en una variable
  $textblanco = strtr($texto, $conv);
  /* Cuenta cuantos caracteres tiene el texto */
  $cont = strlen($textblanco);
  /* Retornamos la cantidad */
  return $cont;
}


/*extract($_POST);

$sentencia = "INSERT INTO contacto_clientes(ciudad, nombre_cliente, correo_cliente, telefono_cliente, nro_documento, modelo, fecha_compra, mensaje_cliente, fecha_registro, condiciones) VALUES ('$ciudad' ,'$nombre', '$correo', '$telefono', '$documento', '$modelo', '$fecha_compra', '$mensaje', NOW(), '$acepto')";

$resent = mysqli_query($hyundai, $sentencia);


if($resent!=null) {

}else{
  header("location: 404.html");
}

mysqli_close($hyundai);
*/

/**
* This example shows making an SMTP connection with authentication.
*/

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

//require 'vendor/autoload.php';

//require 'vendor/phpmailer/class.phpmailer.php';

require 'PHPMailer/PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;
//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;
//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
//Set the hostname of the mail server
$mail->Host = 'smtp.gmail.com';
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = 587;


$mail->SMTPSecure = 'tls';
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = 'hysoporte018000@gmail.com';
//Password to use for SMTP authentication
$mail->Password = 'hy123456789';
//Set who the message is to be sent from
$mail->setFrom('hysoporte018000@gmail.com', 'Mensajes Web');
//Set an alternative reply-to address
//$mail->addReplyTo('lider.servicioalcliente@hyundailatinoamerica.com', 'Alejandro Gaviria');
//Set who the message is to be sent to
$mail->addAddress('jf.arenas30@ciaf.edu.co', 'email 1');
//$mail->addAddress('analistamercadeo@hyundailatinoamerica.com', 'Servicio al Cliente');
//Set the subject line
$mail->Subject = "Mensaje Web México";
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML('Pruebas de app ahorro');
//Replace the plain text body with one created manually
//$mail->AtlBody = $mensaje;
//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//adjuntamos un archivo
//$mail->AddAttachment($archivo['tmp_name'], $archivo['name']);


$mail->CharSet = 'UTF-8';

//send the message, check for errors
if (!$mail->send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
  echo "No se ha podido enviar el mensaje.";
} else {
  echo "Ok";
}


?>
