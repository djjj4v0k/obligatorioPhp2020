<?php

    //$baseUrl = 'C:/wamp/www/Obligatorio/';
    // CONEXION SQL
    function conectarSQL($ip = "localhost", $user = "root", $pass = ""){

        return mysqli_connect($ip, $user, $pass);
    }


    // CONEXION BD
    function conectarBD($conexion, $bd){

        return mysqli_select_db($conexion, $bd);

    }

    // CREAR CONEXION
    function crearConexion($ip, $user, $pass, $bd){

        $conexion = mysqli_connect($ip, $user, $pass, $bd);
        if($conexion)
            return $conexion;
        else
            return 1;
    }

    //CERRAR CONEXION
    function cerrarConexion($conexion){

        mysqli_close($conexion);
    }


    // INGRESO
    function ingreso($CI, $PIN, $conexion, $tipo, &$msjIngreso){
        
        if($tipo == "en"){

            $resultado = mysqli_query($conexion, "SELECT cedula, pin, nombres, apellidos FROM encargado WHERE cedula = '$CI' AND eliminado = '0'");
            
            if($resultado){

                $filaAsociativa = mysqli_fetch_array($resultado);
    
                $ciBD = $filaAsociativa["cedula"];
                $pinBD = $filaAsociativa["pin"];
                $nomBD = $filaAsociativa["nombres"];
                $apeBD = $filaAsociativa["apellidos"];
                $PIN_MD5 = md5($PIN);


                if($PIN_MD5 == $pinBD){

                    session_start();
                    $_SESSION["cedula"] = $ciBD;
                    $_SESSION["nombre"] = $nomBD;
                    $_SESSION["apellido"] = $apeBD;
                    $_SESSION["tipo"] = "en";
                    $_SESSION["tiempo"] = time();

                    cerrarConexion($conexion);
                    echo '<div class="msj ok">Iniciando sesion...</div>';
                    echo "<meta http-equiv='refresh' content='0.7;url=inicio.php?m=1'>";
                    die();
                } else {

                    $msjIngreso = "Cedula y PIN no coinciden, pruebe nuevamente.";
                    echo '<div class="msj error">'.$msjIngreso.'</div>';

                }
            } else {

                $msjIngreso = "No se encontro el encargado en el sistema.";
                echo '<div class="msj error">'.$msjIngreso.'</div>';
            }

        } else if($tipo == "tr"){

            $resultado = mysqli_query($conexion, "SELECT cedula, pin, nombres, apellidos FROM transportista WHERE cedula = '$CI' AND eliminado = '0'");
                
            if($resultado){

                $filaAsociativa = mysqli_fetch_array($resultado);

                
                $ciBD = $filaAsociativa["cedula"];
                $pinBD = $filaAsociativa["pin"];
                $nomBD = $filaAsociativa["nombres"];
                $apeBD = $filaAsociativa["apellidos"];
                $PIN_MD5 = md5($PIN);


                if($PIN_MD5 == $pinBD){

                    //ingreso de datos a session
                    session_start();
                    $_SESSION["cedula"] = $ciBD;
                    $_SESSION["nombre"] = $nomBD;
                    $_SESSION["apellido"] = $apeBD;
                    $_SESSION["tipo"] = "tr";
                    $_SESSION["tiempo"] = time();

                    cerrarConexion($conexion);
                    echo '<div class="msj ok">Iniciando sesion...</div>';
                    echo "<meta http-equiv='refresh' content='0.7;url=inicio.php?m=1'>";
                    die();
                } else {

                    $msjIngreso = "Cedula y PIN no coinciden, pruebe nuevamente.";
                    echo '<div class="msj error">'.$msjIngreso.'</div>';
                } 
            } else {

                $msjIngreso = "No se encontro el transportista en el sistema.";
                echo '<div class="msj error">'.$msjIngreso.'</div>';
            }

        } else if($tipo == "vs"){
            
            session_start();
            $_SESSION["tipo"] = "vs";
            cerrarConexion($conexion);
            echo '<div class="msj ok">Iniciando sesion...</div>';
            echo "<meta http-equiv='refresh' content='0.7;url=inicio.php'>";
            die();
        } else {

            $msjIngreso = "No es un tipo valido";
        }
        cerrarConexion($conexion);
    }


    // CERRAR SESION
    function cerrarSesion(){

        echo '<div class="msj ok">Cerrando sesion...</div>';
        session_unset();
        session_destroy();
        echo "<meta http-equiv='refresh' content='1;url=index.php'>";
        die();

    }

    // BUSCAR PAQUETE
    function buscarPaquete($codigo, $conexion, &$msjPaquete) {

        $consulta = mysqli_query($conexion, "SELECT estado, fechaEstimada, fechaEntrega FROM paquete WHERE codigo = '$codigo' AND eliminado = '0'");

        if($consulta){

            $filaAsociativa = mysqli_fetch_array($consulta);

            //Mira que el estado del paquete no este vacio, si esta se informa que no se encontro
            if(empty($filaAsociativa["estado"])){
                
                $msjPaquete = "No se encontro el paquete";

            //Buscamos la fecha estimada, si esta entonces mandamos la fecha estimada del paquete
            } else if (!empty($filaAsociativa["fechaEstimada"])){
                

                $resu = array (
                    "estado" => $filaAsociativa["estado"],
                    "fechaPaquete" => $filaAsociativa["fechaEstimada"],
                );
                cerrarConexion($conexion);
                return $resu;
            //Buscamos la fecha de entrega, si esta entonces mandamos la fecha estimada del paquete
            } else if(!empty($filaAsociativa["fechaEntrega"])){
            
                $resu = array (
                    "estado" => $filaAsociativa["estado"],
                    "fechaPaquete" => $filaAsociativa["fechaEntrega"],
                );
                cerrarConexion($conexion);
                return $resu;
            //Si no estan ninguna de las fechas entonces el paquete no esta asignado y solo le mandamos el estado
            } else {

                $resu = array (
                    "estado" => $filaAsociativa["estado"],
                );
                cerrarConexion($conexion);
                return $resu;
            }

            
        } else {

            $msjPaquete = "Ocurrio un error en la consulta.";
        }
        cerrarConexion($conexion);

    }

    // PAQUETES NO ASIGNADOS
    function paquetesNoAsignados($conexion, &$msjPaqueteNo) {


        $consulta = mysqli_query($conexion, "SELECT codigo, dirRemitente, dirEnvio, fragil, perecedero FROM paquete WHERE estado = 'No asignado' AND eliminado = '0'");

        if($consulta){
        
            $cant_filas = mysqli_num_rows($consulta);

            if($cant_filas == 0){

                $msjPaqueteNo = "No hay paquetes sin asignar";

            } else {

                $array = array();

                for($i = 0; $i < $cant_filas; $i++){

                    $filaAsociativa = mysqli_fetch_array($consulta);

                    $codigo = $filaAsociativa["codigo"];
                    $dirRemitente = $filaAsociativa["dirRemitente"];
                    $dirEnvio = $filaAsociativa["dirEnvio"];
                    
                    if($filaAsociativa["fragil"] == 1)
                        $fragil = true;
                    else
                        $fragil = false;

                    if($filaAsociativa["perecedero"] == 1)
                        $perecedero = true;
                    else
                        $perecedero = false;

                    $array[$i]["codigo"] = $codigo;
                    $array[$i]["dirRemitente"] = $dirRemitente;
                    $array[$i]["dirEnvio"] = $dirEnvio;
                    $array[$i]["fragil"] = $fragil;
                    $array[$i]["perecedero"] = $perecedero;
    
                }

                cerrarConexion($conexion);
                return $array;

            }
    
        } else {

            $msjPaqueteNo = "Error en la consulta de paquetes";
        }
        cerrarConexion($conexion);

    }

    // Ver metodo desde aca
    function asignarPaquete($conexion, $ciTransportista){

        $consulta = mysqli_query($conexion, "SELECT codigo, fechaAsignacion, ciTransportista FROM paquete WHERE estado = 'Asignado' AND eliminado = '0'");

        if($consulta){

            $cant_filas = mysqli_num_rows($consulta);

            if($cant_filas > 0){

                $noAsignado = true;
                for($i = 0; $i < $cant_filas; $i++){
                    $filaAsociativa = mysqli_fetch_array($consulta);

                    $ciAsignada = $filaAsociativa["ciTransportista"];

                    if($ciAsignada == $ciTransportista){
                        
                        $codigo = $filaAsociativa["codigo"];
                        $fechaBD = $filaAsociativa["fechaAsignacion"];
                        $timestamp = strtotime($fechaBD);
                        $fechaAsignacion = date("d/m/Y", $timestamp);

                        $msjAsignado = "Ya tiene un paquete asignado, el codigo del paquete es: $codigo, y le fue asignado el dia: $fechaAsignacion.";
                        echo '<div class="msj error">'.$msjAsignado.'</div>';
                        $noAsignado = false;      

                    } 
                }

                if($noAsignado){

                    echo "<br> <form name=asignar method=POST action=''>";
                    echo "Ingrese una fecha estimada de entrega para el paquete: ";
                    echo "<input type=date name=fechaEstimada required>";
                    echo "<input type=submit name=asignar id=asignar value=ASIGNAR>";

                    echo "</form>";
                }
            } else {

                echo "<br> <form name=asignar method=POST action=''>";
                echo "Ingrese una fecha estimada de entrega para el paquete: ";
                echo "<input type=date name=fechaEstimada required>";
                echo "<input type=submit name=asignar id=asignar value=ASIGNAR>";

                echo "</form>";
            }

        } else {

            $msjConsulta = "<br> Ocurrio un error en la consulta";
            echo '<div class="msj error">'.$msjConsulta.'</div>';
        }
        cerrarConexion($conexion);

    }
        
    function asignacionDePaqueteABD($conexion, $ciTransportista, $codigoPaquete, $fechaEstimada){

        $fechaAsignado = date('Y-m-d');

        $consulta = mysqli_query($conexion, "UPDATE paquete SET fechaEstimada = '$fechaEstimada', estado = 'Asignado', fechaAsignacion = '$fechaAsignado', ciTransportista = '$ciTransportista' WHERE codigo = '$codigoPaquete' AND eliminado = '0'");

        if($consulta){

            cerrarConexion($conexion);
            echo "<div class='msj ok'>Se actualizo el paquete, regresando al inicio...</div>";
            echo "<meta http-equiv='refresh' content='1;url=inicio.php?m=1'>";
            die();
        } else {
            $msjConsulta = "<br> Ocurrio un error en la consulta";
            echo '<div class="msj error">'.$msjConsulta.'</div>';
        }
        cerrarConexion($conexion);

    }

    //Muestra la informacion de los paquetes, se usa para transportista y encargado
    function paquetesAsignados($conexion, $ciTransportista = 'n'){

        if($ciTransportista == 'n'){
            
            $query = "SELECT codigo, dirRemitente, dirEnvio, fragil, perecedero, fechaEstimada, estado, fechaAsignacion, ciTransportista FROM paquete WHERE estado = 'Asignado' AND eliminado = '0'";
        } else {

            $query = "SELECT codigo, dirRemitente, dirEnvio, fragil, perecedero, fechaEstimada, estado, fechaAsignacion FROM paquete WHERE estado = 'Asignado' AND ciTransportista = '$ciTransportista' AND eliminado = '0'";
        }

        $consulta = mysqli_query($conexion, $query);

        if($consulta){
        
            $cant_filas = mysqli_num_rows($consulta);

            if($cant_filas == 0){

                if($ciTransportista == 'n')
                    echo "<div class='msj alerta'>No hay paquetes asignados</div>";
                else
                    echo "<div class='msj alerta'>No tiene paquetes asignados</div>";
                
            } else {

                    $array = array();

                    for($i = 0; $i < $cant_filas; $i++){

                        $filaAsociativa = mysqli_fetch_array($consulta);

                        $codigo = $filaAsociativa["codigo"];
                        $dirRemitente = $filaAsociativa["dirRemitente"];
                        $dirEnvio = $filaAsociativa["dirEnvio"];
                        
                        if($filaAsociativa["fragil"] == 1)
                            $fragil = true;
                        else
                            $fragil = false;

                        if($filaAsociativa["perecedero"] == 1)
                            $perecedero = true;
                        else
                            $perecedero = false;

                        $fechaEstimada = $filaAsociativa["fechaEstimada"];
                        $estado = $filaAsociativa["estado"];
                        $fechaAsignacion = $filaAsociativa["fechaAsignacion"];

                        $array[$i]["codigo"] = $codigo;
                        $array[$i]["dirRemitente"] = $dirRemitente;
                        $array[$i]["dirEnvio"] = $dirEnvio;
                        $array[$i]["fragil"] = $fragil;
                        $array[$i]["perecedero"] = $perecedero;
                        $array[$i]["fechaEstimada"] = $fechaEstimada;
                        $array[$i]["estado"] = $estado;
                        $array[$i]["fechaAsignacion"] = $fechaAsignacion;


                        if($ciTransportista == 'n'){

                            $ciTr = $filaAsociativa["ciTransportista"];
                            $consultaTr = mysqli_query($conexion, "SELECT nombres, apellidos FROM transportista WHERE cedula = $ciTr AND eliminado = '0'");

                            if($consultaTr){

                                $filaAsociativaTr = mysqli_fetch_array($consultaTr);
                                $nombre = $filaAsociativaTr["nombres"];
                                $apellido = $filaAsociativaTr["apellidos"];

                                $nombreCompleto = $nombre . " " . $apellido;

                                $array[$i]["ciTransportista"] = $ciTr;
                                $array[$i]["nombreCompleto"] = $nombreCompleto;

                            }

                        }
                    }
                    cerrarConexion($conexion);
                    return $array;
                }
        } else {

            echo "<div class='msj error'>Error en la consulta de paquetes</div>";
        }
        cerrarConexion($conexion);

    }

    function entregarPaquete($conexion, $codigoPaquete, $ciTransportista){

        $consulta = mysqli_query($conexion, "SELECT ciTransportista FROM paquete WHERE codigo = '$codigoPaquete' AND eliminado = '0'");

        if($consulta){

            $filaAsociativa = mysqli_fetch_array($consulta);
            $ciBD = $filaAsociativa["ciTransportista"];

            if($ciTransportista == $ciBD){

                echo "<br> <form name=entregar method=POST action=''>";
                echo "Ingrese la fecha de entrega del paquete: ";
                echo "<input type=date name=fechaEntrega required>";
                echo "<input type=submit name=entregar id=entregar value=ENTREGAR>";

                echo "</form>";

            } else {
                echo "El transportista no coincide con el asignado.";
            }

        } else 
            echo "Ocurrio un error en la consulta";

        cerrarConexion($conexion);

    }

    function entregaPaqueteABD($conexion, $ciTransportista, $fechaEntrega, $codigoPaquete){

        $consulta = mysqli_query($conexion, "UPDATE paquete SET fechaEntrega = '$fechaEntrega', estado = 'Entregado' WHERE codigo = '$codigoPaquete' AND eliminado = '0'");

        if(!$consulta){

            echo "Ocurrio un error en la consulta";
        } else {

            cerrarConexion($conexion);
            echo "<div class='msj ok'>Se actualizo el paquete, regresando al inicio...</div>";
            echo "<meta http-equiv='refresh' content='1;url=inicio.php?m=1'>";
            die();
        }
        cerrarConexion($conexion);
    }

    //Envia un array con el historial de los paquetes, para tenerlo en la pantalla de encargado no hay que pasarle la cedula, y para tener el historial de un transportista le pasamos la cedula
    function historialPaquetes($conexion, $ciTransportista = 'n'){

        $resu = array();
        //Primero busco los paquetes asignados
        if($ciTransportista == 'n')      
            $consulta1 = mysqli_query($conexion, "SELECT codigo, fechaEstimada, estado, ciTransportista FROM paquete WHERE estado = 'Asignado' AND eliminado = '0'");
        else 
            $consulta1 = mysqli_query($conexion, "SELECT codigo, fechaEstimada, estado FROM paquete WHERE estado = 'Asignado' AND ciTransportista = '$ciTransportista' AND eliminado = '0'");

        if($consulta1){
        
            $cant_filas = mysqli_num_rows($consulta1);

            if($cant_filas > 0){

                $array1 = array();

                for($i = 0; $i < $cant_filas; $i++){

                    $filaAsociativa = mysqli_fetch_array($consulta1);

                    $codigo = $filaAsociativa["codigo"];
                    $fechaEstimada = $filaAsociativa["fechaEstimada"];
                    $estado = $filaAsociativa["estado"];
                    
                    if($ciTransportista == 'n'){

                        $ciTr = $filaAsociativa["ciTransportista"];
                        $array1[$i]["ciTransportista"] = $ciTr;

                        $consultaTr = mysqli_query($conexion, "SELECT nombres, apellidos FROM transportista WHERE cedula = '$ciTr' AND eliminado = '0'");

                        if($consultaTr){

                            $filaAsociativaTr = mysqli_fetch_array($consultaTr);
                            $nombre = $filaAsociativaTr["nombres"];
                            $apellido = $filaAsociativaTr["apellidos"];

                            $nombreCompleto = $nombre . " " . $apellido;

                            $array1[$i]["nombreCompleto"] = $nombreCompleto;

                        }
                    }


                    $array1[$i]["codigo"] = $codigo;
                    $array1[$i]["fechaEstimada"] = $fechaEstimada;
                    $array1[$i]["estado"] = $estado;

    
                }

                $resu[0] = $array1;

            }
    
        } else {

            echo "Error en la consulta de paquetes";
        }

        //Despues hago lo mismo pero con los paquetes asignados previamente al Transportista y el resto para el Encargado
        if($ciTransportista == 'n')      
            $consulta2 = mysqli_query($conexion, "SELECT codigo, fechaEntrega, estado, ciTransportista FROM paquete WHERE estado = 'Entregado' AND eliminado = '0'");
        else 
            $consulta2 = mysqli_query($conexion, "SELECT codigo, fechaEntrega, estado FROM paquete WHERE estado = 'Entregado' AND ciTransportista = '$ciTransportista' AND eliminado = '0'");

        if($consulta2){
        
            $cant_filas = mysqli_num_rows($consulta2);

            if($cant_filas > 0){

                $array2 = array();

                for($i = 0; $i < $cant_filas; $i++){

                    $filaAsociativa = mysqli_fetch_array($consulta2);

                    $codigo = $filaAsociativa["codigo"];
                    $estado = $filaAsociativa["estado"];
                    $fechaEntrega = $filaAsociativa["fechaEntrega"];
                    
                    $array2[$i]["codigo"] = $codigo;
                    $array2[$i]["estado"] = $estado;
                    $array2[$i]["fechaEntrega"] = $fechaEntrega;


                    if($ciTransportista == 'n'){

                        $ciTr = $filaAsociativa["ciTransportista"];
                        $array2[$i]["ciTransportista"] = $ciTr;

                        $consultaTr = mysqli_query($conexion, "SELECT nombres, apellidos FROM transportista WHERE cedula = '$ciTr' AND eliminado = '0'");

                        if($consultaTr){

                            $filaAsociativaTr = mysqli_fetch_array($consultaTr);
                            $nombre = $filaAsociativaTr["nombres"];
                            $apellido = $filaAsociativaTr["apellidos"];

                            $nombreCompleto = $nombre . " " . $apellido;

                            $array2[$i]["nombreCompleto"] = $nombreCompleto;

                        }
                    }
                }

                $resu[1] = $array2;

            }
    
        } else {

            echo "Error en la consulta de paquetes";
        }

        if($ciTransportista == 'n'){
            $consulta3 = mysqli_query($conexion, "SELECT codigo, estado FROM paquete WHERE estado = 'No asignado' AND eliminado = '0'");

            if($consulta3){
        
                $cant_filas = mysqli_num_rows($consulta3);
    
                if($cant_filas > 0){
    
                    $array3 = array();
    
                    for($i = 0; $i < $cant_filas; $i++){
    
                        $filaAsociativa = mysqli_fetch_array($consulta3);
    
                        $codigo = $filaAsociativa["codigo"];
                        $estado = $filaAsociativa["estado"];
                        
                        $array3[$i]["codigo"] = $codigo;
                        $array3[$i]["estado"] = $estado;
            
                    }

                    $resu[2] = $array3;

                }
            }
        }
        cerrarConexion($conexion);
        return $resu;

    }

    function listaPaquetes($conexion){
        
        $consulta = mysqli_query($conexion, "SELECT codigo, dirRemitente, dirEnvio, fragil, perecedero, fechaEstimada, fechaEntrega, estado, fechaAsignacion, ciTransportista FROM paquete WHERE eliminado = '0'"); // agregar WHERE eliminado = 0 cuando actualice la bd

        if($consulta){

            $cant_filas = mysqli_num_rows($consulta);

            if($cant_filas > 0){

                $array = array();

                for($i = 0; $i < $cant_filas; $i++){
                    
                    $filaAsociativa = mysqli_fetch_array($consulta);

                    $codigo = $filaAsociativa["codigo"];
                    $dirRemitente = $filaAsociativa["dirRemitente"];
                    $dirEnvio = $filaAsociativa["dirEnvio"];
                    $fragil = $filaAsociativa["fragil"];
                    $perecedero = $filaAsociativa["perecedero"];
            
                    $estado = $filaAsociativa["estado"];
                                  
                    $array[$i]["codigo"] = $codigo;
                    $array[$i]["dirRemitente"] = $dirRemitente;
                    $array[$i]["dirEnvio"] = $dirEnvio;
                    $array[$i]["fragil"] = $fragil;
                    $array[$i]["perecedero"] = $perecedero;
                    $array[$i]["estado"] = $estado;
                    
                    //Comento por mientras, cuando lo pruebe veo si lo borro o no
                    //if($estado != 'No asignado'){

                        $fechaAsignacion = $filaAsociativa["fechaAsignacion"];
                        $ciTransportista = $filaAsociativa["ciTransportista"];

                        $array[$i]["fechaAsignacion"] = $fechaAsignacion;
                        $array[$i]["ciTransportista"] = $ciTransportista;

                        //if($estado == 'Asignado'){

                            $fechaEstimada = $filaAsociativa["fechaEstimada"];
                            $array[$i]["fechaEstimada"] = $fechaEstimada;
                        //} else {

                            $fechaEntrega = $filaAsociativa["fechaEntrega"];
                            $array[$i]["fechaEntrega"] = $fechaEntrega;
                        //}
                    //}
                }
                cerrarConexion($conexion);
                return $array;

            } else {
                
                echo "<div class='msj alerta'>No hay paquetes en el sistema</div>";
            }

        } else {

            echo "Error en la consulta de transportista";
        }
        cerrarConexion($conexion);

    }

    function existePaquete($conexion, $codigo){

        $consulta = mysqli_query($conexion, "SELECT codigo FROM paquete WHERE codigo = '$codigo'");

        $cant_filas = mysqli_num_rows($consulta);

        if($cant_filas > 0)
            return true;
        else
            return false;
    }

    function agregarPaquete($conexion, $codigo, $dirRemitente, $dirEnvio, $fragil, $perecedero){

        $consulta = mysqli_query($conexion, "INSERT INTO paquete(codigo, dirRemitente, dirEnvio, fragil, perecedero) VALUES ('$codigo', '$dirRemitente', '$dirEnvio', '$fragil', '$perecedero')");

        if($consulta){
        
            cerrarConexion($conexion);
            echo "<div class='msj ok'>El paquete se agrego exitosamente</div>";
            echo "<meta http-equiv='refresh' content='1;url=inicio.php?m=2'>";
            die();

        } else {

            echo "Ocurrio un error en la consulta";
        }
      
        cerrarConexion($conexion);
    }

    function modificarPaquete($conexion, $codigoPaquete, $codigoNuevo, $dirRemitente, $dirEnvio, $fragil, $perecedero){

        $consulta = mysqli_query($conexion, "UPDATE paquete SET codigo = '$codigoNuevo', dirRemitente = '$dirRemitente', dirEnvio = '$dirEnvio', fragil = '$fragil', perecedero = '$perecedero' WHERE codigo = '$codigoPaquete' AND eliminado = '0'");

        if($consulta){

            cerrarConexion($conexion);
            echo "<div class='msj ok'>El paquete se actualizo exitosamente</div>";
            echo "<meta http-equiv='refresh' content='1;url=inicio.php?m=2'>";
            die();
        } else {
            echo "<div class='msj error'>Ocurrio un error en la consulta</div>";
        }
        cerrarConexion($conexion);
    }

    function eliminarPaquete($conexion, $codigoPaquete){

        $consulta = mysqli_query($conexion, "UPDATE paquete SET eliminado = '1' WHERE codigo = '$codigoPaquete' AND eliminado = '0'");

        if($consulta){

            cerrarConexion($conexion);
            echo "<div class='msj alerta'>El paquete se elimino exitosamente</div>";
            echo "<meta http-equiv='refresh' content='1;url=inicio.php?m=2'>";
            die();
        } else {
            echo "<div class='msj error'>Ocurrio un error en la consulta</div>";
        }
        cerrarConexion($conexion);
    }

    function listaTransportistas($conexion){

        $consulta = mysqli_query($conexion, "SELECT cedula, nombres, apellidos, direccion, telefono, foto FROM transportista WHERE eliminado = '0'");

        if($consulta){

            $cant_filas = mysqli_num_rows($consulta);

            if($cant_filas > 0){

                $array = array();

                for($i = 0; $i < $cant_filas; $i++){
                    
                    $filaAsociativa = mysqli_fetch_array($consulta);

                    $ciTransportista = $filaAsociativa["cedula"];
                    $nombres = $filaAsociativa["nombres"];
                    $apellidos = $filaAsociativa["apellidos"];
                    $direccion = $filaAsociativa["direccion"];
                    $telefono = $filaAsociativa["telefono"];
                    $foto = $filaAsociativa["foto"];

                    $array[$i]["cedula"] = $ciTransportista;
                    $array[$i]["nombres"] = $nombres;
                    $array[$i]["apellidos"] = $apellidos;
                    $array[$i]["direccion"] = $direccion;
                    $array[$i]["telefono"] = $telefono;
                    $array[$i]["foto"] = $foto;

                }
                cerrarConexion($conexion);
                return $array;

            } else {
                
                echo "<div class='msj alerta'>No hay transportistas en el sistema </div>";
            }

        } else {

            echo "<div class='msj error'>Error en la consulta de transportista</div>";
        }
        cerrarConexion($conexion);

    }

    function existeTransportista($conexion, $cedula){

        $consulta = mysqli_query($conexion, "SELECT cedula FROM transportista WHERE cedula = '$cedula'");

        $cant_filas = mysqli_num_rows($consulta);

        if($cant_filas > 0)
            return true;
        else
            return false;
    }

    function eliminarTransportista($conexion, $cedulaTransportista){

        $consulta = mysqli_query($conexion, "UPDATE transportista SET eliminado = '1' WHERE cedula = '$cedulaTransportista' AND eliminado = '0'");

        if($consulta){

            cerrarConexion($conexion);
            echo "<div class='msj alerta'>El transportista se elimino exitosamente</div>";
            echo "<meta http-equiv='refresh' content='1;url=inicio.php?m=3'>";
            die();
        } else {
            echo "<div class='msj error'>Ocurrio un error en la consulta</div>";
        }
        cerrarConexion($conexion);
    }

    function tienePaqueteAsignado($conexion, $ciTransportista){

        $consulta = mysqli_query($conexion, "SELECT codigo FROM paquete WHERE estado = 'Asignado' AND eliminado = '0' AND ciTransportista = '$ciTransportista'");

        if($consulta){

            $cant_filas = mysqli_num_rows($consulta);

            if($cant_filas > 0){
                cerrarConexion($conexion);
                return true;                
            } else {
                cerrarConexion($conexion);
                return false;
            }
        }
        cerrarConexion($conexion);
    }



?>