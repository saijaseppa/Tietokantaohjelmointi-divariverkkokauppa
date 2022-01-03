<?php

// Metodi test_input muotoilee annetun datan poistaen turhat
// välilyönnit sekä /-merkit sekä muuttaa hmtl-merkit (< ja > -> &lt ja &gt).
// Tämä estää Cross-site Scripting (CSS)-hyökkäyksiä. 
function test_input($sana) {
  $sana = trim($sana);
  $sana = stripslashes($sana);
  $sana = htmlspecialchars($sana);
  return $sana;
}
?>