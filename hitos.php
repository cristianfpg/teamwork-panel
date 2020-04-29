<?php 
  require_once "includes/fetchTWData.php";
  require_once "data/currentWeek.php";

  $exportData = exportData($currentWeek,null,null,null);
  $timeline = (object) array();
  $milestones = array();

  for($i = 0; $i < 5; $i++){
    $date = date("Y-m-01");
    $date = strtotime($date);
    $monthDate = strtotime("+$i month", $date);
    $month = date("m",$monthDate);
    $year = date("Y",$monthDate);

    $nombre = strftime("%B", $monthDate);
    $cantidadDias = cal_days_in_month(CAL_GREGORIAN, intval($month), intval($year));

    $timeline->{$month} = (object) array(
      "nombre" => $nombre,
      "dias" => $cantidadDias,
      "year" => $year
    );

  }

  $today = date("d");
  $thismonth = date("m");

  foreach($exportData->{"projects"} as $account){
    foreach($account->{"milestones"} as $key => $milestone){
      if($key != "0"){
        array_push($milestones, $milestone->{"data"});
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Hitos</title>
  <link rel="stylesheet" href="assets/css/color.css">
</head>
<body>
  <div id="hitos">
    <div class="milestones">
      <span class="etiqueta">Hito</span>
      <?php foreach($milestones as $milestone): ?>
        <a href="https://coloralcuadrado.teamwork.com/#/milestones/<?php echo $milestone->{"id"};; ?>" class="titulo-milestone" target="_blank"><span><?php echo $milestone->{"title"}; ?></span></a>
      <?php endforeach; ?>
    </div>
    <?php foreach($timeline as $keyMonth => $month): ?>
      <div class="month">
        <span class="etiqueta"><?php echo $month->{"nombre"}; ?></span>
        <div class="dias" style="grid-template-columns: repeat(<?php echo $month->{"dias"}; ?>, 1fr);">
          <?php for($keyDay = 1; $keyDay<=$month->{"dias"};$keyDay++): ?>
            <?php 
              $todayClass = "";
              $mondayClass = "";
              $cuadroDia = $month->{"year"}."/".($keyMonth)."/".($keyDay < 10 ? "0".$keyDay : $keyDay)." 00:00:00";
              $cuadroDia = strtotime($cuadroDia);
              $keyDaysMonday = date('l', $cuadroDia);

              if($keyMonth == $thismonth && $keyDay == $today){
                $todayClass = "hoy";
              }

              if($keyDaysMonday == "Saturday" || $keyDaysMonday == "Sunday"){
                $mondayClass = "findesemana";
              }

              if($keyDaysMonday == "Monday"){
                $mondayClass = "marcasemana";
              }
            ?>
            <div class="dia dia-<?php echo $keyDay." ".$todayClass." ".$mondayClass; ?>">
              <?php foreach($milestones as $milestone): ?>
                <?php
                  $year = substr($milestone->{"deadline"}, 0, 4);
                  $monthDl = substr($milestone->{"deadline"}, 4, 2);
                  $day = substr($milestone->{"deadline"}, 6, 2);
                  $idMilestone = $milestone->{"id"};
                  $active = "";

                  if($month->{"year"} <= $year){
                    if(intval($monthDl) > $keyMonth){
                      $active = "active";
                    }
  
                    if(intval($monthDl) >= $keyMonth && intval($day) >= $keyDay){
                      $active = "active";
                    }

                    if(intval($monthDl) == $keyMonth && intval($day) == $keyDay){
                      $active .= " entrega";
                    }
                  }
                ?>
                <a href="https://coloralcuadrado.teamwork.com/#/milestones/<?php echo $idMilestone; ?>" class="cuadrado <?php echo $active; ?>" target="_blank"></a>
              <?php endforeach; ?>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>