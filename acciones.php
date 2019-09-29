<?php
	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");


 	require_once("Conectar.php");
	require_once("funciones_generales.php");

	function conexion(){
		return 1;
	}
  
	function iniciarSesion(){
		$db = new Bd();
		$db->conectar();

		$sql = $db->consulta("SELECT * FROM usuarios WHERE email = :email AND pass = :pass", array(":email" => $_REQUEST["email"], ":pass" => encriptarPass($_REQUEST["password"])));

		$db->desconectar();
		
		return json_encode($sql);
	}

  	function registro(){
		$resp;
   		 $db = new Bd();
		$db->conectar();

		if(validarCorreo($_REQUEST["email"]) == 0){
			$db->sentencia("INSERT INTO usuarios (nombres, apellidos, email, pass, pin) VALUES (:nombres, :apellidos, :email, :pass, :pin)  ", array(":nombres" => $_REQUEST["nombres"], ":apellidos" => $_REQUEST["apellidos"], ":email" => $_REQUEST["email"], ":pass" => encriptarPass($_REQUEST["pass"]), ":pin"=> "0000"));
			$resp["success"] = true;
			$resp['msj'] = "Se ha registrado correctamente";
		}else{
			$resp["success"] = false;
			$resp['msj'] = "El correo ya se encuentra registrado";
		}

		$db->desconectar();
		
		return json_encode($resp);
	}

	function validarCorreo($email){
		$db = new Bd();
		$db->conectar();

		$sql = $db->consulta("SELECT * FROM usuarios WHERE email = :email", array(":email" => $email));

		$db->desconectar();

		return $sql["cantidad_registros"];
	}

  function agregarAhorro(){
		$uniqid = uniqid();
		$monto = 0;
		$montoDef = 0; 
		$db = new Bd();
		$db->conectar();

		if (validarCantidadAhorros($_REQUEST["fk_id_usuario"]) < 3 ) {
			$db->sentencia("INSERT INTO ahorros (fk_id_usuario, nombre, objetivo, ahorrado, intervalo, fechaMeta, proposito, uniqid) VALUES (:fk_id_usuario, :nombre, :objetivo, :ahorrado, :intervalo, :fechaMeta, :proposito, :uniqid)", array(":fk_id_usuario" => $_REQUEST["fk_id_usuario"], ":nombre" => $_REQUEST["nombreAhorro"], ":objetivo" => $_REQUEST["objetivo"], ":ahorrado" =>'0', ":intervalo" => $_REQUEST["intervalo"], ":fechaMeta" => "", ":proposito" => $_REQUEST["proposito"], ":uniqid" => $uniqid));

			$sql = $db->consulta("SELECT * FROM ahorros WHERE uniqid = :uniqid", array(":uniqid" => $uniqid));

			if ($sql["cantidad_registros"] == 1) {
				
				while($montoDef < $_REQUEST["objetivo"]){
					$monto +=  $_REQUEST["intervalo"];
					$montoDef += $monto;
					$db->sentencia("INSERT INTO montos (fk_id_ahorro, intervalo, chec) VALUES(:fk_id_ahorro, :intervalo, :chec)", array(":fk_id_ahorro" => $sql[0]["id"], ":intervalo" => $monto, ":chec" => 0)); 
				}

				$resp["success"] = true;
				$resp['msj'] = "Se ha registrado correctamente";
			}else{
				$resp["success"] = false;
				$resp['msj'] = "No se ha creado el ahorro";
			}
		}else{
			$resp["success"] = false;
			$resp['msj'] = "Ya tienes tres ahorros creados";
		}

		$db->desconectar();
		return json_encode($resp);
	}
	
	function validarCantidadAhorros($idUsuario){
		$db = new Bd();
		$db->conectar();

		$sql = $db->consulta("SELECT * FROM ahorros WHERE fk_id_usuario = :fk_id_usuario", array(":fk_id_usuario" => $idUsuario));

		$db->desconectar();

		return $sql["cantidad_registros"];
	}

	function traerAhorros(){
		$db = new Bd();
		$db->conectar();
	
		$sql = $db->consulta("SELECT * FROM ahorros WHERE fk_id_usuario = :idUsuario", array(":idUsuario" =>  $_REQUEST["idUsuario"] ));
		
		$db->desconectar();
		
		return json_encode($sql);
	}

	function enviarPin(){
		$resp;
		$correoDestino = $_REQUEST["email"];

		if( validarCorreo($correoDestino) ==1){

			date_default_timezone_set('Etc/UTC');
	
			require 'PHPMailer/PHPMailerAutoload.php';
		
			//Create a new PHPMailer instance
			$mail = new PHPMailer;
			//Tell PHPMailer to use SMTP
			$mail->isSMTP();
		
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
			$mail->addAddress($correoDestino, 'email 1');
			//$mail->addAddress('analistamercadeo@hyundailatinoamerica.com', 'Servicio al Cliente');
			//Set the subject line
			$mail->Subject = "Pin recuperacion password - PersonalBanca";
			//Read an HTML message body from an external file, convert referenced images to embedded,
			//convert HTML into a basic plain-text alternative body
			$pin = generarPin();
			$mail->msgHTML('Pin de recuperacion: '.$pin);
			setearPin($pin, $correoDestino);
		
		
			$mail->CharSet = 'UTF-8';
		
			//send the message, check for errors
			if (!$mail->send()) {
				echo "Mailer Error: " . $mail->ErrorInfo;
				echo "No se ha podido enviar el mensaje.";
				$resp['success'] = false;
			} else {
				$resp['success'] = true;
			}
		}else{
			$resp['success'] = false;
		}
		return json_encode($resp);
	}
	
	function generarPin(){
		return rand( 0 ,  99999 );
	}
	
	function setearPin($pin, $email){
		$db = new Bd();
		$db->conectar();

		$db->sentencia("UPDATE usuarios SET pin = :pin where email= :email ", array(":pin" => $pin, ":email" => $email));
		$db->desconectar();

	}

	function validarPin(){
		$resp;
		$db = new Bd();
		$db->conectar();
	
		$sql = $db->consulta("SELECT * FROM usuarios WHERE email = :email and pin = :pin", array(":email" =>  $_REQUEST["email"], ":pin" =>  $_REQUEST["pin"] ));
		
		$db->desconectar();

		if($sql['cantidad_registros'] > 0){
			$resp['success'] = true;
		}else {
			$resp['success'] = false;
		}
		
		return json_encode($resp);
	}
	

	function nuevaPassword(){
		$db = new Bd();
		$db->conectar();

		$db->sentencia("UPDATE usuarios SET pass = :pass where email= :email ", array(":pass" => encriptarPass($_REQUEST["pass"]), ":email" => $_REQUEST["email"]));
		$db->desconectar();

		return 1;

	}

	function datosAhorro(){
		$db = new Bd();
		$db->conectar();

		$sql = $db->consulta("SELECT * FROM ahorros WHERE id = :id", array(":id" => $_REQUEST["idAhorro"]));

		$db->desconectar();

		return json_encode($sql);
	}

	function datosMontos(){
		$db = new Bd();
		$db->conectar();

		$sql = $db->consulta("SELECT * FROM montos WHERE fk_id_ahorro = :fk_id_ahorro ORDER BY intervalo ASC", array(":fk_id_ahorro" => $_REQUEST["idAhorro"]));

		$db->desconectar();

		return json_encode($sql);
	}

	function actualizarMonto(){
		$db = new Bd();
		$db->conectar();

		$db->sentencia("UPDATE montos SET chec = :chec WHERE id = :id", array(":chec" => $_REQUEST["chec"], ":id" => $_REQUEST["id"]));

		$db->sentencia("UPDATE ahorros SET ahorrado = :ahorrado WHERE id = :id", array(":ahorrado" => $_REQUEST["ahorrado"], ":id" => $_REQUEST["idAhorro"]));

		$db->desconectar();

		return 1;
	}


  if(@$_REQUEST['accion']){
    if(function_exists($_REQUEST['accion'])){
      echo($_REQUEST['accion']());
    }else{
      echo 'Accion '.$_REQUEST['accion'].' no Existe';
    }
  }else{
    echo 'no se ha seleccionado alguna acciòn';
  }
?>