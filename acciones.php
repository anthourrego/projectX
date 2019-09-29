<?php
	header('Access-Control-Allow-Origin: *');
	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");


  require_once("Conectar.php");
	require_once("funciones_generales.php");

	function conexion(){
		return 1;
	}

  if(@$_REQUEST['accion']){
    if(function_exists($_REQUEST['accion'])){
      echo($_REQUEST['accion']());
    }
  }
?>