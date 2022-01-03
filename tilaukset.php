<?php
session_start();

//Luodaan tietokantayhteys
require 'tietokantayhteys.php';

include 'logged_in.php';

//Indeksit

$id = 'tilaus_id';
$hinta = 'tilaus_hinta';
$kpl = 'mkpl_maara';
$pvm = 'tilaus_pvm';
$tila = 'tilaus_tila';
$tilaus_id = null;

$query = "SELECT tilaus_id, tilaus_hinta, mkpl_maara, tilaus_pvm, tilaus_tila" . "
FROM keskusdivari.asiakkaan_tilaukset WHERE kayttaja_id=" . $_id . ";";

$query_sisalto = "SELECT  mkpl_id, teos_nimi, teos_tekija, teos_vuosi, teos_tyyppi, teos_luokka, teos_isbn, mkpl_hinta, mkpl_kunto" ."
                  FROM keskusdivari.mkpl_tiedot"."
                  WHERE mkpl_id IN (SELECT mkpl.mkpl_id"."
                  FROM keskusdivari.myyntikappale AS mkpl"."
                  WHERE mkpl.mkpl_tilausid=";

$tulos = pg_query($query);
$tulos_taulukko = pg_fetch_all($tulos);

$tilaus_sisalto = null;

?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
     integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style>
        #page-container {
            position: relative;
            min-height: 100vh;
        }
        #content-wrap {
            padding-bottom: 3rem;
        }
        #footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 3rem;
        }
    </style>
    <title>Tilauksesi</title>
  </head>

  <body id="page-container">
    <div id="content-wrap">
        <?php include 'header.php' ?>
        <div class="first-div">
            <blockquote class="blockquote text-center mt-4">
                <h2 class="mb-0"><small>Tilaukset</small></h2>
            </blockquote>
        </div>
        <?php if (isset($tulos)) : ?>
        <div class="container">
          <h4 class="pt-3 pb-2"><small>Aiemmat tilauksesi:</small></h4>
          <?php
            if (!$tulos_taulukko) {
              echo "<div> Sinulla ei ole vielä tilauksia.</div>";
            }
            else {
              echo
                '<table class=\'table table-striped\'>'.
                  '<thead>
                    <tr>
                      <th>Tilauksen tunnus</th>
                      <th>Hinta</th>
                      <th>Myyntikappaleiden määrä</th>
                      <th>Tilaus päivämäärä</th>
                      <th>Tilauksen tila</th>
                    </tr>
                   </thead>'.
                  '<tbody class="accordion" id="order-accordion">';
                foreach ($tulos_taulukko as $rivi) {
                echo
                    '<tr class="clickable" data-toggle="collapse" data-target="#collapse'. $rivi[$id] .'">
                      <td>'.$rivi[$id].'</td>
                      <td>'.$rivi[$hinta].'</td>
                      <td>'.$rivi[$kpl].'</td>
                      <td>'.$rivi[$pvm].'</td>
                      <td>';
                      if($rivi[$tila] == 1){
                        echo 'Lähetetty</td>';}
                      elseif ($rivi[$tila] == 0) {
                        echo 'Odottaa </td>';
                      }
                      else{
                        echo 'Ei tietoa </td>';
                      }

                    echo '</tr>'.
                    '<tr id="collapse'.$rivi[$id].'" class="accordion-collapse collapse">
                      <td colspan="5">';
                      $order_query = $query_sisalto . $rivi[$id] . ");";
                      $tilaus_sisalto = pg_query($order_query);
                      if($tilaus_sisalto){
                        $sisalto_rivit = pg_fetch_all($tilaus_sisalto);
                        if(!$sisalto_rivit){
                          echo "<div>Tilauksen sisältö ei saatavilla</div>";
                        }
                        else{
                          echo
                          '<table>
                            <thead>
                            <tr>
                              <th>Nimi</th>
                              <th>Tekijä</th>
                              <th>Vuosi</th>
                              <th>Tyyppi</th>
                              <th>Luokka</th>
                              <th>ISBN</th>
                              <th>Hinta</th>
                              <th>Kunto</th>
                            </tr>
                            </thead>
                            <tbody>';
                          foreach ($sisalto_rivit as $sis_rivi) {
                            //teos_nimi, teos_tekija, teos_vuosi, teos_tyyppi, teos_luokka, teos_isbn, mkpl_hinta, mkpl_kunto
                            echo
                              '<tr>
                                <td>'.$sis_rivi['teos_nimi'].' </td>
                                <td>'.$sis_rivi['teos_tekija'].' </td>
                                <td>'.$sis_rivi['teos_vuosi'].' </td>
                                <td>'.$sis_rivi['teos_tyyppi'].' </td>
                                <td>'.$sis_rivi['teos_luokka'].' </td>
                                <td>'.$sis_rivi['teos_isbn'].' </td>
                                <td>'.$sis_rivi['mkpl_hinta'].' </td>
                                <td>'.$sis_rivi['mkpl_kunto'].' </td>
                              </tr>';
                          }
                          echo
                          '</tbody>
                           </table>';
                        }
                      }
                      echo '</td>
                          </tr>';
                      }

                  echo '</tbody>'.
                '</table>';
                echo'<div>Tarkastele tilauksia tarkemmin klikkaamalla riviä!</div>';
            }
          ?>
        </div>
        <?php endif; ?>
        <?php include 'footer.php'; ?>
    </div>


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>

</html>
