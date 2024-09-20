<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Vulnerabile (Protetto con Prepared Statements)</title>
</head>
<body>
    <h1>Sito Vulnerabile a SQL Injection (Protetto con Prepared Statements)</h1>

    <form action="sqlVuln_sanificato.php" method="GET">
        <label for="user_id">Inserisci nome utente:</label>
        <input type="text" id="user_id" name="user_id"><br><br>
        <input type="submit" value="Cerca">
    </form>

    <?php
    // Configurazione del database
    $servername = "10.0.0.4";  // IP del container del database
    $username = "user";
    $password = "password";
    $dbname = "testdb";

    // Creazione della connessione
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Controllo della connessione
    if ($conn->connect_error) {
        die("Connessione al database fallita: " . $conn->connect_error);
    }

    // Funzione di validazione dell'input
    function validaInput($data) {
        // Controlliamo se il campo è vuoto
        if (empty($data)) {
            return false;
        }
        
        // Rimuoviamo eventuali spazi bianchi
        $data = trim($data);
        
        // Filtriamo caratteri non desiderati (solo lettere e spazi)
        if (!preg_match("/^[a-zA-Z ]*$/", $data)) {
            return false;
        }
        
        return $data;
    }

    // Verifica se è stato inviato un parametro tramite GET
    if (isset($_GET['user_id'])) {
        $user = validaInput($_GET['user_id']);
        
        if ($user === false) {
            echo "Input non valido. Assicurati di inserire solo caratteri alfabetici.";
        } else {
            // Preparazione della query sicura con prepared statements
            $stmt = $conn->prepare("SELECT * FROM utenti WHERE nome = ?");
            $stmt->bind_param("s", $user);  // "s" indica che si tratta di una stringa
            
            // Esecuzione della query
            $stmt->execute();
            $result = $stmt->get_result();

            // Controllo del risultato
            if ($result->num_rows > 0) {
                // Output dei dati di ciascuna riga
                while ($row = $result->fetch_assoc()) {
                    echo "ID: " . $row["id"] . " - Nome: " . $row["nome"] . "<br>";
                }
            } else {
                echo "0 risultati trovati";
            }

            // Chiusura dello statement
            $stmt->close();
        }
    }

    // Chiusura della connessione
    $conn->close();
    ?>
</body>
</html>
