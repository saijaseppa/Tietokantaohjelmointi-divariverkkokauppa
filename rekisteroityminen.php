<?php
// Uuden asiakkaan rekisteröityminen tällä sivulla.
session_start();

//Luodaan tietokantayhteys
require 'tietokantayhteys.php';

//Muuttujien alustus.
$email = $nimi = $osoite = $puhelin = $salasana = $salasanaCheck =  "";
$salasanaCheck_err = $email_err = "";
$rooli = "asiakas";

// Kun käyttäjä klikkaa peruuta-nappia, palataan takaisin
// tervetuloa-sivulle, eikä tietoja tallenneta.
if (isset($_POST['peruuta']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    header('Location: tervetuloa.php');
}

// Kun käyttäjä klikkaa rekisteroidy-nappia, tarkistetaan, että sähköpostiosoite
// ei vielä ole käytössä. Jos kaikki ok, tallennetaan tiedot tietokantaan ja 
// siirrytään etusivulle.
if (isset($_POST['rekisteroidy']) && $_SERVER["REQUEST_METHOD"] == "POST") {

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

    //Tarkistetaan käyttäjän antamat syötteet tarkista_syote -metodin avulla. 
    $email = tarkista_syote($_POST["email"]);
    $nimi = tarkista_syote($_POST["nimi"]);
    $osoite = tarkista_syote($_POST["osoite"]);
    $puhelin = tarkista_syote($_POST["puhelin"]);
    $salasana = $_POST["salasana"];

    // Tarkistetaan vastaavatko kahden salasanakentän tekstit toisiaan, jos eivät
    // annetaan virheviesti (tässä vielä käytettävyysongelma: koko lomake tyhjenee jos salasanat
    // ei vastaa toisiaan). 
    if ($_POST["salasanaCheck"] !== $_POST["salasana"]) {
        $salasanaCheck_err = "Salasanat eivät vastanneet toisiaan.";

        // Jos salasanat vastasivat toisiaan, tallenetaan tiedot tietokantaan ja siirrytään etusivulle. 
    } else {

        // Haetaan tietokannasta onko sähköposti jo talletettu tietokantaan.
        $email_query = pg_query('SELECT * FROM keskusdivari.kayttaja WHERE kayttaja_email = \'' . $email . '\'');
        $rowCount = pg_num_rows($email_query);

        //Edetään, jos mitään kenttää ei ole jätetty tyhjäksi.
        if (!empty($email) && !empty($nimi) && !empty($osoite) && !empty($puhelin) && !empty($salasana) && !empty($_POST["salasanaCheck"])) {
            // Katsotaan annetun sähköpostin olemassaolo aiemman kyselyn tuloksesta.
            if ($rowCount > 0) {
                $email_err = "Sähköpostiosoite on jo käytössä!";
            } else {
                // Suojataan tiedot ennen niiden lähettämistä tietokantaan. 
                $_email = pg_escape_string($email);
                $_nimi = pg_escape_string($nimi);
                $_osoite = pg_escape_string($osoite);
                $_puhelin = pg_escape_string($puhelin);
                $_salasana = pg_escape_string($salasana);
                $_rooli = pg_escape_string($rooli);

                $sql_talletus = pg_query('INSERT INTO keskusdivari.kayttaja (kayttaja_nimi, kayttaja_email, kayttaja_osoite, kayttaja_puhelin, kayttaja_rooli, kayttaja_salasana)
                                 VALUES(\'' . $_nimi . '\', \'' . $_email . '\', \'' . $_osoite . '\', \'' . $_puhelin . '\', \'' . $_rooli . '\', \'' . $_salasana . '\')');

                if (!$sql_talletus) {
                    die('Tietojen talletus epäonnistui! ');
                }

                // Haetaan tietokannasta juuriluodun käyttäjän id
                $_id_kysely = pg_query('SELECT kayttaja_id FROM keskusdivari.kayttaja WHERE kayttaja_email = \'' . $_email . '\'');
                $_id = pg_fetch_all_columns($_id_kysely);
                // Talletetaan sessiomuuttujat kirjautumistiedoista:
                $_SESSION['id'] = $_id[0];
                $_SESSION['nimi'] = $_nimi;
                $_SESSION['osoite'] = $_osoite;
                $_SESSION['email'] = $_email;
                $_SESSION['puhelin'] = $_puhelin;
                $_SESSION['ostoskori'] = [];

                // Kun tiedot on talletettu onnistuneesti tietokantaan, kirjaudutaan sisään eli siirrytään etusivulle. 
                header('Location: etusivu.php');
            }
        }
    }
}

?>

<!DOCTYPE HTML>
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
    <title>Rekisteröityminen</title>
</head>

<body id="page-container">
    <div id="content-wrap">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand">Rekisteröidy verkkokauppaamme täyttämällä alla olevat tiedot:</a>
        </nav>

        <form class="row g-3 col-sm-8 mt-4" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="col-md-6 mt-2">
                <label for="email" class="form-label">Sähköposti</label>
                <input type="email" id="email" name="email" class="form-control
                <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $email_err; ?></span>
            </div>
            <div class="col-md-6 mt-2">
                <label for="nimi" class="form-label">Nimi</label>
                <input type="text" class="form-control" id="nimi" name="nimi">
            </div>
            <div class="col-md-6 mt-2">
                <label for="salasana" class="form-label">Salasana</label>
                <input type="password" class="form-control" id="salasana" name="salasana" aria-describedby="passwordHelpBlock">
                <div id="passwordHelpBlock" class="form-text">
                    Salasanan tulee olla 8-15 merkkiä pitkä.
                </div>
            </div>
            <div class="col-md-6 mt-2">
                <label for="salasanaCheck" class="form-label">Salasana uudelleen</label>
                <input type="password" id="salasanaCheck" name="salasanaCheck" class="form-control
            <?php echo (!empty($salasanaCheck_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $salasanaCheck_err; ?></span>
            </div>

            <div class="col-12 mt-3">
                <label for="osoite" class="form-label">Osoite</label>
                <input type="text" class="form-control" id="osoite" name="osoite">
            </div>
            <div class="col-12 mt-3">
                <label for="puhelin" class="form-label">Puhelinnumero</label>
                <input type="text" class="form-control" id="puhelin" name="puhelin">
            </div>
            <div class="col-12 mt-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="invalidCheck" required>
                    <label class="form-check-label" for="invalidCheck">
                        Olen lukenut käyttöehdot ja tarkistanut syöttämäni tiedot
                    </label>
                    <div class="invalid-feedback mt-3">
                        Käyttöehdot on hyväksyttävä
                    </div>
                </div>
            </div>
            <br><br>
            <div class="col-md-6 col-sm-8 mt-3">
                <button type="submit" class="btn btn-primary" id="rekisteroidy" name="rekisteroidy">Rekisteröidy</button>
            </div>
            <div class="col-md-6 mt-3">
                <button type="submit" class="btn btn-primary" a href="tervetuloa.php" id="peruuta" name="peruuta">Peruuta</button>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>

</html>

<?php
pg_close($yhteys); ?>