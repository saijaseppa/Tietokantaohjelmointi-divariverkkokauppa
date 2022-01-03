<?php
// Tällä sivulla admin-roolin omaava käyttäjä voi lisätä
// uusia teoksia ja myyntikappaleita verkkokauppaan.
// Keskustietokannan ylläpitäjä voi lisätä uuden teoksen/myyntikappaleen
// mille divarille tahansa, mutta divarien ylläpitäjät voivat
// lisätä näitä vain omalle divarillensa.

session_start();
//Luodaan tietokantayhteys ja tarkistetaan kirjautumistiedot.
require 'tietokantayhteys.php';
include 'logged_in.php';

// Lippumuuttujien alustus.
$teos_loytyy = false;
$uusiteos = false;
$yliadmin = false;

// Kirjautuneen käyttäjän divari on nimessä (D1 tai D2 tai D3)
$adminin_divari = $_nimi;

// Jos ylläpitäjä on koko keskustietokannan ylläpitäjä D0, hän voi päättää itse
// mille divarille lisää uuden myyntikappaleen. Tämä lippumuuttuja määrittää
// näytettävän html-lomakkeen sisältöä.
if($_nimi === "D0"){
    $yliadmin = true;
}


if (isset($_POST['jatka']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Haetaan ensin tieto, löytyykö teoksen nimellä jo perustettua teosta tietokannasta. Jos teos löytyy, lisätään html:n loppulomake
    // jolla kysytään käyttäjältä loput tiedot.

    // Käyttäjän syöttämä teoksen nimi.
    $teos_nimi = $_POST["teoksen_nimi"];

    // Haetaan tietokannasta onko teos jo talletettu tietokantaan.
    $sql_1 = pg_query('SELECT * FROM keskusdivari.teos WHERE teos_nimi = \'' . $teos_nimi . '\'');
    $rowCount = pg_num_rows($sql_1);

    // Jos teoksen nimellä löytyi tietokannasta rivi, teos on jo perustettu.
    if ($rowCount > 0) {
        // Haetaan tietokannasta teoksen olemassaolevat tiedot.
        $teostiedot = pg_fetch_row($sql_1);

        $tekija = $teostiedot[2];
        $luokka = $teostiedot[3];
        $tyyppi = $teostiedot[4];
        $massa = $teostiedot[5];
        $isbn = $teostiedot[6];
        $vuosi = $teostiedot[7];

        // Muutetaan totuusarvo, jotta html-koodiin lisätään loppulomake.
        $teos_loytyy = true;
    } else {
        // Jos teos on uusi
        $uusiteos = true;
    }
}

// "lisaa"-nappi löytyy lomakkeelta, jolla lisätään vain uusi myyntikappale, kun
// teos jo löytyy tietokannasta.
if (isset($_POST['lisaa']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Muuttujat lomakkeelta.
    $teos_nimi = $_POST["teoksen_nimi"];
    $kunto = $_POST["kunto"];
    $myyntihinta = $_POST["mhinta"];
    $so_hinta = $_POST["so_hinta"];

    if ($yliadmin) {
        $divari = $_POST["divari"];
    }
    else {
        $divari = $adminin_divari;
    }

    // Tarkistetaan, ettei vaaditut kentät ole tyhjinä.
    if (!empty($kunto) && !empty($divari) && !empty($myyntihinta)) {
        // Syötetyn divarinimen perusteella haetaan divari-taulusta kyseisen divarin divari_id.
        $sql_divari = pg_query('SELECT divari_id FROM keskusdivari.divari WHERE divari_nimi LIKE \'' . $divari . '%' . '\'');
        $divariid = pg_fetch_row($sql_divari);
        $divari_id = $divariid[0];

        // Teoksen nimen perusteella haetaan teoksen id.
        $sql_teosid = pg_query('SELECT teos_id FROM keskusdivari.teos WHERE teos_nimi = \'' . $teos_nimi . '\'');
        $idtaulu = pg_fetch_row($sql_teosid);
        $teos_id = $idtaulu[0];

        // Suojataan muuttujat ennen tietokantaan tallettamista
        $_mhinta = floatval($myyntihinta);
        $_kunto = pg_escape_string($kunto);
        $_tila = '1';
        $_so_hinta = floatval($so_hinta);
        $_teos_id = intval($teos_id);
        $_divari_id = intval($divari_id);

        // Sql-lauseke myyntikappaleen tallettamiseen
        $sql_talletus = pg_query('INSERT INTO keskusdivari.myyntikappale (mkpl_hinta, mkpl_kunto, mkpl_tila, mkpl_so_hinta, mkpl_teosid, mkpl_divari_id)
                                VALUES(' . $_mhinta . ', \'' . $_kunto . '\', \'' . $_tila . '\', ' . $_so_hinta . ', ' . $_teos_id . ', ' . $_divari_id . ')');


        if (!$sql_talletus) {
            die('Tietojen talletus epäonnistui!');
        } else {
            // Asetetaan muuttujaan arvo, jotta html-koodiin tulee onnistumisilmoitus.
            $onnistui = "Talletus onnistui.";
        }
    }
}

// Nappi "lisaauusi" on täysin uuden teoksen lisäyslomakkeella
if (isset($_POST['lisaauusi']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    // Jos teos on täysin uusi, lisätään ensin tietokantaan teostiedot.
    $teos_nimi = pg_escape_string($_POST["teoksen_nimi"]);
    $teos_tekija = pg_escape_string($_POST["teoksen_tekija"]);
    $teos_luokka = pg_escape_string($_POST["luokka"]);
    $teos_tyyppi = pg_escape_string($_POST["tyyppi"]);
    $teos_paino = intval($_POST["paino"]);
    $teos_isbn = pg_escape_string($_POST["isbn"]);
    $teos_vuosi = intval($_POST["teoksen_vuosi"]);

    // Tarkistetaan, että vaaditut kentät on täytetty
    if (!empty($teos_nimi) && !empty($teos_tekija) && !empty($teos_paino) && !empty($teos_isbn)) {
        $sql_uusiteos = pg_query('INSERT INTO keskusdivari.teos (teos_nimi, teos_tekija, teos_luokka, teos_tyyppi, teos_massa, teos_isbn, teos_vuosi)
                            VALUES(\'' . $teos_nimi . '\', \'' . $teos_tekija . '\', \'' . $teos_luokka . '\', \'' . $teos_tyyppi . '\', ' . $teos_paino . ', \'' . $teos_isbn . '\', ' . $teos_vuosi . ')');

        if (!$sql_uusiteos) {
            die('Tietojen talletus epäonnistui! ');
        }

        // Seuraavaksi lisätään uusi myyntikappale kyseiseen teokseen.

        // Käyttäjän antamat tiedot lomakkeelta.
        $kunto = $_POST["kunto"];
        $divari = $adminin_divari;
        $myyntihinta = $_POST["mhinta"];
        $so_hinta = $_POST["so_hinta"];

        // Tarkistetaan, ettei vaaditut kentät ole tyhjinä.
        if (!empty($kunto) && !empty($divari) && !empty($myyntihinta)) {
            // Syötetyn divarinimen perusteella haetaan divari-taulusta kyseisen divarin divari_id.
            $sql_divari = pg_query('SELECT divari_id FROM keskusdivari.divari WHERE divari_nimi LIKE \'' . $divari . '%' . '\'');
            $divariid = pg_fetch_row($sql_divari);
            $divari_id = $divariid[0];

            // Teoksen nimen perusteella haetaan teoksen id.
            $sql_teosid = pg_query('SELECT teos_id FROM keskusdivari.teos WHERE teos_nimi = \'' . $teos_nimi . '\'');
            $idtaulu = pg_fetch_row($sql_teosid);
            $teos_id = $idtaulu[0];

            // Suojataan muuttujat ennen tietokantaan tallettamista
            $_mhinta = floatval($myyntihinta);
            $_kunto = pg_escape_string($kunto);
            $_tila = '1';
            $_so_hinta = floatval($so_hinta);
            $_teos_id = intval($teos_id);
            $_divari_id = intval($divari_id);

            // Sql-lauseke myyntikappaleen lisäämiseen
            $sql_talletus = pg_query('INSERT INTO keskusdivari.myyntikappale (mkpl_hinta, mkpl_kunto, mkpl_tila, mkpl_so_hinta, mkpl_teosid, mkpl_divari_id)
                            VALUES(' . $_mhinta . ', \'' . $_kunto . '\', \'' . $_tila . '\', ' . $_so_hinta . ', ' . $_teos_id . ', ' . $_divari_id . ')');


            if (!$sql_talletus) {
                die('Tietojen talletus epäonnistui! ');
            } else {
                // Asetetaan muuttujaan arvo, jotta html-koodiin tulee onnistumisilmoitus.
                $onnistui = "Talletus onnistui.";
            }
        }
        else {
            $ei_onnistunut = "Ei onnistunut.";
        }
    }
    else {
        $ei_onnistunut = "Ei onnistunut.";
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
    <title>Myyntikappaleen lisäys</title>
</head>

<body id="page-container">
    <div id="content-wrap">
        <?php include 'header.php'; ?>

        <div class="first-div">
            <blockquote class="blockquote text-center mt-3">
                <h2 class="mb-0"><small>Lisää tästä uusi teos tai myyntikappale verkkokauppaan:</small></h2>
                <h5 class="mb-0"><small>Kirjoita ensin hakukenttään lisättävän teoksen nimi ja klikkaa hae.</small></h5>
                <h5 class="mb-0"><small>Täytä sen jälkeen alle aukeavaan lomakkeeseen siitä puuttuvat tiedot.</small></h5>
                <h6 class="mb-0"><small>Tähdellä (*) merkityt kentät ovat pakollisia.</small></h6>
            </blockquote>
        </div>
        <div>
            <?php
            if (isset($onnistui)) {
                echo
                '<div class="alert alert-success" role="alert">
                Tallennus onnistui!
                </div>';
            }
            elseif (isset($ei_onnistunut)) {
                echo
                '<div class="alert alert-danger" role="alert">
                Pakollisia tietoja puuttui, yritä uudelleen.
                </div>';
            }
            ?>
        </div>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group row ml-2">
                <label for="teoksen_nimi" class="col-sm-2 col-form-label">Hae teoksen nimellä *</label>
                <div class="col-sm-8">
                    <input type="text" name="teoksen_nimi" placeholder="Lisää teoksen nimi" <?php if ($teos_loytyy || $uusiteos) {echo 'value="' . $teos_nimi . '"';} ?> class="form-control">
                </div>
            </div>

            <div class="col-md-6 col-sm-8 mt-3">
                <blockquote class="blockquote text-center mt-3">
                    <button type="submit" class="btn btn-primary" id="jatka" name="jatka">Hae</button>
                </blockquote>
            </div>
            <?php
            if ($teos_loytyy) {
                echo
                '<div class="form-group row ml-2">
                <label for="teosnimi" class="col-sm-2 col-form-label">Teoksen nimi *</label>
                <div class="col-sm-8">
                    <input class="form-control" type="text" placeholder="' . $teos_nimi .'" readonly>
                </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="teoksen_tekija" class="col-sm-2 col-form-label">Teoksen tekijä *</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" placeholder="' . $tekija . '" readonly>
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="paino" class="col-sm-2 col-form-label">Teoksen paino *</label>
                    <div class="input-group col-sm-8">
                        <input class="form-control" type="text" placeholder="' . $massa . '" readonly>
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="luokka" class="col-sm-2 col-form-label">Teoksen luokka</label>
                    <select aria-label="Luokan valinta." class="form-select" disabled>
                        <option selected>' . $luokka . '</option>
                        <option value="romantiikka">Romantiikka</option>
                        <option value="historia">Historia</option>
                        <option value="dekkari">Dekkari</option>
                        <option value="huumori">Huumori</option>
                        <option value="opas">Opas</option>
                    </select>
                </div>
                <div class="form-group row ml-2">
                    <label for="tyyppi" class="col-sm-2 col-form-label">Teoksen tyyppi</label>
                    <select class="form-select" aria-label="Tyypin valinta." disabled>
                        <option selected>' . $tyyppi . '</option>
                        <option value="romaani">Romaani</option>
                        <option value="sarjakuva">Sarjakuva</option>
                        <option value="tietokirja">Tietokirja</option>
                    </select>
                </div>
                <div class="form-group row ml-2">
                    <label for="isbn" class="col-sm-2 col-form-label">Teoksen isbn *</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" placeholder="' . $isbn . '" readonly>
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="teoksen_vuosi" class="col-sm-2 col-form-label">Teoksen julkaisuvuosi</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" placeholder="' . $vuosi . '" readonly>
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="kunto" class="col-sm-2 col-form-label">Teoksen kuntoluokka *</label>
                    <select class="form-select" aria-label="Teoksen kunnon valinta." name="kunto">
                        <option selected>Valitse kuntoluokitus</option>
                        <option value="tyydyttävä">Tyydyttävä</option>
                        <option value="hyvä">Hyvä</option>
                        <option value="erinomainen">Erinomainen</option>
                    </select>
                </div>';
                if ($yliadmin) {
                    // Keskustietokannan ylläpitäjälle näytettävä lomakeosuus:
                    echo
                    '<div class="form-group row ml-2">
                        <label for="divari" class="col-sm-2 col-form-label">Teosta myyvä divari *</label>
                        <div class="col-sm-8">
                            <input type="text" name="divari" placeholder="Lisää omistava divari (D1, D2, D3)"
                                class="form-control">
                        </div>
                    </div>';
                } else {
                    // Divarien ylläpitäjille näytettävä lomakeosuus:
                    echo
                    '<div class="form-group row ml-2">
                        <label for="divari" class="col-sm-2 col-form-label">Teosta myyvä divari *</label>
                        <div class="col-sm-8">
                            <input type="text" name="divari" placeholder="' . $adminin_divari . '"
                                class="form-control" readonly>
                        </div>
                    </div>';
                }
                echo
                '<div class="form-group row ml-2">
                    <label for="so_hinta" class="col-sm-2 col-form-label">Teoksen sisäänostohinta</label>
                    <div class="input-group col-sm-8">
                        <input type="text" name="so_hinta" placeholder="Lisää teoksen sisäänostohinta euroina" class="form-control">
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="mhinta" class="col-sm-2 col-form-label">Teoksen myyntihinta *</label>
                    <div class="input-group col-sm-8">
                        <input type="text" name="mhinta" placeholder="Lisää teoksen myyntihinta euroina" class="form-control">
                    </div>
                </div>
                <div class="col-md-6 col-sm-8 mt-3">
                    <blockquote class="blockquote text-center mt-3">
                        <button type="submit" class="btn btn-primary" id="lisaa" name="lisaa">Lisää teos</button>
                    </blockquote>
                </div>';
            }
            if ($uusiteos) {
                echo
                '<div class="form-group row ml-2">
                <label for="teosnimi" class="col-sm-2 col-form-label">Teoksen nimi *</label>
                <div class="col-sm-8">
                    <input class="form-control" type="text" name="teosnimi" placeholder="' . $teos_nimi .'" readonly>
                </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="teoksen_tekija" class="col-sm-2 col-form-label">Teoksen tekijä *</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" placeholder="Lisää teoksen tekijä" name="teoksen_tekija">
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="paino" class="col-sm-2 col-form-label">Teoksen paino *</label>
                    <div class="input-group col-sm-8">
                        <input class="form-control" type="text" placeholder="Lisää teoksen paino grammoina" name="paino">
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="luokka" class="col-sm-2 col-form-label">Teoksen luokka</label>
                    <select aria-label="Luokan valinta." name="luokka" class="form-select">
                        <option selected>Valitse teoksen luokitus</option>
                        <option value="romantiikka">Romantiikka</option>
                        <option value="historia">Historia</option>
                        <option value="dekkari">Dekkari</option>
                        <option value="huumori">Huumori</option>
                        <option value="opas">Opas</option>
                    </select>
                </div>
                <div class="form-group row ml-2">
                    <label for="tyyppi" class="col-sm-2 col-form-label">Teoksen tyyppi</label>
                    <select class="form-select" aria-label="Tyypin valinta." name="tyyppi">
                        <option selected>Valitse teoksen tyyppi</option>
                        <option value="romaani">Romaani</option>
                        <option value="sarjakuva">Sarjakuva</option>
                        <option value="tietokirja">Tietokirja</option>
                    </select>
                </div>
                <div class="form-group row ml-2">
                    <label for="isbn" class="col-sm-2 col-form-label">Teoksen isbn *</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" placeholder="Lisää teoksen isbn" name="isbn">
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="teoksen_vuosi" class="col-sm-2 col-form-label">Teoksen julkaisuvuosi</label>
                    <div class="col-sm-8">
                        <input class="form-control" type="text" placeholder="Lisää teoksen julkaisuvuosi" name="teoksen_vuosi">
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="kunto" class="col-sm-2 col-form-label">Teoksen kuntoluokka *</label>
                    <select class="form-select" aria-label="Teoksen kunnon valinta." name="kunto">
                        <option selected>Valitse kuntoluokitus</option>
                        <option value="tyydyttävä">Tyydyttävä</option>
                        <option value="hyvä">Hyvä</option>
                        <option value="erinomainen">Erinomainen</option>
                    </select>
                </div>';
                if ($yliadmin) {
                    echo
                    '<div class="form-group row ml-2">
                        <label for="divari" class="col-sm-2 col-form-label">Teosta myyvä divari *</label>
                        <div class="col-sm-8">
                            <input type="text" name="divari" placeholder="Lisää omistava divari (D1, D2, D3)"
                                class="form-control">
                        </div>
                    </div>';
                } else {
                    echo
                    '<div class="form-group row ml-2">
                        <label for="divari" class="col-sm-2 col-form-label">Teosta myyvä divari *</label>
                        <div class="col-sm-8">
                            <input type="text" name="divari" placeholder="' . $adminin_divari . '"
                                class="form-control" readonly>
                        </div>
                    </div>';
                }
                echo
                '<div class="form-group row ml-2">
                    <label for="so_hinta" class="col-sm-2 col-form-label">Teoksen sisäänostohinta</label>
                    <div class="input-group col-sm-8">
                        <input type="text" name="so_hinta" placeholder="Lisää teoksen sisäänostohinta euroina" class="form-control">
                    </div>
                </div>
                <div class="form-group row ml-2">
                    <label for="mhinta" class="col-sm-2 col-form-label">Teoksen myyntihinta *</label>
                    <div class="input-group col-sm-8">
                        <input type="text" name="mhinta" placeholder="Lisää teoksen myyntihinta euroina" class="form-control">
                    </div>
                </div>
                <div class="col-md-6 col-sm-8 mt-3">
                    <blockquote class="blockquote text-center mt-3">
                        <button type="submit" class="btn btn-primary" id="lisaauusi" name="lisaauusi">Lisää teos</button>
                    </blockquote>
                </div>';
            }
            ?>
        </form>
    </div>


    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous">
    </script>
</body>

</html>
