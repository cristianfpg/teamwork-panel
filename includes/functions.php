<?php 
  setlocale(LC_TIME, 'es_ES');
  date_default_timezone_set("America/Bogota");
  // include_once "google-calendar.php";

  function apiCall($action){
    $channel = curl_init($action);
    $key = "";

    curl_setopt( $channel, CURLOPT_URL, "https://coloralcuadrado.teamwork.com/". $action );
    curl_setopt( $channel, CURLOPT_RETURNTRANSFER, 1 ); 
    curl_setopt( $channel, CURLOPT_HTTPHEADER, 
      array( "Authorization: BASIC ". base64_encode( $key .":xxx" ))
    );

    $response = curl_exec($channel);
  
    curl_close ( $channel );
    return $response;
  }

  function getStartOfWeekDate($date = null){
    if ($date instanceof \DateTime) {
      $date = clone $date;
    } else if (!$date) {
      $date = new \DateTime();
    } else {
      $date = new \DateTime($date);
    }
    
    $date->setTime(0, 0, 0);
    
    if ($date->format('N') == 1) {
        // If the date is already a Monday, return it as-is
        return $date;
    } else {
        // Otherwise, return the date of the nearest Monday in the past
        // This includes Sunday in the previous week instead of it being the start of a new week
        return $date->modify('last monday');
    }
  }

  function handleWriteFile($msg,$filename, $modo){
    $file = fopen($filename, $modo);
    fwrite($file, $msg."\n");
    fclose($file);
  }

  function connectToDb($crud,$table,$keys,$values,$findKey,$findValue){ 
    try {
      $pdo = new PDO("mysql:host=localhost;dbname=dbname", "root", "root");
      $pdo->exec("SET NAMES 'utf8';");
      $pdoReturn = null;
      switch($crud){
        case "all":
          $sql = $pdo->query("SELECT * FROM $table");
          $pdoReturn = $sql->fetchall();
          break;
        case "read":
          $sql = $pdo->query("SELECT * FROM $table WHERE $keys = '$values'");
          $pdoReturn = $sql->fetchall();
          break;
        case "create":
          $sql = "INSERT INTO $table ($keys) VALUES ('$values')";
          $pdo->prepare($sql)->execute();
          break;
        case "update":
          $sql = "UPDATE $table SET $keys='$values' WHERE $findKey='$findValue'"; 
          $pdo->prepare($sql)->execute();
          break;
        case "delete":
          $sql = "DELETE FROM $table WHERE $table.$keys = '$values'";
          $pdo->prepare($sql)->execute();
          break;
        case "multiple_clean":
          $query = "";
          foreach($keys as $k => $key){
            if($k != 0) $query .= ",";
            $query .= "$key=''";
          }
          $sql = "UPDATE $table SET $query WHERE $findKey='$findValue'"; 
          $pdo->prepare($sql)->execute();
          break;
        case "get_columns":
          $q = $pdo->prepare("DESCRIBE $table");
          $q->execute();
          $pdoReturn = $q->fetchAll(PDO::FETCH_COLUMN);
          break;
      }
      $pdo = null;
      return $pdoReturn;
    } catch (PDOException $e) {
        print "¡Error!: " . $e->getMessage() . "<br/>";
        die();
    }
  }


  function detectTeam($responsable){
    $query = connectToDb("read","equipos","id",$responsable,null,null)[0];
    if(count($query) >= 1){
      return array($query["nombre"],$responsable,null,$query["color"]);
    }else{
      return false;
    }
  }

  function detectEjecutiva($ejecutiva){
    $query = connectToDb("read","proyectos","ejecutiva",$ejecutiva,null,null);
    $proyectos = array();

    if(count($query) >= 1){
      $queryEjecutiva = connectToDb("read","ejecutivas","id",$ejecutiva,null,null);
      foreach($query as $account){
        array_push($proyectos, $account["id"]);
      }
      return array($queryEjecutiva[0]["nombre"], $proyectos);
    }else{
      return false;
    }
  }
  
  $getEventsGoogleCalendar = function($calendarId) use ($service){
    $optParams = array(
      'orderBy' => 'startTime',
      'singleEvents' => true,
      'timeMin' => date('c'),
      'timeMax' => getStartOfWeekDate()->modify('+8 day')->format("c")
    );

    $results = $service->events->listEvents($calendarId, $optParams);
    $events = $results->getItems();

    if (empty($events)) {
      return false;
    } else {
      return $events;
    }
  };
  
  function slugify($string){
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
  }

  function armarUrl($parametroDeCambio, $valorParametro, $parametrosGet){
    $urlLink = "./?";
    foreach($parametrosGet as $key => $valor){
      if($parametroDeCambio == $key){
        if($valor == ""){
          if($valorParametro){
            $urlLink .= $key."=".$valorParametro."&";
          }else{
            $urlLink .= $key."=".$valor."1&";
          }
        }else{
          if($valorParametro && $parametrosGet->{$parametroDeCambio} != $valorParametro){
            $urlLink .= $key."=".$valorParametro."&";
          }else{
            continue;
          }
        }
      }else{
        if($valor == "") continue;
        $urlLink .= $key."=".$valor."&";
      }
    }
    if($urlLink == "./?"){
      $urlLink = "./";
    }
    return $urlLink;
  };

  function getAdminTemplate($plantilla, $data){
    $template = "";
    foreach($data as $key => $row){
      // MODAL PARA BORRAR EL ELEMENTO
      $template .= 
      '<div class="modal" id="delete-row-'. $key .'">
        <div class="content">
          <form action="includes/crud.php" method="POST">
            <input type="submit" value="Confirmar">
            <input type="hidden" name="crud" value="delete">
            <input type="hidden" name="tabla" value="'.$plantilla.'">
            <input type="hidden" name="id" value="'.$row["id"].'">
            <a href="#"><span>Cancelar</span></a>
          </form>
        </div>
      </div>';

      // GRUPO PARA EDITAR EL ELEMENTO
      $template .= 
      '<form action="includes/crud.php" method="POST" class="row" id="row-'. $key .'">
        <span>Nombre ejecutiva: <input type="text" name="nombre" value="'. $row["nombre"] .'"></span>
        <span>Color: <input type="text" name="ejecutiva" value="'. $row["color"] .'"></span>
        <input type="hidden" name="id" value="'. $row["id"] .'">
        <input type="hidden" name="crud" value="update">
        <input type="hidden" name="tabla" value="'.$plantilla.'">
        <a href="#row-'. $key .'"><span>Editar</span></a>
        <a href="#delete-row-'. $key .'"><span>Borrar</span></a>
        <input type="submit" value="Confirmar" class="update">
        <a href="#" class="update"><span>Cancelar</span></a>
      </form>';

      // GRUPO PARA CREAR EL ELEMENTO
      $template .=
      '<form action="includes/crud.php" method="POST" class="row crear">
        <span>Nombre ejecutiva: <input type="text" name="nombre"></span>
        <span>Color: <input type="text" name="color"></span>
        <span>ID Ejecutiva: <input type="number" name="id"></span>
        <input type="hidden" name="crud" value="create">
        <input type="hidden" name="tabla" value="'.$plantilla.'">
        <input type="submit" value="Crear" class="crear">
      </form>';
    }


    return $template;
  }
?>