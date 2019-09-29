<?php
	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");


  require_once("Conectar.php");
	require_once("funciones_generales.php");

	function conexion(){
		return 1;
  }
  


	function inicarSesion(){
		$db = new Bd();
		$db->conectar();

		$sql = $db->consulta("SELECT * FROM usuarios WHERE email = :email AND pass = :pass", array(":email" => $_REQUEST["usuario"], ":pass" => $_REQUEST["password"]));

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