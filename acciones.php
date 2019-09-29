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

		$db = new Bd();
		$db->conectar();

		$db->sentencia("INSERT INTO usuarios (nombres, apellidos, email, pass, pin) VALUES (:nombres, :apellidos, :email, :pass, :pin)  ", array(":nombres" => $_REQUEST["nombres"], ":apellidos" => $_REQUEST["apellidos"], ":email" => $_REQUEST["email"], ":pass" => encriptarPass($_REQUEST["pass"]), ":pin"=> "0000"));

		$db->desconectar();
		
		return 1;

	}

   function AgregarAhorro(){
	$db = new Bd();
	$db->conectar();

	$db->sentencia("INSERT INTO ahorros (fk_id_usuario, nombre, objetivo, ahorrado, intervalo) VALUES (:fk_id_usuario, :nombre, :objetivo, :ahorrado, :intervalo)  ", array(":fk_id_usuario" => $_REQUEST["idUsuario"], ":nombre" => $_REQUEST["nombre"], ":objetivo" => $_REQUEST["objetivo"], ":ahorrado" => $_REQUEST["ahorrado"], ":intervalo" => $_REQUEST["intervalo"]));

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