<?php
  $next_monday = getStartOfWeekDate()->modify('+7 day');
  $next_tuesday = getStartOfWeekDate()->modify('+8 day');
  $next_wednesday = getStartOfWeekDate()->modify('+9 day');
  $next_thursday = getStartOfWeekDate()->modify('+10 day');
  $next_friday = getStartOfWeekDate()->modify('+11 day');
  $next_nextMonday = getStartOfWeekDate()->modify('+14 day');
  $next_nextTuesday = getStartOfWeekDate()->modify('+15 day');

  $nextWeek = array(
    array(
      "date" => $next_monday->format("Ymd"),
      "day" => $next_monday->format("d"),
      "shorthand" => "Lun",
      "label" => "Lunes",
    ),
    array(
      "date" => $next_tuesday->format("Ymd"),
      "day" => $next_tuesday->format("d"),
      "shorthand" => "Mar",
      "label" => "Martes"
    ),
    array(
      "date" => $next_wednesday->format("Ymd"),
      "day" => $next_wednesday->format("d"),
      "shorthand" => "Mié",
      "label" => "Miércoles"
    ),
    array(
      "date" => $next_thursday->format("Ymd"),
      "day" => $next_thursday->format("d"),
      "shorthand" => "Jue",
      "label" => "Jueves"
    ),
    array(
      "date" => $next_friday->format("Ymd"),
      "day" => $next_friday->format("d"),
      "shorthand" => "Vie",
      "label" => "Viernes"
    ),
    array(
      "date" => $next_nextMonday->format("Ymd"),
      "day" => $next_nextMonday->format("d"),
      "shorthand" => "Lun",
      "label" => "Sig. Lunes"
    ),
    array(
      "date" => $next_nextTuesday->format("Ymd"),
      "day" => $next_nextTuesday->format("d"),
      "shorthand" => "Mar",
      "label" => "Sig. Martes"
    )
  );
?>