<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSS Vulnerable Website</title>
</head>
<body>
    <h1>Simulazione di un sito vulnerabile a XSS con cookie</h1>

    <p>Inserisci il tuo nome per memorizzarlo nei cookie:</p>
    <form action="xssVulnCookie.html" method="POST">
        <label for="username">Nome:</label><br>
        <input type="text" id="username" name="username"><br><br>
        <input type="submit" value="Salva nome">
    </form>
<!-- Per proteggere le applicazioni web da XSS, bisognerebbe:
*Sanificare l'input: Verificare e pulire l'input dell'utente per rimuovere qualsiasi codice potenzialmente eseguibile.
*Sanificare l'output: Utilizzare funzioni come htmlspecialchars() in PHP per rendere sicuro l'output. -->
    <hr>

    <h2>Benvenuto:</h2>
    <p><?php 
        if(isset($_POST['username'])) {
            setcookie("username", $_POST['username'], time() + (86400 * 30), "/");
            echo $_POST['username'];
        } else if (isset($_COOKIE['username'])) {
            echo $_COOKIE['username'];
        }
    ?></p>

</body>
</html>
