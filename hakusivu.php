<?php
// Tällä sivulla on toteutettu myyntikappaleiden hakuominaisuus sekä tuotteiden lisääminen ostoskoriin.

session_start();

//Luodaan tietokantayhteys ja tarkistetaan kirjautumistiedot.
require 'tietokantayhteys.php';
require 'turvallisuus.php';
include 'logged_in.php';


$lisatty_ostoskoriin = false;
$haku_luokan_mukaan = false;

// Haetaan lomakkeen täytetyt kohdat.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Jos tuote lisätään ostoskoriin.
    if (isset($_POST['mkpl_id'])) {
        $mkpl_id = $_POST['mkpl_id'];

        $lisays = "UPDATE keskusdivari.myyntikappale
            SET mkpl_tila = '0'
            WHERE mkpl_id = $mkpl_id;";

        // Asetetaan tuote varatuksi.
        pg_query($lisays);

        // Lisätään tuote ostoskoriin eli sessiomuuttujaan 'ostoskori'.
        array_push($_SESSION['ostoskori'], $mkpl_id);
        $lisatty_ostoskoriin = true;
    }

    // Haetaan täytetyt hakukentät.
    $teoksen_nimi = (isset($_POST['teoksen_nimi']) ? tarkista_syote($_POST['teoksen_nimi']) : null);
    $teoksen_tekija = (isset($_POST['teoksen_tekija']) ? tarkista_syote($_POST['teoksen_tekija']) : null);
    $luokka = (isset($_POST['luokka']) ? $_POST['luokka'] : null);
    $tyyppi = (isset($_POST['tyyppi']) ? $_POST['tyyppi'] : null);

    if ($teoksen_nimi || $teoksen_tekija || $luokka || $tyyppi) {
        // Alustetaan kysely.
        $sql = "SELECT teos_nimi, teos_vuosi, teos_tekija, mkpl_hinta, mkpl_kunto, teos_tyyppi, teos_luokka, mkpl_tila, mkpl_id
                FROM keskusdivari.mkpl_tiedot
                WHERE ";

        // Jos haettu teoksen nimellä, muokataan kyselyä.
        if ($teoksen_nimi) {
            $sql .= "teos_nimi LIKE '%" . $teoksen_nimi . "%'";

            if ($teoksen_tekija || $luokka || $tyyppi) {
                $sql .= " AND ";
            }
        }

        // Jos haettu teoksen tekijällä, muokataan kyselyä.
        if ($teoksen_tekija) {
            $sql .= "teos_tekija LIKE '%" . $teoksen_tekija . "%'";

            if ($luokka || $tyyppi) {
                $sql .= " AND ";
            }
        }

        // Jos haettu teoksen luokalla, muokataan kyselyä.
        if ($luokka) {
            // Kyselyn alustus luokan keskihinnan ja kokonaishinnan selvittämiseksi.
            $luokka_kysely = "SELECT teos_luokka, SUM(mkpl_hinta) as kok_myyntihinta, ROUND(AVG(mkpl_hinta), 2) as keskihinta
            FROM keskusdivari.myyntikappale, keskusdivari.teos
            WHERE mkpl_teosid = teos_id AND mkpl_tila IS NOT NULL AND ";

            $sql .= "(";
            $luokka_kysely .= "(";
            $viimeinen = end($luokka);

            // Käydään läpi kaikki valitut luokat.
            foreach ($luokka as $luokan_nimi) {
                $sql .= "teos_luokka = '" . $luokan_nimi . "'";
                $luokka_kysely .= "teos_luokka = '" . $luokan_nimi . "'";

                if ($luokan_nimi != $viimeinen) {
                    $sql .= " OR ";
                    $luokka_kysely .= " OR ";
                }
            }

            $sql .= ") ";
            $luokka_kysely .= ") GROUP BY teos_luokka ORDER BY teos_luokka;";

            if ($tyyppi) {
                $sql .= " AND ";
            }

            // Lisäys koodiin toteuttamaan raportti 2 toteutuksen:
            // Kun haetaan luokan avulla, lisätään hakutulosten loppuun
            // rivi, jolla kerrotaan kyseisen luokan myyntikappaleiden
            // keskihinta sekä kokonaismyyntihinta.

            $kyselytulos = pg_query($luokka_kysely);
            $luokka_taulukko = pg_fetch_all($kyselytulos);
            $haku_luokan_mukaan = true;

        }

        // Jos haettu teoksen tyypillä, muokataan kyselyä.
        if ($tyyppi) {
            $sql .= "(";
            $viimeinen2 = end($tyyppi);

            // Käydään läpi kaikki valitut tyypit.
            foreach ($tyyppi as $tyypin_nimi) {
                $sql .= "teos_tyyppi = '" . $tyypin_nimi . "'";

                if ($tyypin_nimi != $viimeinen2) {
                    $sql .= " OR ";
                }
            }

            $sql .= ")";
        }

        $sql .= " AND mkpl_tila IS NOT NULL;";

        // Toteutetaan kysely.
        $tulos = pg_query($sql);
        $tulos_taulukko = pg_fetch_all($tulos);
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
    <title>Divarin hakusivu</title>
  </head>

  <body id="page-container">
    <div id="content-wrap">
        <?php include 'header.php'; ?>
        <div class="container">
            <form method="post" action="<?php $_SERVER['PHP_SELF'];?>">
                <blockquote class="blockquote text-center mt-3">
                    <?php
                    if ($lisatty_ostoskoriin) {
                        echo '<h3 class="mb-4"><small>Tuote lisätty ostoskoriin!</small></h3>';
                    }
                    ?>
                    <h4><small>
                        Rajaa hakua
                    </small></h4>
                </blockquote>
                <blockquote class="blockquote text-center mt-3">
                    <h6><small><?php
                        // Jos mitään hakukenttää ei ole täytetty.
                        if (empty($teoksen_nimi) && empty($teoksen_tekija) && empty($tyyppi) && empty($luokka)) {
                            echo "Valitse vähintään yksi hakuehto";
                        }
                        ?>
                    </small></h6>
                </blockquote>
                <div class="form-group row ml-2">
                    <label for="teoksen_nimi" class="col-sm-2 col-form-label">Teoksen nimi</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="teoksen_nimi" placeholder="Lisää teoksen nimi">
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="teoksen_tekija" class="col-sm-2 col-form-label">Teoksen tekijä</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="teoksen_tekija" placeholder="Lisää teoksen tekijä">
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <div class="col-sm-2">
                        Luokka
                    </div>
                    <div class="col-sm-10" id="luokka">
                        <div class="form-check">
                            <label class="form-check-label" for="romantiikka">
                                <input class="form-check-input" id="romantiikka" type="checkbox" name="luokka[]" value="romantiikka">
                                Romantiikka
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label" for="historia">
                                <input class="form-check-input" id="historia" type="checkbox" name="luokka[]" value="historia">
                                Historia
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label" for="dekkari">
                                <input class="form-check-input" id="dekkari" type="checkbox" name="luokka[]" value="dekkari">
                                Dekkari
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label" for="huumori">
                            <input class="form-check-input" id="huumori" type="checkbox" name="luokka[]" value="huumori">
                                Huumori
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label" for="opas">
                                <input class="form-check-input" id="opas" type="checkbox" name="luokka[]" value="opas">
                                Opas
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <div class="col-sm-2">Tyyppi</div>
                    <div class="col-sm-10">
                        <div class="form-check">
                            <label class="form-check-label" for="romaani">
                                <input class="form-check-input" id="romaani" type="checkbox" name="tyyppi[]" value="romaani">
                                Romaani
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label" for="sarjakuva">
                                <input class="form-check-input" id="sarjakuva" type="checkbox" name="tyyppi[]" value="sarjakuva">
                                Sarjakuva
                            </label>
                        </div>
                        <div class="form-check">
                            <label class="form-check-label" for="tietokirja">
                                <input class="form-check-input" id="tietokirja" type="checkbox" name="tyyppi[]" value="tietokirja">
                                Tietokirja
                            </label>
                        </div>
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary">
                        Hae
                    </button>
                    </div>
                </div>
            </form>
            <?php if (isset($tulos)) : ?>
                <h4 class="pt-3 pb-2"><small>Hakutulokset:</small></h4>

                <ul class="tulokset" style=list-style-type:none padding-left:0><?php
                    if (!$tulos_taulukko) {
                        echo "Ei tuloksia";
                    }
                    else {
                        foreach ($tulos_taulukko as $rivi) {
                            // Tulostetaan hakutulokset.
                            echo '<li class="tulos">'.
                                    '<div>'.
                                        $rivi['teos_nimi'] . ', ' .
                                        $rivi['teos_vuosi'] . ' - ' .
                                        $rivi['teos_tekija'] .
                                    '</div>' .
                                    '<div>' .
                                        'tyyppi: '.
                                        $rivi['teos_tyyppi'] . ' / luokka: ' .
                                        $rivi['teos_luokka'] .
                                    '</div>'.
                                    '<div>'.
                                        'hinta: ' .
                                        $rivi['mkpl_hinta'] . '€ / kunto: ' .
                                        $rivi['mkpl_kunto'] .
                                    '</div>';

                                    if ($rivi['mkpl_tila'] == 1) {
                                        // Jos tuotetta ei ole varattu, lisätään 'lisää ostoskoriin'-painike.
                                        echo '<div>' .
                                                '<form method="post">' .
                                                    '<input type="hidden" name="mkpl_id" value="' . $rivi['mkpl_id'] . '"></input>' .
                                                    '<input type="submit" class="btn btn-primary mt-2 mb-4" value="Lisää ostoskoriin"></input>' .
                                                '</form>' .
                                            '</div>';
                                    }
                                    else {
                                        // Jos tuote on varattu, sitä ei voi lisätä ostoskoriin.
                                        echo '<div class="mt-2 mb-4">' .
                                                'Tuote varattu' .
                                        '</div>';
                                    }

                                    echo '</li>';
                        }
                    }
                ?></ul>
            <?php endif; ?>

            <div>
                <?php
                    // Jos on haettu luokan mukaan, muodostetaan hakutulosten alle tiedot luokan keskihinnasta
                    // sekä kokonaismyyntihinnasta.
                    if ($haku_luokan_mukaan) {
                        if (pg_num_rows($kyselytulos) > 0) {
                            foreach ($luokka_taulukko as $row) {
                                // Tulostetaan tulokset
                                echo
                                '<ul class="luokkatulos">'.
                                        '<div><b>'.
                                            'Haettu luokka: '.
                                            $row['teos_luokka'] .
                                        '</b></div>' .
                                        '<li>' .
                                            'Luokan myyntikappaleiden keskihinta: '.
                                            $row['keskihinta'] . '€. ' .
                                        '</li>'.
                                        '<li>'.
                                            'Luokan myyntikappaleiden kokonaismyyntihinta: ' .
                                            $row['kok_myyntihinta'] . '€. ' .
                                        '</li>
                                </ul>';
                            }
                        }
                        else {
                            echo "";
                        }
                    }
                ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  </body>

</html>
