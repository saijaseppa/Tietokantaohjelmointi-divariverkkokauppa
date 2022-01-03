<?php
// Tällä sivulla käyttäjä näkee ostoskoriin lisäämänsä tuotteen (toiminnallisuus tällä hetkellä
// vain yhdelle myyntikappaleelle). Tällä sivulla käyttäjä voi myös tyhjentää ostoskorin ja vahvistaa
// tilauksen.

session_start();

//Luodaan tietokantayhteys ja tarkistetaan kirjautumistiedot.
require 'tietokantayhteys.php';
include 'logged_in.php';

$tulostaulukko = null;
$toimituskulut = null;
$paketti_paino = 0;
$pakettien_painot = [];
$postikulut = [];
$vaihtavat_idt = [];

// Jos ostoskorissa on tuotteita (toimii tällä hetkellä oikein vain, kun ostoskorissa on yksi tuote.).
if ($_SESSION['ostoskori']) {
    $kaikkientulostentaulukko = [];
    foreach ($_SESSION['ostoskori'] as $myyntikappale_id) {
        // Haetaan myyntikappaleen tiedot tietokannasta.
        $sql = "SELECT teos_nimi, teos_vuosi, teos_tekija, mkpl_hinta, mkpl_kunto, teos_tyyppi, teos_luokka, mkpl_id, teos_massa
            FROM keskusdivari.mkpl_tiedot
            WHERE mkpl_id = $myyntikappale_id;";

        $tulos = pg_query($sql);
        $tulostaulukko = pg_fetch_all($tulos);
        array_push($kaikkientulostentaulukko,$tulostaulukko);
    }
    foreach ($kaikkientulostentaulukko as $mkpl) {
        // Lasketaan paketin paino.
        if($paketti_paino + $mkpl[0]['teos_massa'] <= 2000){
          $paketti_paino = $paketti_paino + $mkpl[0]['teos_massa'];
        }
        else{
          array_push($vaihtavat_idt, $mkpl[0]['mkpl_id']);
          array_push($pakettien_painot, $paketti_paino);
          $paketti_paino = 0;
          $paketti_paino = $paketti_paino + $mkpl[0]['teos_massa'];
        }
    }
    array_push($pakettien_painot, $paketti_paino);
    foreach ($pakettien_painot as $paketti) {
      $sql2 = "SELECT hinta
      FROM keskusdivari.toimituskulut
      WHERE paino >= '$paketti'
      ORDER BY hinta;";

      $tulos2 = pg_query($sql2);
      $tulostaulukko2 = pg_fetch_all($tulos2);
      array_push($postikulut, $tulostaulukko2[0]['hinta']);

    }

    $toimituskulut = array_sum($postikulut);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Jos käyttäjä vahvistaa tilauksen.
    if (isset($_POST['tilaa'])) {
        // Tuotteiden lukumäärä ostoskorissa.
        $mkpl_maara = count($_SESSION['ostoskori']);

        foreach ($_SESSION['ostoskori'] as $mkpl_id) {
            // Päivitetään tuote myydyksi.
            $myyty_tuote = "UPDATE keskusdivari.myyntikappale
                    SET mkpl_tila = null
                    WHERE mkpl_id = $mkpl_id;";

            pg_query($myyty_tuote);
        }

        date_default_timezone_set('Europe/Helsinki');
        $aika = getdate();
        // Tilauksen päivämäärä.
        $pvm = "'" . $aika['year'] . '-' . $aika['mon'] . '-' . $aika['mday'] . "'";

        // Luodaan tilaus.
        $tilaus_sql = "INSERT INTO keskusdivari.tilaus(kayttaja_id, tilaus_tila, tilaus_pvm, tilaus_maksettu, tilaus_toimituskulut)
                    VALUES ($_SESSION[id], '0', $pvm, '1', $toimituskulut);";

        pg_query($tilaus_sql);

        // Haetaan äsken luodun tilauksen id.
        $_id_kysely = pg_query("SELECT tilaus_id FROM keskusdivari.tilaus WHERE kayttaja_id = $_SESSION[id] AND tilaus_pvm = $pvm ORDER BY tilaus_id DESC;");
        $_id = pg_fetch_all_columns($_id_kysely);
        $tilaus_id = $_id[0];

        $pakettien_idt = [];
        // Luodaan paketit.
        $ind1 = 0;
        foreach ($postikulut as $paketin_toimituskulut) {
          // Luodaan paketti.
          $paketti_sql = "INSERT INTO keskusdivari.paketti(tilaus_id, lahetys_pvm, mkpl_maara, paketti_paino, paketti_tila, paketti_toimituskulut)
                  VALUES ($tilaus_id, null, $mkpl_maara, $pakettien_painot[$ind1], '0',$paketin_toimituskulut);";

          pg_query($paketti_sql);
          $ind1 = $ind1 + 1;

          // Haetaan äsken luodun paketin id.
          $_id_kysely = pg_query("SELECT paketti_id FROM keskusdivari.paketti WHERE tilaus_id = $tilaus_id ORDER BY tilaus_id, paketti_id DESC;");
          $_id = pg_fetch_all_columns($_id_kysely);
          $paketti_id = $_id[0];
          // Lisätään paketin id taulukkoon.
          array_push($pakettien_idt, $paketti_id);
        }

        $ind2 = 0;
        // Päivitetään myydyn myyntikappaleen tietoihin tilaus_id, paketti_id ja myyntipäivämäärä.
        foreach ($_SESSION['ostoskori'] as $mkpl_id) {
            if($mkpl_id == $vaihtavat_idt[$ind2]){
              $ind2 = $ind2 + 1;
            }
            $mkpl_sql = "UPDATE keskusdivari.myyntikappale
                        SET mkpl_tilausid = $tilaus_id,
                            mkpl_paketti_id = $pakettien_idt[$ind2],
                            mkpl_myyntipvm = $pvm
                        WHERE mkpl_id = $mkpl_id;";

            pg_query($mkpl_sql);
        }

        // Tyhjennetään ostoskori.
        $_SESSION['ostoskori'] = [];

        // Siirrytään onnistunut_tilaus.php-sivulle.
        header('Location: onnistunut_tilaus.php');
    }
    // Jos käyttäjä tyhjentää ostoskorin.
    else if (isset($_POST['tyhjenna_kori'])) {
        foreach ($_SESSION['ostoskori'] as $mkpl_id) {
            // Palautetaan tuotteen tilaksi 1 eli 'vapaa'
            $vapauta_tuote = "UPDATE keskusdivari.myyntikappale
                    SET mkpl_tila = '1'
                    WHERE mkpl_id = $mkpl_id;";

            pg_query($vapauta_tuote);

            // Tyhjennetään ostoskori.
            $_SESSION['ostoskori'] = [];

            // Päivitetään ostoskorisivu.
            header('Location: ostoskori.php');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
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
    <title>Ostoskori</title>
  </head>

  <body id="page-container">
    <div id="content-wrap">
        <?php include 'header.php' ?>
        <div class="first-div">
            <blockquote class="blockquote text-center mt-4">
                <h2 class="pt-3 pb-2"><small>Ostoskori</small></h2>
            </blockquote>
            <div class="container">
            <ul class="tulokset" style=list-style-type:none padding-left:0>
            <?php
                // Jos ostoskorissa on tuotteita.
                if ($tulostaulukko && $toimituskulut) {
                    foreach ($kaikkientulostentaulukko as $tuote) {
                        echo '
                        <div class="card">'.
                        '<li class="tulos">'.
                            '<div>'.
                                $tuote[0]['teos_nimi'] . ', ' .
                                $tuote[0]['teos_vuosi'] . ' - ' .
                                $tuote[0]['teos_tekija'] .
                            '</div>' .
                            '<div>' .
                                'tyyppi: '.
                                $tuote[0]['teos_tyyppi'] . ' / luokka: ' .
                                $tuote[0]['teos_luokka'] .
                            '</div>'.
                            '<div>'.
                                'hinta: ' .
                                $tuote[0]['mkpl_hinta'] . '€ / kunto: ' .
                                $tuote[0]['mkpl_kunto'] .
                            '</div>'.
                            '</li>'.
                            '</div>' ;
                    }
                    echo '<div>'.
                        'toimituskulut: ' .
                        $toimituskulut . '€' .
                    '</div>' ;
                    // Lisätään painikkeet tilauksen vahvistamiselle sekä ostoskorin tyhjentämiselle.
                    echo '<div>' .
                        '<form method="post">' .
                            '<input type="hidden" name="tilaa" value="tilaa"></input>' .
                            '<input type="submit" class="btn btn-primary mt-2 mb-2" value="Vahvista tilaus"></input>' .
                        '</form>' .
                        '<form method="post">' .
                            '<input type="hidden" name="tyhjenna_kori" value="tyhjenna_kori"></input>' .
                            '<input type="submit" class="btn btn-primary mb-4" value="Tyhjennä ostoskori"></input>' .
                        '</form>' .
                    '</div>';
                }
                else {
                    echo '<div class="mt-2 mb-4">' .
                        'Ostoskorisi on tyhjä' .
                    '</div>';
                }
            ?>
            </ul>
          </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>

</html>
