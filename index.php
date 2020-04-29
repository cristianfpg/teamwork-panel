<?php 
  require_once "includes/fetchTWData.php";
  require_once "data/currentWeek.php";
  require_once "data/nextWeek.php";

  $parametrosGet = (object) array(
    "siguientesemana" => $_GET["siguientesemana"],
    "lunesfestivo" => $_GET["lunesfestivo"],
    "equipos" => $_GET["equipos"],
    "ejecutivas" => $_GET["ejecutivas"]
  );
  
  $equipos = $_GET["equipos"] ? explode(",",$_GET["equipos"]) : null;
  $ejecutivas = $_GET["ejecutivas"] ? explode(",",$_GET["ejecutivas"]) : null;
  $today = date("Ymd");
  $titulo = "Tráfico";
  $botonCambioSemana = "Próxima semana";
  $colorBoton = "#00adff";
  $colorFondoBoton = "#828284";

  $limiteHitoDosSemanas = $nextWeek[4]["date"];
  $limiteHitoUltimaSemana = $currentWeek[4]["date"];


  $selectedTeam = detectTeam($equipos[0]);

  if($selectedTeam) $titulo .= " ".$selectedTeam[0];

  if($parametrosGet->{"siguientesemana"} == ""){
    $exportData = exportData($currentWeek,$equipos,$getEventsGoogleCalendar,$ejecutivas);
  }else{
    $exportData = exportData($nextWeek,$equipos,$getEventsGoogleCalendar,$ejecutivas);
    $titulo = "Siguiente tráfico";
    $botonCambioSemana = "Semana actual";
    $colorBoton = "#828284";
    $colorFondoBoton = "#00adff";
  }

  $projects = $exportData->{"projects"};
  $week = $exportData->{"week"};
  $tasks = $exportData->{"tasks"};
  $milestones = $exportData->{"milestones"};

  $dataEquipos = connectToDb("all","equipos",null,null,null,null);
  $dataEjecutivas = connectToDb("all","ejecutivas",null,null,null,null);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Tráfico semanal</title>
  <link rel="stylesheet" href="assets/css/color.css">
</head>
<body class="trafico">
  <?php foreach($projects as $key => $project): ?>
    <?php foreach($project->{"milestones"} as $key => $milestone): ?>
      <?php if($key != "0"): ?>
        <div id="modal-<?php echo $key;?>" class="modal">
          <div class="window">
            <span class="titulo-window"><?php echo $milestone->{"title"}; ?></span>
            <?php foreach($milestone->{"task-lists"} as $taskList): ?>
              <span class="tasklist-title"><?php echo $taskList->{"title"}; ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endforeach; ?>
  <span id="btn-toggle" class="btn-menu">Menú</span>
  <div id="desplegable" class="desplegable">
    <div class="convenciones">
      <span class="label">Equipos</span>
      <?php foreach($dataEquipos as $equipo): ?>
        <a class="equipo-<?php echo $equipo["id"];?>" href="<?php echo armarUrl("equipos",$equipo["id"],$parametrosGet); ?>"><div style="background-color: <?php echo $equipo["color"];?>" class="marca"></div><span><?php echo $equipo["nombre"];?></span></a>
      <?php endforeach; ?>

      <?php if($equipos[0] != ""): ?>
        <a href="<?php echo armarUrl("equipos",null,$parametrosGet); ?>"><div class="marca"></div><span>Ver todos</span></a>
      <?php endif; ?>
    </div>
    <div class="convenciones">
      <span class="label">Ejecutivas</span>
      <?php foreach($dataEjecutivas as $ejecutiva): ?>
        <a class="ejecutiva-<?php echo $ejecutiva["id"];?>" href="<?php echo armarUrl("ejecutivas",$ejecutiva["id"],$parametrosGet); ?>"><div style="background-color: <?php echo $ejecutiva["color"];?>" class="marca"></div><span><?php echo $ejecutiva["nombre"];?></span></a>
      <?php endforeach; ?>

      <?php if($ejecutivas[0] != ""): ?>
        <a href="<?php echo armarUrl("ejecutivas",null,$parametrosGet); ?>"><div class="marca"></div><span>Ver todos</span></a>
      <?php endif; ?>
    </div>
    <a href="<?php echo armarUrl("siguientesemana",null,$parametrosGet); ?>" class="btn cambiar-semana" style="background-color: <?php echo $colorFondoBoton; ?>"><?php echo $botonCambioSemana; ?></a>
    <h1 class="titulo" style="color: <?php echo $colorBoton; ?>"><?php echo $titulo; ?></h1>
  </div>
  <div id="trafico">
    <div class="row row-0 week-labels">
      <div class="column"><span>Proyectos</span></div>
      <div class="column"><span>Listas de tareas</span></div>
      <div class="column"><span>Tareas</span></div>
      <?php if(count($week) >= 1): ?>
        <?php foreach($week as $dayKey => $day): ?>
          <?php 
            $todayClass = "";
            $hrefFestivo = "";
            if($today == $day["date"]) $todayClass = " today"; 
            if($parametrosGet->{"lunesfestivo"} != "" && $dayKey == 5) continue; 
            if($parametrosGet->{"lunesfestivo"} == "" && $dayKey == 6) continue; 

            if($parametrosGet->{"lunesfestivo"} != "" && $dayKey == 6) $hrefFestivo = "href='".armarUrl("lunesfestivo",null,$parametrosGet)."'";
            if($parametrosGet->{"lunesfestivo"} == "" && $dayKey == 5) $hrefFestivo = "href='".armarUrl("lunesfestivo",null,$parametrosGet)."'";
          ?>
          <a <?php echo $hrefFestivo; ?> class="column<?php echo $todayClass; ?> day-label">
            <span><?php echo $day["label"]; ?></span><br>
            <span><?php echo $day["day"]; ?></span>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php if(is_object($projects) && count((array)$projects) != 0): ?>
      <?php foreach($projects as $key => $project): ?>
        <div class="row row-a">
          <div class="column title" data-proyecto="<?php echo $key; ?>">
            <span><?php echo $project->{"project-name"}; ?></span>
          </div>
          <div class="column content">
            <?php foreach($project->{"milestones"} as $key => $milestone): ?>
              <?php 
                $entregaEstaSemana = "";
                if($milestone->{"deadline-date"} <= $limiteHitoDosSemanas && $milestone->{"deadline-date"} > $limiteHitoUltimaSemana){
                  $entregaEstaSemana = "dos-semanas";
                }else if($milestone->{"deadline-date"} <= $limiteHitoUltimaSemana){
                  $entregaEstaSemana = "ultima-semana";
                }
              ?>
              <?php if($key != "0"): ?>
                <div class="row row-b-2 <?php echo $entregaEstaSemana;?>">
                  <div>
                    <span class="titulo-hito"><?php echo $milestone->{"title"}; ?></span>
                    <span>- <?php echo $milestone->{"deadline-label"}; ?></span>
                  </div>
                </div>
              <?php else: ?>
                <div class="row row-b-2 vacio">
                </div>
              <?php endif; ?>
              <?php foreach($milestone->{"task-lists"} as $taskList): ?>
                <div class="row row-b">
                  <div class="column title">
                    <span><?php echo $taskList->{"title"}; ?></span>
                  </div>
                  <div class="column content">
                    <?php foreach($taskList->{"parent-tasks"} as $parentTask): ?>
                      <div class="row row-c">
                        <div class="column title">
                          <span><?php echo $parentTask->{"parent-task-name"}; ?></span>
                        </div>
                        <?php foreach($week as $dayKey => $day): ?>
                          <?php 
                            if($parametrosGet->{"lunesfestivo"} != "" && $dayKey == 5) continue; 
                            if($parametrosGet->{"lunesfestivo"} == "" && $dayKey == 6) continue;
                          ?>
                          <div class="column day">
                            <?php foreach($parentTask->{"teams"} as $teamId => $team): ?>
                              <?php foreach($team->{"tasks"} as $keyId => $task): ?>
                                <?php 
                                  // ITERA POR CADA UNO DE LOS DIAS DE LAS SEMANA Y DE LAS TAREAS.
                                  // VALIDA SI:
                                  // EL DIA DE ENTREGA DE LA TAREA ES IGUAL QUE EL DIA QUE EVALUA -> PUEDE DETECTAR TAREAS VENCIDAS
                                  // (O)
                                  // EL DIA DE INICIO DE LA TAREA ES MENOR O IGUAL AL DIA QUE EVALUA (Y) QUE EL DIA DE ENTREGA DE LA TAREA ES MAYOR O IGUAL QUE EL DIA QUE EVALUA (Y) QUE EL DIA EN EL QUE SE VE EL TRAFICO ES MENOR 0 IGUAL A LA FECHA QUE SE EVALUA
                                  if(
                                    $task->{"due-date"} == $day["date"] ||
                                    (
                                      $task->{"start-date"} <= $day["date"] && // limite si ya paso el dia cuando se ve el trafico
                                      $task->{"due-date"} >= $day["date"] && // limite a la fecha de entrega
                                      $today <= $day["date"]
                                      // $task->{"start-date"} <= $day["date"]
                                      // $today <= $day["date"] // quita el dia anterior si no es la fecha de entrega
                                    ) 
                                  ): ?>
                                  <?php 
                                    $estado = "";
                                    $link = 'href="https://coloralcuadrado.teamwork.com/#/tasks/'.$keyId.'"';
                                    if($task->{"due-date"} == $day["date"] ) $estado = "dia-entrega"; 
                                    if($task->{"due-date"} < $today ) $estado = "atrasada"; 
                                    if($task->{"event"}) $link = ""; 
                                  ?>
                                  <a <?php echo $link; ?> target="_blank" class="row task <?php echo $estado; ?>">
                                    <span><?php echo $task->{"content"}; ?></span>
                                    <div class="bg" style="background-color: <?php echo $team->{"color"}; ?>"></div>
                                  </a>
                                <?php else: ?>
                                  <div class="row task vacio"></div>
                                <?php endif; ?>
                              <?php endforeach; ?>
                            <?php endforeach; ?>
                          </div>
                        <?php endforeach; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="sin-resultados">
        <img class="icono" src="assets/img/icono-telescopio.png" alt="">
        <p class="copy">No hay resultados.<br><a href="./" style="text-decoration: underline;">Volver al home</a></p>
      </div>
    <?php endif; ?>
  </div>
  <script>
    var btnToggle = document.getElementById("btn-toggle");
    var desplegable = document.getElementById("desplegable");

    btnToggle.addEventListener("click", function(){
      desplegable.classList.toggle('active');
      if(btnToggle.innerText == "MENÚ"){
        btnToggle.innerText = "CERRAR";
      }else{
        btnToggle.innerText = "MENÚ";
      }
    });
  </script>
  <?php 
    if($equipos[0]){
      $jsEquipos = "<script>";
      $jsEquipos .= "var equipoActivo = document.querySelector('.equipo-$equipos[0]');";
      $jsEquipos .= "equipoActivo.classList.add('active');";
      $jsEquipos .= "</script>";
      echo $jsEquipos;
    }
    if($ejecutivas[0]){
      $jsEquipos = "<script>";
      $jsEquipos .= "var ejecutivaActiva = document.querySelector('.ejecutiva-$ejecutivas[0]');";
      $jsEquipos .= "ejecutivaActiva.classList.add('active');";
      $jsEquipos .= "</script>";
      echo $jsEquipos;
    }
  ?>
</body>
</html>