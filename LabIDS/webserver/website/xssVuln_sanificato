<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Vulnerable Website</title>
</head>
<body>
    <h1>Simulazione di un sito vulnerabile a XSS (Reflected)</h1>

    <p>Inserisci un commento:</p>
    <form action="xssVuln.php" method="GET">
        <label for="comment">Commento:</label><br>
        <textarea id="comment" name="comment" rows="4" cols="50"></textarea><br><br>
        <input type="submit" value="Invia">
    </form>

    <hr>

    <h2>Commenti inseriti:</h2>

    <div>
        <p><?php
        if(isset($_GET['comment'])) {
            $comment = htmlspecialchars($_GET['comment'], ENT_QUOTES, 'UTF-8');
            echo $comment;
        }
        ?></p>
    </div>

</body>
</html>

