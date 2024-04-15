<?php
$host = "localhost";
$db = "gestione_libreria";
$user = "root";
$pass = "";
$dsn = "mysql:host=$host;dbname=$db";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Controlla se è stata caricata un'immagine
    if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {
        // Percorso di destinazione dell'immagine
        $upload_directory = 'uploads/';
        // Nome del file dell'immagine
        $image_name = $_FILES['immagine']['name'];
        // Percorso completo del file dell'immagine sul server
        $image_path = $upload_directory . $image_name;
        // Sposta il file dall'area temporanea alla destinazione
        move_uploaded_file($_FILES['immagine']['tmp_name'], $image_path);

        // Recupera gli altri dati dal modulo
        $titolo = $_POST['titolo'];
        $autore = $_POST['autore'];
        $anno_pubblicazione = $_POST['anno_pubblicazione'];
        $genere = $_POST ['genere'];

        try {
            // Prepara e esegui la query per inserire i dati nel database
            $stmt = $pdo->prepare("INSERT INTO libri (immagine, titolo, autore, anno_pubblicazione, genere) VALUES (:immagine, :titolo, :autore, :anno_pubblicazione, :genere)");

            $stmt->execute([
                'immagine' => $image_path,
                'titolo' => $titolo,
                'autore' => $autore,
                'anno_pubblicazione' => $anno_pubblicazione,
                'genere' => $genere,
            ]);
            echo "Dati inseriti con successo!";
            header('Location: \Esercizi\U2-S1-L5\index.php');
            exit();
        } catch (PDOException $e) {
            die("Errore durante l'inserimento dei dati: " . $e->getMessage());
        }
    } else {
        
        $errors['immagine'] = 'Errore nel caricamento dell\'immagine';
    }
}

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM libri WHERE id = :id");
$stmt->execute(['id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Eliminazione record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    if (!is_numeric($id)) {
        echo "L'ID utente deve essere un numero.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM libri WHERE id = ?");
        $stmt->execute([$id]);
        echo "Record eliminato con successo!";
        
    }
}

// Ricerca per nome
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['search'])) {
    $search = $_GET['search'];

    $stmt = $pdo->prepare("SELECT * FROM libri WHERE titolo LIKE ? OR autore LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);

    $results = $stmt->fetchAll();
}

// Visualizzazione di tutti i record paginati
$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$stmt = $pdo->prepare('SELECT * FROM libri LIMIT :limit OFFSET :offset');
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$paginated_results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ricerca e Eliminazione Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: rgb(238,174,202);
background: radial-gradient(circle, rgba(238,174,202,1) 0%, rgba(233,176,148,0.6475840336134453) 100%);
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="mt-5 mb-4">Gestione libri</h1>





  <div >  <button type="submit" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">Inserisci</button> </div>
<!----------------------------------------------------------------------- Modale inserimento libro --------------------------------->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            
            <div class="modal-body">
            <h3>Inserisci libro</h3>
            <form style="width: 460px" method="POST"  enctype="multipart/form-data">
            <div class="mb-3">
            <label for="Immagine" class="form-label">Immagine</label>
            <input type="file" class="form-control" name="immagine" id="immagine" value="<?php echo $immagine['immagine'] ?? ''; ?>" />
        </div>
        <div class="mb-3">
            <label for="Titolo" class="form-label">Titolo</label>
            <input type="text" class="form-control" name="titolo" id="titolo" value="<?php echo $titolo['titolo'] ?? ''; ?>" />
        </div>
        <div class="mb-3">
            <label for="Autore" class="form-label">Autore</label>
            <input type="text" class="form-control" name="autore" id="autore" value="<?php echo $autore['autore'] ?? ''; ?>" />
        </div>
        <div class="mb-3">
            <label for="Anno pubblicazione" class="form-label">Anno pubblicazione</label>
            <input type="text" class="form-control" name="anno_pubblicazione" id="anno_pubblicazione" value="<?php echo $anno_pubblicazione['anno_pubblicazione'] ?? ''; ?>" />
        </div>
        <div class="mb-3">
            <label for="Genere" class="form-label">Genere</label>
            <input type="text" class="form-control" name="genere" id="genere" value="<?php echo $genere['genere'] ?? ''; ?>" />
        </div>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                <button type="submit" class="btn btn-primary">Inserisci</button>
    </form>
            </div>
           
        </div>
    </div>
</div>


    <!----------------------------------------------------------------------- Form per eliminare un record --------------------------------->
    <form method="POST">
        <div class="mb-3">
            <label for="id" class="form-label">ID da eliminare</label>
            <input type="text" class="form-control" name="id" id="id" />
        </div>
        <button type="submit" class="btn btn-danger">Elimina</button>
    </form>

    <!--------------------------------------------------- Form per la ricerca ------------------------------------------------------------------>
    <form method="GET">
        <div class="mb-3">
            <label for="search" class="form-label">Ricerca per nome</label>
            <input type="text" class="form-control" name="search" id="search" />
        </div>
        <button type="submit" class="btn btn-primary">Cerca</button>
    </form>

    <!---------------------------------------------------- Visualizzazione dei risultati della ricerca ----------------------------------------->
    <?php if (!empty($results)): ?>
        <div class="row">
            <?php foreach ($results as $result): ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= $result['titolo'] ?></h5>
                            <? $result['immagine'] ?>
                            <p class="card-text">ID: <?= $result['id'] ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!---------------------------------------------------------------- Visualizzazione dei record paginati ----------------------------------->
    <h2 class="mt-5">Tutti i record nel database:</h2>
    <div class="row">
    <?php foreach ($paginated_results as $libri): ?>
        <div class="col-md-4">
            <div class="card h-100"> 
                <div class="card-body d-flex flex-column"> 
                    <h5 class="card-title"><?= $libri['titolo'] ?></h5>
                    <h6 class="card-title"><?= $libri['autore'] ?></h6>
                    <p class="card-text">ID: <?= $libri['id'] ?></p>
                    <img src="<?= $libri['immagine'] ?>" alt="Immagine libro" class="img-fluid">
                    <p class="card-text">Anno pubblicazione: <?= $libri['anno_pubblicazione'] ?></p>
                    <p class="card-text">Genere: <?= $libri['genere'] ?></p>
                    <a href="modifica.php?id=<?php echo $libri['id']; ?>" class="mt-auto btn btn-primary">Modifica</a> 
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>


    <!-------------------------------------------------------------- Pulsanti di navigazione ------------------------------------------------>
    <div class="mt-3">
        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary <?= $page <= 1 ? 'disabled' : '' ?>">Previous</a>
        <a href="?page=<?= $page + 1 ?>" class="btn btn-primary <?= count($paginated_results) < $limit ? 'disabled' : '' ?>">Next</a>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>