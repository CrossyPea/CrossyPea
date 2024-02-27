<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Überprüfen, ob der Benutzer eingeloggt ist, sonst auf die Login-Seite umleiten
if (!isset($_SESSION['loggedin'])) {
    header("Location: index.html");
    exit;
}

require 'config.php'; // Stellt die Datenbankverbindungsinformationen bereit

// SQL-Abfrage, um die höchsten Werte zu ermitteln
$sql = "SELECT MAX(strom) AS max_strom, MAX(wasser) AS max_wasser, MAX(oel) AS max_oel FROM verbrauch";
$result = $mysqli->query($sql);

// Variablen für die Defaultwerte initialisieren
$default_strom = '';
$default_wasser = '';
$default_oel = '';

// Überprüfen, ob das Resultat Zeilen enthält
if ($result && $result->num_rows > 0) {
    // Daten auslesen
    $row = $result->fetch_assoc();
    $default_strom = $row['max_strom'];
    $default_wasser = $row['max_wasser'];
    $default_oel = $row['max_oel'];
}


// Verarbeitung des Formulars nach dem Senden
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize und Validiere die Eingaben
    $datumInput = filter_input(INPUT_POST, 'datum', FILTER_DEFAULT);
    $datumObjekt = DateTime::createFromFormat('Y-m-d', $datumInput);
    $strom = filter_input(INPUT_POST, 'strom', FILTER_VALIDATE_FLOAT);
    $wasser = filter_input(INPUT_POST, 'wasser', FILTER_VALIDATE_FLOAT);
    $oel = filter_input(INPUT_POST, 'oel', FILTER_VALIDATE_FLOAT);
    $MX3550_SW = filter_input(INPUT_POST, 'MX3550_SW', FILTER_VALIDATE_INT);
    $MX3550_Farbe = filter_input(INPUT_POST, 'MX3550_Farbe', FILTER_VALIDATE_INT);
    $MX4410_SW = filter_input(INPUT_POST, 'MX4410_SW', FILTER_VALIDATE_INT);
    $MX4410_Farbe = filter_input(INPUT_POST, 'MX4410_Farbe', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['id']; // Benutzer-ID aus der Session

    // Datenbankverbindung aufbauen
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Überprüfe die Verbindung
    if ($mysqli === false) {
        die("ERROR: Could not connect. " . $mysqli->connect_error);
    }

    // Bereite das SQL-Statement vor (angepasst, um alle Parameter zu berücksichtigen)
        $sql = "INSERT INTO verbrauch (datum, strom, wasser, oel, MX3550_SW, MX3550_Farbe, MX4410_SW, MX4410_Farbe, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if($stmt = $mysqli->prepare($sql)){
          // Binde die Variablen an das vorbereitete Statement als Parameter
          $datum = $datumObjekt ? $datumObjekt->format('Y-m-d') : null; // Überprüfen, ob das Datum korrekt erstellt wurde
         // Stellen Sie sicher, dass alle Parameter korrekt gebunden werden
         $stmt->bind_param("sdddi", $datum, $strom, $wasser, $oel, $MX3550_SW, $MX3550_Farbe, $MX4410_SW, $MX4410_Farbe, $user_id);

        // Versuche das vorbereitete Statement auszuführen
        if($stmt->execute()){
            echo "Verbrauchsdaten erfolgreich gespeichert.";
        } else{
            echo "ERROR: Could not execute query: $sql. " . $mysqli->error;
        }
    } else{
        echo "ERROR: Could not prepare query: $sql. " . $mysqli->error;
    }

    // Schließe das Statement und die Verbindung
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
            <div class="form-group">
                <label for="strom">Strom (kWh x 50):</label>
                <input type="number" id="strom" name="strom" step="0.001" value="<?php echo htmlspecialchars($default_strom); ?>" required>
            </div>
            <div class="form-group">
                <label for="wasser">Wasser (m³):</label>
                <input type="number" id="wasser" name="wasser" step="0.1" value="<?php echo htmlspecialchars($default_wasser); ?>" required>
            </div>
            <div class="form-group">
                <label for="oel">Öl (Liter):</label>
                <input type="number" id="oel" name="oel" step="0.01" value="<?php echo htmlspecialchars($default_oel); ?>" required>
            </div>
            <div class="form-group">
                <label for="kopierer1">MX3550:</label>
                <input type="number" id="MX3550_SW" name="MX3550_SW" placeholder="SW">
                <input type="number" id="MX3550_Farbe" name="MX3550_Farbe" placeholder="Farbe">
            </div>
            <div class="form-group">
                <label for="kopierer2">MX4410:</label>
                <input type="number" id="MX4410_SW" name="MX4410_SW" placeholder="SW">
                <input type="number" id="MX4410_Farbe" name="MX4410_Farbe" placeholder="Farbe">
            </div>
            <div class="form-group">
                <label for="bemerkungen">Bemerkungen:</label>
                <textarea id="bemerkungen" name="bemerkungen" rows="1" placeholder="Hier können Sie Bemerkungen eingeben"></textarea>
            </div>
            <input type="submit" value="Absenden">
        </form>
    </div>
</body>
</html>