<?php 
  require_once "includes/functions.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrador</title>
  <link rel="stylesheet" href="assets/css/color.css">
  <style>
    .row *{
      display: inline-block;
      margin: 10px;
      vertical-align: middle;
    }
    .row.crear > span input,
    .row.crear > span select{
      display: block;
      border: 1px solid gray;
      padding: 11px;
      margin: 0;
      border-radius: 5px;
      min-width: 100px;
    }
    .row.crear > span select{
      cursor: pointer;
    }
    .row:not(.crear) input:not(.update),
    .row:not(.crear) select:not(.update){
      border: 0;
      pointer-events: none;
    }
    .row .update{
      display: none;
    }
    .row:target input:not(.update),
    .row:target select:not(.update){
      border-bottom: 1px solid black;
      pointer-events: auto;
    }
    .row:target a{
      display: none;
    }
    .row:target .update{
      display: inline-block;
    }
    .modal{
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.85);
      position: fixed;
      top: 0;
      left: 0;
      display: none;
    }
    .modal:target{
      display: block;
    }
    .modal .content{
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      padding: 30px;
      background-color: white;
      text-align: center;
    }
    .modal .content input[type=submit]{
      display: block;
      margin-bottom: 30px;
    }
    .tabs{
      text-align: center;
    }
    .tabs > *{
      margin: 20px;
      display: inline-block;
    }
    .tabs .active{
      font-weight: bold;
      text-decoration: underline;
    }
    .wrapper{
      max-width: 800px;
      width: 80%;
      margin: 50px auto;
    }
    input[type=submit]{
      background-color: #00adff;
      vertical-align: bottom;
      color: white;
      font-size: 15px;
      border-radius: 0px;
      font-weight: bold;
      padding: 10px 20px;
      cursor: pointer;
    }
    a span{
      text-decoration: underline;
    }
    a:hover span{
      color: #00adff;
    }
    a.borrar:hover span{
      color: tomato;
    }
  </style>
</head>
<body>
  <div class="tabs">
    <a class="proyectos" href="?tab=proyectos"><span>Proyectos</span></a>
    <a class="ejecutivas" href="?tab=ejecutivas"><span>Ejecutivas</span></a>
    <a class="equipos" href="?tab=equipos"><span>Equipos</span></a>
  </div>
  <div class="wrapper">
    <?php 
      switch($_GET["tab"]){ 
        case "proyectos":
          $proyectos = connectToDb("all","proyectos",null,null,null,null);
      ?>
        <script>
          var tabActivo = document.querySelector('.proyectos');
          tabActivo.classList.add('active');
        </script>
        <?php foreach($proyectos as $key => $proyecto): ?>
          <div class="modal" id="delete-row-<?php echo $key; ?>">
            <div class="content">
              <form action="includes/crud.php" method="POST">
                <input type="submit" value="Confirmar">
                <input type="hidden" name="crud" value="delete">
                <input type="hidden" name="tabla" value="proyectos">
                <input type="hidden" name="id" value="<?php echo $proyecto["id"]; ?>">
                <a href="#"><span>Cancelar</span></a>
              </form>
            </div>
          </div>
          <form action="includes/crud.php" method="POST" class="row" id="row-<?php echo $key; ?>">
            <span>Nombre proyecto: <input type="text" name="nombre" value="<?php echo $proyecto["nombre"]; ?>"></span>
            <span>Ejecutiva: <select name="ejecutiva">
              <option value="" disabled selected></option>
              <?php 
                $ejecutivas = connectToDb("all","ejecutivas",null,null,null,null);
                foreach($ejecutivas as $ejecutiva){
                  $selected = "";
                  if($ejecutiva["id"] == $proyecto["ejecutiva"]) $selected = " selected";
              ?>
                <option value="<?php echo $ejecutiva["id"]; ?>" <?php echo $selected?>><?php echo $ejecutiva["nombre"]; ?></option>
                <?php }; ?>
            </select></span>
            <input type="hidden" name="id" value="<?php echo $proyecto["id"]; ?>">
            <input type="hidden" name="crud" value="update">
            <input type="hidden" name="tabla" value="proyectos">
            <a href="#row-<?php echo $key; ?>"><span>Editar</span></a>
            <a class="borrar" href="#delete-row-<?php echo $key; ?>"><span>Borrar</span></a>
            <input type="submit" value="Confirmar" class="update">
            <a href="#" class="update"><span>Cancelar</span></a>
          </form>
        <?php endforeach; ?>
        <form action="includes/crud.php" method="POST" class="row crear">
          <!-- <span>Nombre proyecto: <input type="text" name="nombre"></span> -->
          <span>ID Proyecto: <input type="number" name="id"></span>
          <span>Ejecutiva: <select name="ejecutiva">
            <option value="" disabled selected>Seleccione</option>
            <?php 
              $ejecutivas = connectToDb("all","ejecutivas",null,null,null,null);
              foreach($ejecutivas as $ejecutiva):
            ?>
              <option value="<?php echo $ejecutiva["id"]; ?>"><?php echo $ejecutiva["nombre"]; ?></option>
            <?php endforeach?>
          </select></span>
          <input type="hidden" name="crud" value="create">
          <input type="hidden" name="tabla" value="proyectos">
          <input type="submit" value="Crear" class="crear">
        </form>
      <?php break;
        case "ejecutivas":
          $ejecutivas = connectToDb("all","ejecutivas",null,null,null,null);
      ?>
        <script>
          var tabActivo = document.querySelector('.ejecutivas');
          tabActivo.classList.add('active');
        </script>
        <?php foreach($ejecutivas as $key => $ejecutiva): ?>
          <div class="modal" id="delete-row-<?php echo $key; ?>">
            <div class="content">
              <form action="includes/crud.php" method="POST">
                <input type="submit" value="Confirmar">
                <input type="hidden" name="crud" value="delete">
                <input type="hidden" name="tabla" value="ejecutivas">
                <input type="hidden" name="id" value="<?php echo $ejecutiva["id"]; ?>">
                <a href="#"><span>Cancelar</span></a>
              </form>
            </div>
          </div>
          <form action="includes/crud.php" method="POST" class="row" id="row-<?php echo $key; ?>">
            <span>Nombre ejecutiva: <input type="text" name="nombre" value="<?php echo $ejecutiva["nombre"]; ?>"></span>
            <span>Color: <input type="text" name="color" value="<?php echo $ejecutiva["color"]; ?>"></span>
            <input type="hidden" name="id" value="<?php echo $ejecutiva["id"]; ?>">
            <input type="hidden" name="crud" value="update">
            <input type="hidden" name="tabla" value="ejecutivas">
            <a href="#row-<?php echo $key; ?>"><span>Editar</span></a>
            <a class="borrar" href="#delete-row-<?php echo $key; ?>"><span>Borrar</span></a>
            <input type="submit" value="Confirmar" class="update">
            <a href="#" class="update"><span>Cancelar</span></a>
          </form>
        <?php endforeach; ?>
        <form action="includes/crud.php" method="POST" class="row crear">
          <span>Nombre ejecutiva: <input type="text" name="nombre"></span>
          <span>Color: <input type="text" name="color"></span>
          <span>ID Ejecutiva: <input type="number" name="id"></span>
          <input type="hidden" name="crud" value="create">
          <input type="hidden" name="tabla" value="ejecutivas">
          <input type="submit" value="Crear" class="crear">
        </form>
      <?php break;
          case "equipos":
          $equipos = connectToDb("all","equipos",null,null,null,null);
      ?>
        <script>
          var tabActivo = document.querySelector('.equipos');
          tabActivo.classList.add('active');
        </script>
        <?php foreach($equipos as $key => $equipo): ?>
          <div class="modal" id="delete-row-<?php echo $key; ?>">
            <div class="content">
              <form action="includes/crud.php" method="POST">
                <input type="submit" value="Confirmar">
                <input type="hidden" name="crud" value="delete">
                <input type="hidden" name="tabla" value="equipos">
                <input type="hidden" name="id" value="<?php echo $equipo["id"]; ?>">
                <a href="#"><span>Cancelar</span></a>
              </form>
            </div>
          </div>
          <form action="includes/crud.php" method="POST" class="row" id="row-<?php echo $key; ?>">
            <span>Nombre equipo: <input type="text" name="nombre" value="<?php echo $equipo["nombre"]; ?>"></span>
            <span>Color: <input type="text" name="color" value="<?php echo $equipo["color"]; ?>"></span>
            <input type="hidden" name="id" value="<?php echo $equipo["id"]; ?>">
            <input type="hidden" name="crud" value="update">
            <input type="hidden" name="tabla" value="equipos">
            <a href="#row-<?php echo $key; ?>"><span>Editar</span></a>
            <a class="borrar" href="#delete-row-<?php echo $key; ?>"><span>Borrar</span></a>
            <input type="submit" value="Confirmar" class="update">
            <a href="#" class="update"><span>Cancelar</span></a>
          </form>
        <?php endforeach; ?>
        <form action="includes/crud.php" method="POST" class="row crear">
          <span>Nombre equipo: <input type="text" name="nombre"></span>
          <span>Color: <input type="text" name="color"></span>
          <span>ID equipo: <input type="number" name="id"></span>
          <input type="hidden" name="crud" value="create">
          <input type="hidden" name="tabla" value="equipos">
          <input type="submit" value="Crear" class="crear">
        </form>
    <?php break;
        default:
    ?>
      <div>Seleccione un tab</div>
    <?php } ?>
  </div>
</body>
</html>