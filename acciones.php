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
		$db = new Bd();
		$db->conectar();

		if (validarCantidadAhorros($_REQUEST["fk_id_usuario"]) < 3 ) {
			$db->sentencia("INSERT INTO ahorros (fk_id_usuario, nombre, objetivo, ahorrado, intervalo, fechaMeta, proposito) VALUES (:fk_id_usuario, :nombre, :objetivo, :ahorrado, :intervalo, :fechaMeta, :proposito)", array(":fk_id_usuario" => $_REQUEST["fk_id_usuario"], ":nombre" => $_REQUEST["nombreAhorro"], ":objetivo" => $_REQUEST["objetivo"], ":ahorrado" =>'0', ":intervalo" => $_REQUEST["intervalo"], ":fechaMeta" => "", ":proposito" => $_REQUEST["proposito"]));
			$resp["success"] = true;
			$resp['msj'] = "Se ha registrado correctamente";
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
		$resp;
		$db = new Bd();
		$db->conectar();
	
		$sql = $db->consulta("SELECT * FROM ahorros WHERE fk_id_usuario = :idUsuario", array(":idUsuario" =>  $_REQUEST["idUsuario"] ));
		
		$db->desconectar();
		
		return json_encode($sql);
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