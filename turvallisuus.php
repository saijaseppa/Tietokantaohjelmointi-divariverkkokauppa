<?php
  // Metodi tarkista_syote muotoilee annetun datan poistaen turhat
  // välilyönnit sekä /-merkit sekä muuttaa hmtl-merkit (< ja > -> &lt ja &gt).
  // Tämä estää Cross-site Scripting (CSS)-hyökkäyksiä.
  // Lähde: https://www.w3schools.com/php/php_form_validation.asp
  function tarkista_syote($sana)
  {
    $sana = trim($sana);
    $sana = stripslashes($sana);
    $sana = htmlspecialchars($sana);
    return $sana;
  }
?>
