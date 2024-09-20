<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Vulnerabile</title>
</head>
<body>
    <h1>Sito Vulnerabile a SQL Injection</h1>

    <form action="sqlVuln.php" method="GET">
        <label for="user_id">Inserisci nome utente:</label>
        <input type="text" id="user_id" name="user_id"><br><br>
        <input type="submit" value="Cerca">
    </form>

    <p>Prova con un attacco SQL come: <code>' OR '1'='1</code></p>

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

    // Verifica se è stato inviato un parametro tramite GET
    if (isset($_GET['user_id'])) {
        // Query vulnerabile a SQL Injection
        $user = $_GET['user_id'];
        $query = "SELECT * FROM utenti WHERE nome = '$user'";
        
        // Esecuzione della query
        $result = $conn->query($query);

        echo "<h2>Risultati della ricerca:</h2>";
        if ($result->num_rows > 0) {
            // Output dei dati di ciascuna riga
            while($row = $result->fetch_assoc()) {
                echo "ID: " . $row["id"] . " - Nome: " . $row["nome"] . "<br>";
            }
        } else {
            echo "0 risultati trovati";
        }
    }

    // Chiusura della connessione
    $conn->close();
    ?>
</body>
</html>

