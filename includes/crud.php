<?php 
  require_once "functions.php";

  $tabla = "";

  function crudProyectos(){
    switch($_POST["crud"]){
      case "create":
        $project = json_decode(apiCall("projects/".$_POST["id"].".json"))->{"project"}->{"name"};

        connectToDb("create","proyectos","id",$_POST["id"],null,null);
        connectToDb("update","proyectos","ejecutiva",$_POST["ejecutiva"],"id",$_POST["id"]);
        connectToDb("update","proyectos","nombre",$project,"id",$_POST["id"]);
        break;
      case "update":
        connectToDb("update","proyectos","ejecutiva",$_POST["ejecutiva"],"id",$_POST["id"]);
        break;
      case "delete":
        connectToDb("delete","proyectos","id",$_POST["id"],null,null);
        break;
    }
  }

  function crudEjecutivas(){
    switch($_POST["crud"]){
      case "create":
        connectToDb("create","ejecutivas","id",$_POST["id"],null,null);
        connectToDb("update","ejecutivas","nombre",$_POST["nombre"],"id",$_POST["id"]);
        connectToDb("update","ejecutivas","color",$_POST["color"],"id",$_POST["id"]);
        break;
      case "update":
        connectToDb("update","ejecutivas","nombre",$_POST["nombre"],"id",$_POST["id"]);
        connectToDb("update","ejecutivas","color",$_POST["color"],"id",$_POST["id"]);
        break;
      case "delete":
        connectToDb("delete","ejecutivas","id",$_POST["id"],null,null);
        break;
    }
  }

  function crudEquipos(){
    switch($_POST["crud"]){
      case "create":
        connectToDb("create","equipos","id",$_POST["id"],null,null);
        connectToDb("update","equipos","nombre",$_POST["nombre"],"id",$_POST["id"]);
        connectToDb("update","equipos","color",$_POST["color"],"id",$_POST["id"]);
        break;
      case "update":
        connectToDb("update","equipos","nombre",$_POST["nombre"],"id",$_POST["id"]);
        connectToDb("update","equipos","color",$_POST["color"],"id",$_POST["id"]);
        break;
      case "delete":
        connectToDb("delete","equipos","id",$_POST["id"],null,null);
        break;
    }
  }

  if(strlen($_POST["id"]) < 2){
    header("Location: ../admin-s83nsi4jsd9.php");
    exit;
  }

  switch($_POST["tabla"]){
    case "proyectos":
      crudProyectos();
      $tabla = "proyectos";
      break;
    case "ejecutivas":
      crudEjecutivas();
      $tabla = "ejecutivas";
      break;
    case "equipos":
      crudEquipos();
      $tabla = "equipos";
      break;
  }

  header("Location: ../admin-s83nsi4jsd9.php?tab=$tabla");
?>