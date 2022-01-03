<?php
// Tämä sivu on käyttäjän asetuksia varten, tarkistetaan mikä rooli kirjautuneella
// käyttäjällä on, ja näytetään sivun sisältö sen mukaisesti.

session_start();

//Luodaan tietokantayhteys ja tarkistetaan kirjautumistiedot.
require 'tietokantayhteys.php';
include 'logged_in.php';

//Haetaan tietokannasta tieto, onko käyttäjän rooli asiakas vai admin. 
$rooli_kysely= pg_query('SELECT kayttaja_rooli FROM keskusdivari.kayttaja WHERE kayttaja_email = \'' . $_email . '\'');
if (!$rooli_kysely) {
echo "Virhe kyselyssä.";
}
//Haetaan saadun tiedon perusteella halutut arvot taulusta. Muuttuja $rooli on array, joten $rooli[0] on toivottu kyselyn tulos. 
$rooli = pg_fetch_all_columns($rooli_kysely);
// Kun kirjautuneella käyttäjällä on admin-rooli, hänellä on oikeus lisätä tietokantaan teoksia ja myyntikappaleita.
// 
if ($rooli[0] === "admin") {
    include 'admin_hallinta.php';
}
// Kun kirjautuneen käyttäjän rooli on asiakas, hänellä on oikeudet tarkastella ja muokata omia tietojaan.
else {
    include 'asiakastietojen_muokkaus.php';
} 

pg_close($yhteys);
?>