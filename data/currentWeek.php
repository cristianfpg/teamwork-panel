<?php
  $monday = getStartOfWeekDate();
  $tuesday = getStartOfWeekDate()->modify('+1 day');
  $wednesday = getStartOfWeekDate()->modify('+2 day');
  $thursday = getStartOfWeekDate()->modify('+3 day');
  $friday = getStartOfWeekDate()->modify('+4 day');
  $nextMonday = getStartOfWeekDate()->modify('+7 day');
  $nextTuesday = getStartOfWeekDate()->modify('+8 day');

  $currentWeek = array(
    array(
      "date" => $monday->format("Ymd"),
      "day" => $monday->format("d"),
      "shorthand" => "Lun",
      "label" => "Lunes",
    ),
    array(
      "date" => $tuesday->format("Ymd"),
      "day" => $tuesday->format("d"),
      "shorthand" => "Mar",
      "label" => "Martes"
    ),
    array(
      "date" => $wednesday->format("Ymd"),
      "day" => $wednesday->format("d"),
      "shorthand" => "Mié",
      "label" => "Miércoles"
    ),
    array(
      "date" => $thursday->format("Ymd"),
      "day" => $thursday->format("d"),
      "shorthand" => "Jue",
      "label" => "Jueves"
    ),
    array(
      "date" => $friday->format("Ymd"),
      "day" => $friday->format("d"),
      "shorthand" => "Vie",
      "label" => "Viernes"
    ),
    array(
      "date" => $nextMonday->format("Ymd"),
      "day" => $nextMonday->format("d"),
      "shorthand" => "Lun",
      "label" => "Sig. Lunes"
    ),
    array(
      "date" => $nextTuesday->format("Ymd"),
      "day" => $nextTuesday->format("d"),
      "shorthand" => "Mar",
      "label" => "Sig. Martes"
    )
  );
?>