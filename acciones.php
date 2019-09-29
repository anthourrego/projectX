<?php
  require_once("Conectar.php");
  require_once("funciones_generales.php");

  function datosCurso(){
  	$db = new Bd();
  	$db->conectar();

  	$sql_mc = $db->consulta("SELECT * FROM mandino_curso WHERE mc_id = :mc_id", array(":mc_id" => $_POST['curso']));

  	$db->desconectar();
		
		if ($sql_mc['cantidad_registros'] > 0) {
  		return json_encode($sql_mc[0]);
		}else{
			return false;
		}  
  }

  //Funcion para saber cuantas lecciones tiene un curso
  function porcentajeCurso($curso, $usuario){
    $db = new Bd();
    $db->conectar();
    $cont = 0; 
    $contUsu = 0;
    $porcentaje = 0;

    $sql_select_cantidadLecciones = $db->consulta("SELECT * FROM mandino_curso INNER JOIN mandino_unidades ON fk_mc = mc_id INNER JOIN mandino_lecciones ON fk_mu = mu_id WHERE mc_id = :mc_id", array(":mc_id" => $curso));
    $cont = $sql_select_cantidadLecciones['cantidad_registros'];

    $sql_select_cantidadLecciones_usuario = $db->consulta("SELECT * FROM mandino_curso INNER JOIN mandino_unidades ON fk_mc = mc_id INNER JOIN mandino_lecciones ON fk_mu = mu_id INNER JOIN mandino_lecciones_visto AS mlv ON mlv.fk_ml = ml_id WHERE mc_id = :mc_id AND mlv.fk_usuario = :fk_usuario AND (mlv.mlv_taller_aprobo = 0 OR mlv.mlv_taller_aprobo = 2)", array(":mc_id" => $curso, ":fk_usuario" => $usuario));

    $contUsu = $sql_select_cantidadLecciones_usuario['cantidad_registros'];

		if ($cont > 0) {
			//Formulamos el porcentaje
			$porcentaje = ($contUsu * 100)/$cont;
		}

    $db->desconectar();
    return round($porcentaje);
	}

  function centralCursos(){
  	//Generamos la conexion con la base de datos
	  $curso = '';
	  $porcentaje = 0;
		$emp = ""; //Se guardan los valores con la empresas relacionadas con usuarios
		$emp_curso = ""; //Se guardan los valos de los cursos que tienen la empresas


  	$db = new Bd();
		$db->conectar();

		$sql_mc = $db->consulta("SELECT * FROM empresas_usuarios AS eu INNER JOIN empresas_cursos AS ec ON eu.fk_empresa = ec.fk_empresa INNER JOIN mandino_curso AS mc ON mc.mc_id = ec.fk_curso WHERE fk_usuario = :fk_usuario GROUP BY ec.fk_curso", array(":fk_usuario" => $_POST['id_usu']));
		
		if ($sql_mc['cantidad_registros'] > 0) {
			for ($i=0; $i < $sql_mc['cantidad_registros'] ; $i++) { 
				$sql_mcu = $db->consulta("SELECT * FROM mandino_curso_usuario WHERE fk_mc = :fk_mc AND id_usuario = :fk_usuario AND mcu_activo = 1", array(":fk_mc" => $sql_mc[$i]['mc_id'], ":fk_usuario" => $_POST['id_usu']));
				
				$sql_mu = $db->consulta("SELECT * FROM mandino_unidades WHERE fk_mc = :id", array(":id"=>$sql_mc[$i]['mc_id']));
	
				$porcentaje = porcentajeCurso($sql_mc[$i]['mc_id'], $_POST['id_usu']);
	
				if($sql_mcu['cantidad_registros'] == 1){
					if ($porcentaje == 0) {
						$curso .= '<div class="col-12 col-sm-6 col-lg-4 mt-4 card-deck">
												<div class="card m-shadow m-shadow-primary">
													<div class="card-body">
															<div class="row">
															<div class="col-10">
																<h3 class="card-title text-center mb-3">' . $sql_mc[$i]['mc_nombre'] . '</h3>
															</div>
															<div class="col-2">';
						if ($sql_mc[$i]['mc_descripcion'] != "") {
							$curso .= '<button class="btn btn-info" onclick="mostrarInfo(\''. $sql_mc[$i]['mc_descripcion'] .'\')"><i class="fas fa-info"></i></button>';
						}									
															
						$curso .= '</div>
														</div>
													</div>
													<div class="card-footer bg-transparent border-0">
														<div class="d-flex justify-content-between text-muted">
															<span>' . $porcentaje . '%</span>
															<span>' . $sql_mu['cantidad_registros'] . ' Unidades</span>
														</div>
														<div class="progress mb-4" style="height: 6px;">
															<div class="progress-bar" role="progressbar" style="width:' . $porcentaje . '%;" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100"></div>
														</div>
														<div class="text-center">
															<button class="btn btn-primary rounded-pill" onclick="unidades(' . $sql_mc[$i]['mc_id'] .')"><i class="fas fa-pencil-alt"></i> Iniciar</button>
														</div>
													</div>
												</div>
											</div>';
						}elseif ($porcentaje > 0 && $porcentaje < 100) {
							$curso .= '<div class="col-12 col-sm-6 col-lg-4 mt-4 card-deck">
													<div class="card border border-warning m-shadow m-shadow-warning">
														<div class="card-body">
															<div class="row">
																<div class="col-10">
																	<h3 class="card-title text-center mb-3">' . $sql_mc[$i]['mc_nombre'] . '</h3>
																</div>
															<div class="col-2">';
							if ($sql_mc[$i]['mc_descripcion'] != "") {
								$curso .= '<button class="btn btn-info" onclick="mostrarInfo(\''. $sql_mc[$i]['mc_descripcion'] .'\')"><i class="fas fa-info"></i></button>';
							}										
																
							$curso .= '</div>
															</div>
															
															</div>
															<div class="card-footer bg-transparent border-0">
															<div class="d-flex justify-content-between text-muted">
																<span>' . $porcentaje . '%</span>
																<span>' . $sql_mu['cantidad_registros'] . ' Unidades</span>
															</div>
															<div class="progress mb-4" style="height: 6px;">
																<div class="progress-bar bg-warning" role="progressbar" style="width:' . $porcentaje . '%;" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100"></div>
															</div>
															<div class="text-center">
																<button class="btn btn-warning rounded-pill" onclick="unidades(' . $sql_mc[$i]['mc_id'] .')"><i class="fas fa-user-edit"></i> Continuar</butotn>
																</div>
															</div>
														</div>
													</div>';
						}elseif ($porcentaje == 100) {
							$curso .= '<div class="col-12 col-sm-6 col-lg-4 mt-4 card-deck">
													<div class="card border border-info m-shadow m-shadow-info">
														<div class="card-body">
															<div class="row">
																<div class="col-10">
																	<h3 class="card-title text-center mb-3">' . $sql_mc[$i]['mc_nombre'] . '</h3>
																</div>
															<div class="col-2">';
							if ($sql_mc[$i]['mc_descripcion'] != "") {
								$curso .= '<button class="btn btn-info" onclick="mostrarInfo(\''. $sql_mc[$i]['mc_descripcion'] .'\')"><i class="fas fa-info"></i></button>';
							}										
																
							$curso .= '</div>
															</div>
													
														</div>
														<div class="card-footer bg-transparent border-0">
															<div class="d-flex justify-content-between text-muted">
																<span>' . $porcentaje . '%</span>
																<span>' . $sql_mu['cantidad_registros'] . ' Unidades</span>
															</div>
															<div class="progress mb-4" style="height: 6px;">
																<div class="progress-bar bg-info" role="progressbar" style="width:' . $porcentaje . '%;" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100"></div>
															</div>
															<div class="text-center">
																<button class="btn btn-info rounded-pill" onclick="unidades(' . $sql_mc[$i]['mc_id'] .')"><i class="fas fa-star" style="color: #FFDD43;"></i> Finalizado</button>
															</div>
														</div>
													</div>
												</div>';
						}else{
							$curso .= '<div class="col-12 col-sm-6 col-lg-4 mt-4 card-deck">
													<div class="card border border-primary m-shadow m-shadow-primary">
														<div class="card-body">
															<div class="row">
																<div class="col-10">
																	<h3 class="card-title text-center mb-3">' . $sql_mc[$i]['mc_nombre'] . '</h3>
																</div>
															<div class="col-2">';
							if ($sql_mc[$i]['mc_descripcion'] != "") {
								$curso .= '<button class="btn btn-info" onclick="mostrarInfo(\''. $sql_mc[$i]['mc_descripcion'] .'\')"><i class="fas fa-info"></i></button>';
							}
																												
							$curso .= '</div>
														</div>
													</div>
														<div class="card-footer bg-transparent border-0">
															<div class="d-flex justify-content-between text-muted">
																<span>' . $porcentaje . '%</span>
																<span>' . $sql_mu['cantidad_registros'] . ' Unidades</span>
															</div>
															<div class="progress mb-4" style="height: 6px;">
																<div class="progress-bar" role="progressbar" style="width:' . $porcentaje . '%;" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100"></div>
															</div>
															<div class="text-center">
																<button class="btn btn-primary rounded-pill disabled" disabled><i class="fas fa-pencil-ruler"></i> Iniciar</button>
															</div>
														</div>
													</div>
												</div>';
						}
				}else{
					$curso .= '<div class="col-12 col-sm-6 col-lg-4 mt-4 card-deck">
													<div class="card border border-secondary m-shadow">
														<div class="card-body">
															<div class="row">
																<div class="col-10">
																	<h3 class="card-title text-center mb-3">' . $sql_mc[$i]['mc_nombre'] . '</h3>
																</div>
															<div class="col-2">';
							if ($sql_mc[$i]['mc_descripcion'] != "") {
								$curso .= '<button class="btn btn-secondary" onclick="mostrarInfo(\''. $sql_mc[$i]['mc_descripcion'] .'\')"><i class="fas fa-info"></i></button>';
							}
																									
							$curso .= '</div>
															</div>
													
														</div>
														<div class="card-footer bg-transparent border-0">
															<div class="d-flex justify-content-between text-muted">
																<span>' . $porcentaje . '%</span>
																<span>' . $sql_mu['cantidad_registros'] . ' Unidades</span>
															</div>
															<div class="progress mb-4" style="height: 6px;">
																<div class="progress-bar bg-secondary" role="progressbar" style="width:' . $porcentaje . '%;" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100"></div>
															</div>
															<div class="text-center">
																<a class="btn btn-secondary rounded-pill disabled" disabled href="#"><i class="fas fa-paper-plane"></i> Iniciar</a>
															</div>
														</div>
													</div>
												</div>';
				}
			}
		}

			

  	$db->desconectar();

  	return $curso;
		//return json_encode($sql_mc);
	}

    function datosUnidad(){
  	$db = new Bd();
  	$db->conectar();

  	$sql_mc = $db->consulta("SELECT * FROM mandino_unidades WHERE mu_id = :mu_id", array(":mu_id" => $_POST['unidad']));

  	$db->desconectar();
		
		if ($sql_mc['cantidad_registros'] > 0) {
  		return json_encode($sql_mc[0]);
		}else{
			return false;
		} 
  }
  
  function primerModulo($unidad){
    $db = new Bd();
    $db->conectar();
    $sql_primerLeccion = $db->consulta("SELECT mu_id FROM mandino_curso INNER JOIN mandino_unidades ON fk_mc = mc_id WHERE mc_id = :mc_id LIMIT 1", array(":mc_id" => $unidad));
    $db->desconectar();
    return $sql_primerLeccion[0]['mu_id']; 
  }

  function modulosUnidades(){
  	$lista = '';
  	$contCollapsed = 0;
	  $db = new Bd();
	  $db->conectar();
	  //titulo del curso
	  $sql_mc = $db->consulta("SELECT * FROM mandino_curso WHERE mc_id = :id_mc", array(":id_mc" => $_POST['curso']));
	 		
	 	for ($i=0; $i < $sql_mc['cantidad_registros']; $i++) { 

			$sql_mu = $db->consulta("SELECT * FROM mandino_unidades WHERE fk_mc = :fk_mc ORDER BY mu_orden ASC", array(":fk_mc" => $sql_mc[$i]['mc_id']));
			
			//$lista .= "<div class='row'>";

			for ($k=0; $k < $sql_mu['cantidad_registros']; $k++) { 
				$sql_ml = $db->consulta("SELECT * FROM mandino_lecciones WHERE fk_mu = :id_mu AND ISNULL(fk_ml) ORDER BY ml_orden ASC", array(":id_mu" => $sql_mu[$k]['mu_id']));
				
				if ($sql_ml['cantidad_registros'] > 0) {
					$cont_lecciones_vista = 0;
					//modificacion de colores en los botones de inicio
					$sql_select_ml_mlv = $db->consulta("SELECT * FROM mandino_lecciones INNER JOIN mandino_lecciones_visto AS mlv ON mlv.fk_ml = ml_id WHERE fk_mu = :fk_mu AND fk_usuario = :fk_usuario AND mlv.mlv_taller_aprobo != 1", array(":fk_mu" => $sql_mu[$k]['mu_id'], ":fk_usuario" => $_POST['user']));

					if($sql_select_ml_mlv['cantidad_registros'] == 0 && primerModulo($sql_mc[$i]['mc_id']) == $sql_mu[$k]['mu_id']){
						$lista .= '<div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-4 card-deck">
								<div class="card bg-light mb-4 border-bottom m-shadow m-shadow-primary border-primary">
									<div class="font-weight-bold d-flex justify-content-between card-header">
										<div>' . $sql_mu[$k]['mu_nombre'] . '</div>
										<div style="position: relative; top: -25px; margin-bottom: -40px;">
											<i class="fas fa-3x fa-bookmark text-primary" ></i>
											<p class="text-center text-white" style="position: relative; top: -40px; margin-bottom: -40px;">' . $sql_mu[$k]['mu_orden'] . '</p>
										</div>
									</div>
									<div class="card-body">
										<p class="card-text">
											<ul>';
						for ($a=0; $a < $sql_ml['cantidad_registros']; $a++) { 
							$lista .= '<li>' . $sql_ml[$a]['ml_nombre'] . '</li>';
						}
						$lista .= '</ul>
											</p>          
											</div>
											<div class="card-footer text-center">
												<button class="btn btn-primary rounded-pill" onClick="leccion(' . $sql_mu[$k]['mu_id'] . ', ' . $_POST['curso'] . ')"><i class="fas fa-pencil-alt"></i> Iniciar</button>
											</div>
										</div>
									</div>';
					}elseif ($sql_select_ml_mlv['cantidad_registros'] == $sql_ml['cantidad_registros']) {
						$lista .= '<div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-4 card-deck mt-2">
								<div class="card bg-light mb-4 border-bottom m-shadow m-shadow-info border-info">
									<div class="font-weight-bold d-flex justify-content-between card-header">
										<div>' . $sql_mu[$k]['mu_nombre'] . '</div>
										<div style="position: relative; top: -25px; margin-bottom: -40px;">
											<i class="fas fa-3x fa-bookmark text-info" ></i>
											<p class="text-center text-white" style="position: relative; top: -40px; margin-bottom: -40px;">' . $sql_mu[$k]['mu_orden'] . '</p>
										</div>
									</div>
									<div class="card-body">
										<p class="card-text">';
						$lista .= '<ul>';
						for ($a=0; $a <$sql_ml['cantidad_registros']; $a++) { 
							$lista .= '<li>' . $sql_ml[$a]['ml_nombre'] . '</li>';
						}
						$lista .= '</ul>';
						$lista .= '</p>          
											</div>
											<div class="card-footer text-center">
												<button class="btn btn-info rounded-pill" onClick="leccion(' . $sql_mu[$k]['mu_id'] . ', ' . $_POST['curso'] . ')"><i class="fas fa-star" style="color: #FFDD43;"></i> Finalizado</button>
											</div>
										</div>
									</div>';
					}elseif($sql_select_ml_mlv['cantidad_registros'] == 1){
						$lista .= '<div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-4 card-deck">
								<div class="card bg-light mb-4 border-bottom m-shadow m-shadow-primary border-primary">
									<div class="font-weight-bold d-flex justify-content-between card-header">
										<div>' . $sql_mu[$k]['mu_nombre'] . '</div>
										<div style="position: relative; top: -25px; margin-bottom: -40px;">
											<i class="fas fa-3x fa-bookmark text-primary" ></i>
											<p class="text-center text-white" style="position: relative; top: -40px; margin-bottom: -40px;">' . $sql_mu[$k]['mu_orden'] . '</p>
										</div>
									</div>
									<div class="card-body">
										<p class="card-text">';
						$lista .= '<ul>';
						for ($a=0; $a < $sql_ml['cantidad_registros']; $a++) { 
							$lista .= '<li>' . $sql_ml[$a]['ml_nombre'] . '</li>';
						}
						$lista .= '</ul>';
						$lista .= '</p>          
											</div>
											<div class="card-footer text-center">
												<button class="btn btn-primary rounded-pill" onClick="leccion(' . $sql_mu[$k]['mu_id'] . ', ' . $_POST['curso'] . ')"><i class="fas fa-pencil-alt"></i> Iniciar</button>
											</div>
										</div>
									</div>';
					}elseif($sql_select_ml_mlv['cantidad_registros'] > 1 && $sql_select_ml_mlv['cantidad_registros'] < $sql_ml['cantidad_registros']){
						$lista .= '<div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-4 card-deck">
								<div class="card bg-light mb-4 border-bottom m-shadow m-shadow-warning border-warning">
									<div class="font-weight-bold d-flex justify-content-between card-header">
										<div>' . $sql_mu[$k]['mu_nombre'] . '</div>
										<div style="position: relative; top: -25px; margin-bottom: -40px;">
											<i class="fas fa-3x fa-bookmark text-warning" ></i>
											<p class="text-center" style="position: relative; top: -40px; margin-bottom: -40px;">' . $sql_mu[$k]['mu_orden'] . '</p>
										</div>
									</div>
									<div class="card-body">
										<p class="card-text">';
						$lista .= '<ul>';
						for ($a=0; $a < $sql_ml['cantidad_registros']; $a++) { 
							$lista .= '<li>' . $sql_ml[$a]['ml_nombre'] . '</li>';
						}
						$lista .= '</ul>';
						$lista .= '</p>          
										</div>
										<div class="card-footer text-center">
											<button class="btn btn-warning rounded-pill" onClick="leccion(' . $sql_mu[$k]['mu_id'] . ', ' . $_POST['curso'] . ')"><i class="fas fa-user-edit"></i> Continuar</button>
										</div>
									</div>
								</div>';
					}else{
						$lista .= '<div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-4 card-deck">
								<div class="card bg-light mb-4 border-bottom">
									<div class="font-weight-bold d-flex justify-content-between card-header">
										<div>' . $sql_mu[$k]['mu_nombre'] . '</div>
										<div style="position: relative; top: -25px; margin-bottom: -40px;">
											<i class="fas fa-3x fa-bookmark text-secondary" ></i>
											<p class="text-center text-white" style="position: relative; top: -40px; margin-bottom: -40px;">' . $sql_mu[$k]['mu_orden'] . '</p>
										</div>
									</div>
									<div class="card-body">
										<p class="card-text">';
						$lista .= '<ul>';
						for ($a=0; $a < $sql_ml['cantidad_registros']; $a++) { 
							$lista .= '<li class="text-muted">' . $sql_ml[$a]['ml_nombre'] . '</li>';
						}
						$lista .= '</ul>';
						$lista .= '</p>          
										</div>
										<div class="card-footer text-center">
											<button class="btn btn-secondary rounded-pill disabled" disabled><i class="fas fa-pencil-ruler"></i> Iniciar</button>
										</div>
									</div>
								</div>';
					}
				}
			}
			
			//$lista .= "</div>";
	 	}

	  $db->desconectar();

	  return $lista; 
  }

  if(@$_REQUEST['accion']){
    if(function_exists($_REQUEST['accion'])){
      echo($_REQUEST['accion']());
    }
  }
?>