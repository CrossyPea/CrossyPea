<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Überprüfen, ob der Benutzer eingeloggt ist, sonst auf die Login-Seite umleiten
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.html");
    exit;
}

require 'config.php'; // Stellt die Datenbankverbindungsinformationen bereit

// SQL-Abfrage, um die höchsten Werte zu ermitteln
$sql = "SELECT MAX(strom) AS max_strom, MAX(ladestation) AS max_ladestation, MAX(wasser) AS max_wasser, MAX(oel) AS max_oel, MAX(MX3550_SW) AS max_3550SW, MAX(MX3550_Farbe) AS max_3550Farbe, MAX(MX4410_SW) AS max_4410SW, MAX(MX4410_Farbe) AS max_4410Farbe FROM verbrauch";
$result = $mysqli->query($sql);

// Variablen für die Defaultwerte initialisieren
$default_strom = 0;
$default_ladestation = 0;
$default_wasser = 0;
$default_oel = 0;
$default_3550SW = 0;
$default_3550Farbe = 0;
$default_4410SW = 0;
$default_4410Farbe = 0;

// ÃberprÃ¼fen, ob das Resultat Zeilen enthÃ¤lt
if ($result && $result->num_rows > 0) {
    // Daten auslesen
    $row = $result->fetch_assoc();
    $default_strom = $row['max_strom'];
    $default_ladestation = $row['max_ladestation'];
    $default_wasser = $row['max_wasser'];
    $default_oel = $row['max_oel'];
    $default_3550SW = $row['max_3550SW'];
    $default_3550Farbe = $row['max_3550Farbe'];
    $default_4410SW = $row['max_4410SW'];
    $default_4410Farbe = $row['max_4410Farbe'];
}


// Verarbeitung des Formulars nach dem Senden
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize und Validiere die Eingaben
    $datumInput = filter_input(INPUT_POST, 'datum', FILTER_DEFAULT);
    $datumObjekt = DateTime::createFromFormat('Y-m-d', $datumInput);
    $strom = filter_input(INPUT_POST, 'strom', FILTER_VALIDATE_FLOAT);
    $ladestation = filter_input(INPUT_POST, 'ladestation', FILTER_VALIDATE_FLOAT);$ladestation = filter_input(INPUT_POST, 'ladestation', FILTER_VALIDATE_FLOAT);
    $wasser = filter_input(INPUT_POST, 'wasser', FILTER_VALIDATE_FLOAT);
    $oel = filter_input(INPUT_POST, 'oel', FILTER_VALIDATE_FLOAT);
    $MX3550_SW = filter_input(INPUT_POST, 'MX3550_SW', FILTER_VALIDATE_INT);
    $MX3550_Farbe = filter_input(INPUT_POST, 'MX3550_Farbe', FILTER_VALIDATE_INT);
    $MX4410_SW = filter_input(INPUT_POST, 'MX4410_SW', FILTER_VALIDATE_INT);
    $MX4410_Farbe = filter_input(INPUT_POST, 'MX4410_Farbe', FILTER_VALIDATE_INT);
    $Bemerkungen = filter_var($_POST['Bemerkungen'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $user_id = $_SESSION['id']; // Benutzer-ID aus der Session

    // Datenbankverbindung aufbauen
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Überprüfe die Verbindung
    if ($mysqli === false) {
        die("ERROR: Could not connect. " . $mysqli->connect_error);
    }

    // Bereite das SQL-Statement vor (angepasst, um alle Parameter zu berÃ¼cksichtigen)
        $sql = "INSERT INTO verbrauch (datum, strom, ladestation, wasser, oel, MX3550_SW, MX3550_Farbe, MX4410_SW, MX4410_Farbe, Bemerkungen, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
          // Binde die Variablen an das vorbereitete Statement als Parameter
          $datum = $datumObjekt ? $datumObjekt->format('Y-m-d') : null; // ÃberprÃ¼fen, ob das Datum korrekt erstellt wurde
         // Stellen Sie sicher, dass alle Parameter korrekt gebunden werden
         $stmt->bind_param("sddddiiiisi", $datum, $strom, $ladestation, $wasser, $oel, $MX3550_SW, $MX3550_Farbe, $MX4410_SW, $MX4410_Farbe, $Bemerkungen, $user_id);

        // Versuche das vorbereitete Statement auszufÃ¼hren
        if($stmt->execute()){
            echo "Verbrauchsdaten erfolgreich gespeichert.";
        } else{
            echo "ERROR: Could not execute query: $sql. " . $mysqli->error;
        }
    } else{
        echo "ERROR: Could not prepare query: $sql. " . $mysqli->error;
    }

    // Schließe das Statement und die Verbindung
    $stmt->close();
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verbrauchsdaten eingeben</title>
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 10px;
        margin: 0;
    }
    .container {
        margin: 0 auto;
        max-width: 600px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
    }
    input[type=number],
    input[type=date],
    input[type=submit],
    input[type=text],
    textarea {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        box-sizing: border-box;
    }
    .form-group::after {
        content: "";
        display: table;
        clear: both;
    }
    input[type=submit] {
        cursor: pointer;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 5px;
    }
    input[type=submit]:hover {
        background-color: #45a049;
    }
    @media (min-width: 600px) {
        .form-group::after {
            display: block;
            content: "";
            visibility: hidden;
            height: 0;
            clear: both;
        }
        .form-group {
            display: flex;
            justify-content: space-between;
        }
        .form-group input[type=number] {
            width: 45%;
            float: left;
        }
        .form-group input[type=number]:last-child {
            float: right;
        }
    }

    /* Hier beginnen die zusätzlichen Styles für die MX3550-Eingaben */
    .mx-input-group {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 15px;
    }
    .mx-input-group label {
        width: 100%;
        margin-bottom: 5px;
    }
    .mx-input-group input {
        width: calc(50% - 4px); /* Subtrahiert 4px, um Platz für das margin zu lassen */
        margin-right: 4px; /* Fügt einen kleinen Abstand zwischen den Feldern hinzu */
    }
    .mx-input-group input:last-child {
        margin-right: 0;
    }

    /* Responsive Anpassungen für MX3550-Eingaben */
    @media (min-width: 600px) {
        .mx-input-group input {
            width: calc(50% - 4px); /* Subtrahiert 4px, um Platz für das margin zu lassen */
            margin-right: 4px; /* Fügt einen kleinen Abstand zwischen den Feldern hinzu */
        }
        .mx-input-group input:last-child {
            margin-right: 0;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <h2>Verbrauchsdaten eingeben</h2>
        <form action="eingabeformular.php" method="post">
            <div class="form-group">
                <label for="datum">Datum:</label>
                <input type="date" id="datum" name="datum" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="mx-input-group">
                <label for="strom">Strom (kWh x 50):     /      Ladestation</label>
                <input type="number" id="strom" name="strom" step="0.001" value="<?php echo htmlspecialchars($default_strom); ?>" required>
		<input type="number" id="ladestation" name="ladestation" step="0.001" value="<?php echo htmlspecialchars($default_ladestation); ?>">
            </div>
            <div class="form-group">
                <label for="wasser">Wasser (m³):</label>
                <input type="number" id="wasser" name="wasser" step="0.1" value="<?php echo htmlspecialchars($default_wasser); ?>" required>
            </div>
            <div class="form-group">
                <label for="oel">Öl (Liter):</label>
                <input type="number" id="oel" name="oel" step="0.01" value="<?php echo htmlspecialchars($default_oel); ?>" required>
            </div>
            <div class="mx-input-group">
                <label for="kopierer1">MX3550:</label>
                <input type="number" id="MX3550_SW" name="MX3550_SW" value="<?php echo htmlspecialchars($default_3550SW); ?>">
                <input type="number" id="MX3550_Farbe" name="MX3550_Farbe" value="<?php echo htmlspecialchars($default_3550Farbe); ?>">
            </div>
            <div class="mx-input-group">
                <label for="kopierer2">MX4410:</label>
                <input type="number" id="MX4410_SW" name="MX4410_SW" value="<?php echo htmlspecialchars($default_4410SW); ?>">
                <input type="number" id="MX4410_Farbe" name="MX4410_Farbe" value="<?php echo htmlspecialchars($default_4410Farbe); ?>">
            </div>
            <div class="form-group">
                <label for="bemerkungen">Bemerkungen:</label>
                <textarea id="Bemerkungen" name="Bemerkungen" rows="1" placeholder="Hier können Sie Bemerkungen eingeben"></textarea>
            </div>
            <input type="submit" value="Absenden">
        </form>
    </div>
</body>
</html>