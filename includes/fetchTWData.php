<?php 
  require_once "functions.php";

  function exportData($week,$selectedTeams,$getEventsGoogleCalendar,$ejecutivas){
    $selectedEjecutiva = detectEjecutiva($ejecutivas[0]);
    if(count($ejecutivas) >= 1 && !$selectedEjecutiva) return false;

    $quinceDiasDespues = getStartOfWeekDate()->modify('+15 day')->format('Ymd');

    $ep = "tasks.json?startdate=".$week[0]["date"]."&enddate=".$quinceDiasDespues."&sort=duedate&filter=within14";

    $tasks = json_decode(apiCall($ep));
    $milestones = json_decode(apiCall("milestones.json?find=upcoming"));

    $tasklistWithMilestones = (object) array();

    foreach($milestones->milestones as $milestone){
      foreach($milestone->tasklists as $tasklist){
        $arrTemp = array();
        
        if(!is_object($tasklistWithMilestones->{$tasklist->id})){
          $tasklistWithMilestones->{$tasklist->id} = array();
        }else{
          $arrTemp = $tasklistWithMilestones->{$tasklist->id};
        }

        array_push($arrTemp, $milestone);

        $tasklistWithMilestones->{$tasklist->id} = $arrTemp;
      }
    }

    $projects = new stdClass();
    $allTeams = array();
    
    foreach($tasks->{"todo-items"} as $task){

      $parentTaskId = $task->{"parentTaskId"};
      $dueDate = $task->{"due-date"};
      $projectId = $task->{"project-id"};

      if(count($ejecutivas) >= 1){
        $cuentaCorrecta = false;
        foreach($selectedEjecutiva[1] as $cuenta){
          if($cuenta == $projectId) $cuentaCorrecta = true;
        }
        if(!$cuentaCorrecta) continue;
      }

      if(
          // EVALUA CUANDO INICIA LA TAREA Y SI TIENE TAREAS PADRE, ANTES SOLO SE TRAIAN LAS DE LA SEMANA CON EL CODIGO COMENTADO, AHORA SE TRAEN TODAS QUE INICIEN LA SEMANA SIN IMPORTAR CUANDO ACABEN
          // (
          //   (
          //     $dueDate >= $week[0]["date"] && 
          //     $dueDate <= $week[4]["date"]
          //   ) || 
          //   $dueDate == $week[5]["date"] || 
          //   $dueDate == $week[6]["date"]
          // ) 
          // && $parentTaskId != ""
          $dueDate >= $week[0]["date"] &&
          $parentTaskId != ""
        ){
        $resposabilityPartyIds = $task->{"responsible-party-ids"};
        $taskId = $task->{"id"};
        $content = $task->{"content"};
        $projectName = $task->{"project-name"};
        $todoListName = $task->{"todo-list-name"};
        $todoListId = $task->{"todo-list-id"};
        $parentTask = $task->{"parent-task"};
        $startDate = $task->{"start-date"};
        
        $teamsId = array();
        $responsables = explode(",",$resposabilityPartyIds);

        if(count($selectedTeams) >= 1 && $selectedTeams[0] != ""){
          foreach($selectedTeams as $st){
            foreach($responsables as $responsable){
              if($responsable == $st){
                $team = detectTeam($responsable);
                if($team) array_push($teamsId, $team);
                if(!array_search($responsable,$allTeams) && $team) array_push($allTeams, $responsable);
              }
            }
          }  
        }else{
          foreach($responsables as $responsable){
            $team = detectTeam($responsable);
            if($team) array_push($teamsId, $team);
            if(!array_search($responsable,$allTeams) && $team) array_push($allTeams, $responsable);
          }
        }

        if(count($teamsId) == 0) continue;
      
        $taskData = new stdClass();
        $taskData->{"start-date"} = $startDate;
        $taskData->{"due-date"} = $dueDate;
        $taskData->{"content"} = $content;

        foreach($teamsId as $teamId){
          if(!is_object($projects->{$projectId})){
            $projects->{$projectId} = (object) array(
              "milestones" => (object) array(),
              "project-name" => $projectName,
            );
          };

          $firstMilestone = array();
          
          if(is_array($tasklistWithMilestones->{$todoListId})){
            $firstMilestone = $tasklistWithMilestones->{$todoListId}[0];
          }else{
            $firstMilestone = (object) array(
              "id" => 0
            );
          }

          if(!is_object($projects->{$projectId}->{"milestones"}->{$firstMilestone->id})){
            $milestoneDeadline = strtotime($firstMilestone->deadline);
            $milestoneDeadline = utf8_encode(strftime("%A %e de %B del %Y",$milestoneDeadline));
            $projects->{$projectId}->{"milestones"}->{$firstMilestone->id} = (object) array(
              "task-lists" => (object) array(),
              "title" => $firstMilestone->title,
              "deadline-label" => $milestoneDeadline,
              "deadline-date" => $firstMilestone->deadline,
              "data" => $firstMilestone
            );
          };

          if(!is_object($projects->{$projectId}->{"milestones"}->{$firstMilestone->id}->{"task-lists"}->{$todoListId})){
            $projects->{$projectId}->{"milestones"}->{$firstMilestone->id}->{"task-lists"}->{$todoListId} = (object) array(
              "parent-tasks" => (object) array(),
              "title" => $todoListName
            );
          };

          if(!is_object($projects->{$projectId}->{"milestones"}->{$firstMilestone->id}->{"task-lists"}->{$todoListId}->{"parent-tasks"}->{$parentTaskId})){
            $projects->{$projectId}->{"milestones"}->{$firstMilestone->id}->{"task-lists"}->{$todoListId}->{"parent-tasks"}->{$parentTaskId} = (object) array(
              "teams" => (object) array(),
              "parent-task-name" => $parentTask->{"content"}
            );
          };

          if(!is_object($projects->{$projectId}->{"milestones"}->{$firstMilestone->id}->{"task-lists"}->{$todoListId}->{"parent-tasks"}->{$parentTaskId}->{"teams"}->{$teamId[1]})){
            $projects->{$projectId}->{"milestones"}->{$firstMilestone->id}->{"task-lists"}->{$todoListId}->{"parent-tasks"}->{$parentTaskId}->{"teams"}->{$teamId[1]} = (object) array(
              "tasks" => (object) array(),
              "color" => $teamId[3],
            );
          }
          $projects->{$projectId}->{"milestones"}->{$firstMilestone->id}->{"task-lists"}->{$todoListId}->{"parent-tasks"}->{$parentTaskId}->{"teams"}->{$teamId[1]}->{"tasks"}->{$taskId} = $taskData;
        }
      }
    }

    // if(!$selectedEjecutiva && false){
    //   $allTeams = array_unique($allTeams);
      
    //   $projects->{"reuniones"} = (object) array(
    //     "task-lists" => (object) array(
    //       "reunion" => (object) array(
    //         "parent-tasks" => (object) array(
    //           "reunion" => (object) array(
    //             "teams" => (object) array(),
    //             "parent-task-name" => ""
    //           ),
    //         ),
    //         "title" => ""
    //       ),
    //     ),
    //   );

      // foreach($allTeams as $teamId){
      //   $team = detectTeam($teamId);
      //   $events = $getEventsGoogleCalendar($team[2]);
        
      //   if(is_array($events)){
      //     if(!is_object($projects->{"reuniones"}->{"task-lists"}->{"reunion"}->{"parent-tasks"}->{"reunion"}->{"teams"}->{$team[1]})){
      //       $projects->{"reuniones"}->{"task-lists"}->{"reunion"}->{"parent-tasks"}->{"reunion"}->{"teams"}->{$team[1]} = (object) array(
      //         "tasks" => (object) array(),
      //       );
      //     }
      //     foreach($events as $event){
      //       $eventContent = $event->getSummary();
    
      //       if(!$eventContent) continue;
      //       $projects->{"reuniones"}->{"project-name"} = "REUNIONES";
    
      //       $eventDate = date("Ymd", strtotime($event->start->dateTime));
    
      //       $projects->{"reuniones"}->{"task-lists"}->{"reunion"}->{"parent-tasks"}->{"reunion"}->{"teams"}->{$team[1]}->{"tasks"}->{$event->id} = (object) array(
      //         "start-date" => $eventDate,
      //         "due-date" => $eventDate,
      //         "content" => $eventContent,
      //         "event" => true
      //       );
      //     }
      //   }
      // }
    // }

    return (object) array(
      "projects" => $projects,
      "week" => $week,
      "tasks" => $tasks,
      "milestones" => $milestones,
    );
  }
?>