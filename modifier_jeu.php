<?php
session_start();
$id_jeu = $_GET['id']; // Recupération de l'id du jeu à modifier
$url = $_GET['url'];

include('connexion_base_donnee.php');
$reponse = $bdd->prepare('SELECT jeu.*, categorie_jeu.nom AS nom_categorie FROM jeu LEFT JOIN categorie_jeu ON jeu.id_categorie = categorie_jeu.id WHERE jeu.id = :id'); // Sélection du jeu à modifier
$reponse->execute(array('id' => $id_jeu));
$donnees = $reponse->fetch(); /*
if ($donnees['url'] != $_GET['url']) { // Si l'url qu'on vient d'entrer n'est pas égal à l'url de l'id du jeu de la base de données, on redirige vers la bonne page
    header("location:/modifier_jeu/" . $donnees['url'] . "-" . $donnees['id']);
}
*/
$reponse->closeCursor();
?>

<?php
$title = "Modifier jeu";
include('Header.php');

$dateArticle = ""; // Récupération de la date pour savoir dans quel dossier mettre les images
?>

<body style="<?php if(!isset($_SESSION['nom_image_background'])) { echo "background-image: url('/background.jpg');"; } else { echo "background-image: url('/utilisateurs/" . $_SESSION['id'] . "/background_site/" . $_SESSION['nom_image_background'] . "');"; } ?> background-repeat: no-repeat; background-attachment: fixed; background-size: cover; background-position: center center; overflow-x: hidden;">
    <div class="container container-bordure animation fadeRight bg-white">
        <div class="row">
            <form class="form" id="modifier_jeu" method="post" enctype="multipart/form-data" style="margin:50px">
                <h1>Modifier jeu</h1>
                <hr> <!-- Trait -->
                <?php if (isset($_SESSION['pseudo'])) { // Si l'utilisateur est connecter, il peut modifier un jeu
                ?>
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" name="nom" id="nom" required value="<?php if (!isset($_POST['nom'])) echo $donnees['nom'];
                                                                                else echo $_POST['nom']; ?>" onchange="controleTexteInput(this, 'titreIndication', 'titre')" class="form-control"> <!-- Titre déjà pré-rempli avec les informations de la news et si on tente de modifier le titre, le titre est modifié -->
                        <label id="titreIndication" class="text-danger"><?php if (isset($_POST['nom']) and empty($_POST['nom'])) echo "Veuillez choisir un nom" ?></label> <!-- Indication nom, il sera indiqué si le texte n'a pas de caractère ou le formulaire a déjà été soumis mais qu'il y a une erreur -->
                    </div>
                    <div class="form-group">
                        <!-- Description -->
                        <label for="description">Description de 150 caractères max (non obligatoire)</label>
                        <input type="text" maxlenght="150" name="description" id="description" value="<?php if (!empty($_POST['description'])) echo $_POST['description'];
                                                                                                        else if (isset($donnees['description'])) echo $donnees['description']; ?>" class="form-control"> <!-- On conserve les valeurs au cas où il y a une erreur dans l'envoi -->
                    </div>
                    <div class="form-group">
                        <label for="contenu">Contenu</label>
                        <div class="row" style="margin-bottom:10px;">
                            <div class="col">
                                <script>
                                    var nom_contenu = 'contenu';
                                </script>
                                <?php
                                include('bouton_bb_code.php');
                                ?>

                            </div>
                        </div>
                        <textarea name="contenu" id="contenu" required oninput="previsualisationContenu()" onchange="controleTexteInput(this, 'contenuIndication', 'contenu')" class="form-control" rows="5"><?php if (!isset($_POST['contenu'])) echo $donnees['contenu'];
                                                                                                                                                                                                                else if (!empty($_POST['contenu'])) {
                                                                                                                                                                                                                    echo $_POST['contenu'];
                                                                                                                                                                                                                } ?> </textarea>
                        <hr>
                        <div name="previsualisationContenu" id="previsualisationContenu" style="white-space: pre-wrap;"></div>
                        <label id="contenuIndication" class="text-danger"><?php if (isset($_POST['contenu']) and empty($_POST['contenu'])) echo "Veuillez choisir un contenu" ?></label> <!-- Indication contenu, il sera indiqué si le texte n'a pas de caractère ou le formulaire a déjà été soumis mais qu'il y a une erreur -->
                    </div>
                    <div class="form-group">
                        <label for="nom">Date de sortie</label>
                        <input type="date" name="date_sortie" id="date_sortie" value="<?php if (!isset($_POST['date_sortie'])) echo $donnees['date_sortie'];
                                                                                        else echo $_POST['date_sortie']; ?>" required onchange="controleTexteInput(this, 'dateSortieIndication', 'date')" class="form-control"> <!-- On conserve les valeurs au cas où il y a une erreur dans l'envoi -->
                        <label id="dateSortieIndication" class="text-danger"><?php if (isset($_POST['date_sortie']) and empty($_POST['date_sortie'])) echo "Veuillez choisir une date" ?></label> <!-- Indication date de sortie, il sera indiqué si le texte n'a pas de caractère ou le formulaire a déjà été soumis mais qu'il y a une erreur -->
                    </div>

                    <div class="form-group">
                        <label for="categorie">Catégorie</label>
                        <select class="form-control" name="categorie" id="categorie" required onchange="controleTexteInput(this, 'categorieIndication', 'categorie')" class="form-control">
                            <!-- Selection catégorie de l'article -->
                            <?php
                            $reponse = $bdd->prepare('SELECT categorie_jeu.nom FROM categorie_jeu ORDER BY categorie_jeu.id');
                            $reponse->execute();
                            while ($donnees2 = $reponse->fetch()) { ?>
                                <option value="<?php echo $donnees2['nom']; ?>" <?php if (isset($donnees['nom_categorie']) and $donnees2['nom'] == $donnees['nom_categorie']) echo 'selected="selected"'; ?>><?php echo $donnees2['nom']; ?></option> <!-- Les différentes options du select -->
                            <?php }

                            $reponse->closeCursor(); ?>
                        </select>
                        <label id="categorieIndication" class="text-danger"><?php if (isset($_POST['categorie']) and empty($_POST['categorie'])) echo "Veuillez choisir une catégorie" ?></label> <!-- Indication categorie, il sera indiqué si le texte n'a pas de caractère ou le formulaire a déjà été soumis mais qu'il y a une erreur -->
                    </div>

                    <div class="form-group">
                        <!-- Type de présentation -->
                        <label for="presentation">Type de Présentation</label>
                        <select class="form-control" name="presentation" id="presentation" required onchange="controleTexteInput(this, 'presentationIndication', 'presentation')" class="form-control">
                            <!-- Selection presentation de l'article -->
                            <option value="conteneur" <?php if (isset($_POST['presentation']) && $_POST['presentation'] == "conteneur") {
                                                            echo 'selected="selected"';
                                                        } else if ($donnees['presentation'] == "conteneur") {
                                                            echo 'selected="selected"';
                                                        } ?>>Conteneur</option> <!-- Les différentes options du select -->
                            <option value="section" <?php if (isset($_POST['presentation']) && $_POST['presentation'] == "section") {
                                                        echo 'selected="selected"';
                                                    } else if ($donnees['presentation'] == "section") {
                                                        echo 'selected="selected"';
                                                    } ?>>Section</option> <!-- Les différentes options du select -->
                            <option value="normal" <?php if (isset($_POST['presentation']) && $_POST['presentation'] == "normal") {
                                                        echo 'selected="selected"';
                                                    } else if ($donnees['presentation'] == "normal") {
                                                        echo 'selected="selected"';
                                                    } ?>>Normal</option> <!-- Les différentes options du select -->
                        </select>
                        <label id="presentationIndication" class="text-danger"><?php if (isset($_POST['presentation']) and empty($_POST['presentation'])) echo "Veuillez choisir un type de présentation" ?></label> <!-- Indication presentation, il sera indiqué si le texte n'a pas de caractère ou le formulaire a déjà été soumis mais qu'il y a une erreur -->
                    </div>

                    <script>
                        autoCompletion("plateformes", "Plateformes");

                        var listeTags = tagsCreationArticles('liste_plateformes', 'lierPlateformes'); // On récupère les tags du jeu
                    </script>

                    <div class="form-group">
                    <!-- Console / Pc -->
                    <label for="plateformes">Plateformes (non obligatoire)</label>
                        <div name="lierPlateformes" id="lierPlateformes" style="position: relative; border : 1px solid;">
                            <?php
                            if (!empty($_POST['liste_plateformes'])) { // Servira si il y a une erreur, on split les plateformes et on les reprend
                                $liste_plateformes = $_POST['liste_plateformes'];
                                $liste_plateformes_splitter = explode(',', $liste_plateformes); // On split les differentes plateformes
                            }

                            ?>
                            <?php
                            $reponse2 = $bdd->prepare('SELECT plateformes.nom_plateforme FROM plateformes INNER JOIN jeu_lier_plateformes ON plateformes.id = jeu_lier_plateformes.id_plateforme WHERE jeu_lier_plateformes.id_jeu = :id_jeu'); // On cherche le nom des plateformes lié au jeu
                            $reponse2->execute(array('id_jeu' => $donnees['id']));
                            $i = 0; // Servira si il y a une erreur pour savoir quel numéro de tags cherché

                            while ($donnees2 = $reponse2->fetch()) {
                            ?> <span class='badge badge-info tag' style='margin-left: 5px;'><?php if (!empty($_POST['liste_plateformes'])) echo $liste_plateformes_splitter[$i];
                                                                                            else if (!empty($donnees2['nom_plateforme'])) echo $donnees2['nom_plateforme']; ?> <i class="far fa-window-close" onclick="$(this).parent().remove()" ;></i></span> <!-- On conserve les valeurs au cas où il y a une erreur dans l'envoi -->

                            <?php $i++;
                            }
                            $reponse2->closeCursor();
                            ?>
                            <input type="text" name="plateformes" id="plateformes" style="border: 0;" class="form-control" style="display: inline-block;">
                        </div>
                        <label id="plateformesIndication" class="text-danger"><?php if ((!empty($_POST['plateformes']) and ($_POST['plateformes'] != $donnees2['nom_plateforme']))) echo "La plateforme n'a pas été trouvé"; ?></label> <!-- Indication plateforme du jeu, il sera indiqué si le formulaire a déjà été soumis mais qu'il y a une erreur -->
                    </div>

                    <script>
                        autoCompletion("genres", "Genres");

                        var listeTags = tagsCreationArticles('liste_genres', 'lierGenres'); // On récupère les genres du jeu
                    </script>

                    <div class="form-group">
                    <!-- Genre, type de jeu -->
                    <label for="genres">Genres (non obligatoire)</label>
                        <div name="lierGenres" id="lierGenres" style="position: relative; border : 1px solid;">
                            <?php
                            if (!empty($_POST['liste_genres'])) { // Servira si il y a une erreur, on split les genres et on les reprend
                                $liste_genres = $_POST['liste_genres'];
                                $liste_genres_splitter = explode(',', $liste_genres); // On split les differentes genres
                            }

                            ?>
                            <?php
                            $reponse2 = $bdd->prepare('SELECT genres.genre FROM genres INNER JOIN jeu_lier_genres ON genres.id = jeu_lier_genres.id_genre WHERE jeu_lier_genres.id_jeu = :id_jeu'); // On cherche le nom des genres lié au jeu
                            $reponse2->execute(array('id_jeu' => $donnees['id']));
                            $i = 0; // Servira si il y a une erreur pour savoir quel numéro de tags cherché

                            while ($donnees2 = $reponse2->fetch()) {
                            ?> <span class='badge badge-info tag' style='margin-left: 5px;'><?php if (!empty($_POST['liste_genres'])) echo $liste_genres_splitter[$i];
                                                                                            else if (!empty($donnees2['genre'])) echo $donnees2['genre']; ?> <i class="far fa-window-close" onclick="$(this).parent().remove()" ;></i></span> <!-- On conserve les valeurs au cas où il y a une erreur dans l'envoi -->

                            <?php $i++;
                            }
                            $reponse2->closeCursor();
                            ?>
                            <input type="text" name="genres" id="genres" style="border: 0;" class="form-control" style="display: inline-block;">
                        </div>
                        <label id="genresIndication" class="text-danger"><?php if ((!empty($_POST['genres']) and ($_POST['genres'] != $donnees2['genres']))) echo "Le genre n'a pas été trouvé"; ?></label> <!-- Indication genre du jeu, il sera indiqué si le formulaire a déjà été soumis mais qu'il y a une erreur -->
                    </div>

                    <div class="form-group">
                        <label for="video_background">Vidéo en Arrière plan / URL Youtube (non obligatoire)</label>
                        <input type="text" name="video_background" id="video_background" value="<?php if (!empty($_POST['video_background'])) echo $_POST['video_background'];
                                                                                                else if (isset($donnees['video_background'])) echo $donnees['video_background']; ?>" class="form-control"> <!-- On conserve les valeurs au cas où il y a une erreur dans l'envoi -->
                    </div>

                    <div class="form-group">
                        <label for="bandeaux">Bannière (non obligatoire)</label> <!-- La bannière est placé au début des jeux -->
                        <div class="input-group">
                            <!-- Upload de bannière -->
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroupFileAddon02">Upload</span>
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" accept=".jpg, .png, .bmp, .gif" name="bandeaux" id="inputGroupFile02" onchange="controleTexteInput(this, 'banniereIndication', 'miniature')" aria-describedby="inputGroupFileAddon02"> <!-- Si un fichier a été choisi, l'événement onchange permettra de montrer le nom du fichier sur le label d'information -->
                                <label id="banniereIndication" class="custom-file-label" for="inputGroupFile02">Choisir fichier</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="miniature">Miniature</label>
                        <div class="input-group">
                            <!-- Upload de miniature -->
                            <div class="input-group-prepend">
                                <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                            </div>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" accept=".jpg, .png, .bmp, .gif" name="miniature" id="inputGroupFile01" onchange="controleTexteInput(this, 'miniatureIndication', 'miniature')" aria-describedby="inputGroupFileAddon01"> <!-- Si un fichier a été choisi, l'événement onchange permettra de montrer le nom du fichier sur le label d'information -->
                                <label id="miniatureIndication" class="custom-file-label" for="inputGroupFile01">Choisir fichier</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" id="btn_envoi" class="btn btn-success">Envoyer</button>
                    <hr>
                    <div class="form-group">
                        <button type="button" id="supprimerJeu" data-placement="top" class="btn btn-warning" title="Supprimer Jeu" data-toggle="modal" data-target="#modalConfirmationSupprimerJeu">
                            <div class="list-group-item-text pull-right text-right text-white">Supprimer</div> <!-- Bouton qui va ouvrir une page pour confirmer la suppr de l'article -->
                        </button>
                    </div>
                <?php } else if (!isset($_SESSION['pseudo'])) {
                ?><div class="alert alert-warning" role="alert" style="margin-top: 10px;">Veuillez vous <a href="/connexion.php">connecter</a> pour modifier un jeu.</div> <?php
                                                                                                                                                                        }    ?>

                <?php /*
                                <a href="modifier_news/<?php echo $donnees['url']; ?>-<?php echo $donnees['id']; ?>">
                                    <p class="list-group-item-text pull-right text-right lead">Modifier</p> <!-- Modification de la news -->
                                </a>
                                <a href="/gerer_article.php?url=<?php echo $donnees['url']; ?>&id=<?php echo $donnees['id']; ?>&action=supprimer_article">
                                    <p class="list-group-item-text pull-right text-right lead">Supprimer</p> <!-- Suppression de l'article -->
                                </a>
                                */
                ?>
                <!-- href="/gerer_jeu.php?id=<?php echo $donnees['id']; ?>&action=supprimer_jeu" -->
            </form>

        </div>
    </div>
</body>
<?php
$reponse->closeCursor();
?>

<?php include('confirmation_suppression_jeu.php'); ?>
<?php
include('footer.php');
?>
<?php
include('upload_image.php');
?>
<?php
include('ajout_url.php');
?>
<?php
include('ajout_tableau.php');
?>
<?php
include('ajout_section.php');
?>
<?php
include('ajout_video.php');
?>

<?php
if (!empty($_POST['liste_plateformes'])) { // On cherche pour voir si le jeu est lié à des plateformes, si oui on regarde si les plateformes entré correspond à une plateforme sinon on redemande de retaper la plateforme
    $plateforme_trouver = array();
    $id_plateforme_trouver = array();

    ?>
    <?php

    for ($i = 0; $i < count($liste_plateformes_splitter); $i++) { // On cherche pour chaque plateforme son id
        $reponse = $bdd->prepare('SELECT id FROM plateformes WHERE nom_plateforme = :nom');
        $reponse->execute(array('nom' => $liste_plateformes_splitter[$i]));
        $nombre_id_plateforme_trouver = $reponse->rowCount();
        array_push($id_plateforme_trouver, $nombre_id_plateforme_trouver);
        $donnees = $reponse->fetch();
        array_push($plateforme_trouver, $donnees['id']);
        $reponse->closeCursor();
?>
<?php
    }
}
?>
<?php
if (!empty($_POST['liste_genres'])) { // On cherche pour voir si le jeu est lié à des genres, si oui on regarde si les genres entré correspond à un genre sinon on redemande de retaper le genre
    $genre_trouver = array();
    $id_genre_trouver = array();

    ?>
    <?php

    for ($i = 0; $i < count($liste_genres_splitter); $i++) { // On cherche pour chaque genre son id
        $reponse = $bdd->prepare('SELECT id FROM genres WHERE genre = :nom');
        $reponse->execute(array('nom' => $liste_genres_splitter[$i]));
        $nombre_id_genre_trouver = $reponse->rowCount();
        array_push($id_genre_trouver, $nombre_id_genre_trouver);
        $donnees = $reponse->fetch();
        array_push($genre_trouver, $donnees['id']);
        $reponse->closeCursor();
?>
<?php
    }
}
?>

<?php
if (!empty($_POST['nom']) and !empty($_POST['contenu']) and !empty($_POST['date_sortie']) and !empty($_POST['categorie']) and (empty($_POST['plateformes'])) or (!empty($_POST['plateformes']) and count($id_plateforme_trouver) > 0) and (empty($_POST['genres'])) or (!empty($_POST['genres']) and count($id_genre_trouver) > 0) and !empty($_POST['presentation'])) { // Traitement
    $nom = $_POST['nom'];
    $url = EncodageTitreEnUrl($nom);
    $description = $_POST['description'];
    $contenu = $_POST['contenu'];
    $date_sortie = $_POST['date_sortie'];
    $nom_categorie = $_POST['categorie'];
    $presentation = $_POST['presentation'];
    $video_background = $_POST['video_background'];

    $reponse = $bdd->prepare('SELECT nom, jeu.url as jeu_url FROM jeu WHERE id = :id'); // Selection de l'ancien nom du jeu, servira à renommer le dossier du jeu si il est changé
    $reponse->execute(array('id' => $id_jeu));
    $donnees = $reponse->fetch();
    $reponse->closeCursor();

    if ($donnees['nom'] != $_POST['nom']) { // Si le nom à changé, on renomme
        rename("Jeux/" . $donnees['jeu_url'], "Jeux/" . $url);
    }

    $reponse = $bdd->prepare('SELECT id FROM categorie_jeu WHERE nom = :nom_categorie'); // Selection id catégorie du jeu à l'aide du nom pour l'insérer ensuite
    $reponse->execute(array('nom_categorie' => $nom_categorie));
    $donnees = $reponse->fetch();
    $id_categorie_jeu = $donnees['id'];
    $reponse->closeCursor();

    if (!empty($_FILES['bandeaux']['tmp_name'])) { // On regarde si une bannière à été ajoutée
        $nom_banniere = $_FILES['bandeaux']['name']; // Si il y a un nom, cela sera bien mis dans la base de donnees

        $tailleImage = getimagesize($_FILES['bandeaux']['tmp_name']); // Récupération taille de l'image uploadée
        $largeur = $tailleImage[0];
        $hauteur = $tailleImage[1];
        if ($largeur > 2500) { // On redimensionne
            redimensionImage($largeur, $hauteur, 2500, 2500);
            // $largeur_miniature = 1200; // Largeur de la future miniature
            // $hauteur_miniature = $hauteur / $largeur * 675;
        } else {
            $largeur_miniature = $largeur;
            $hauteur_miniature = $hauteur;
        }

        $type_image = 'bandeaux'; // Recupère le nom de l'image (formulaire) pour indiquer quel type de fichier on va récupérer, miniature, bien penser à mettre le nom du dossier pour le nom de l'input
        $parametre_upload_image = "modification"; // Dit si c'est une modification pour savoir si il faut créer un dossier pour l'image
        include('image_traitement.php');

        $reponse = $bdd->prepare('UPDATE jeu SET nom_banniere = :nom_banniere WHERE id = :id'); // Modification du jeu directement pour mettre la banniere car on ne sait pas si la banniere est là dans la requete suivante et ça créer moins de requete
        $reponse->execute(array('nom_banniere' => $nom_banniere, 'id' => $id_jeu));
    }

    if (!empty($_FILES['miniature']['tmp_name'])) { // On regarde si il y a une nouvelle miniature
        $nom_miniature = $_FILES['miniature']['name'];
        $tailleImage = getimagesize($_FILES['miniature']['tmp_name']); // Récupération taille de l'image uploadée
        $largeur = $tailleImage[0];
        $hauteur = $tailleImage[1];
        $largeur_miniature = 300; // Largeur de la future miniature
        $hauteur_miniature = $hauteur / $largeur * 300;

        $type_image = 'miniature'; // Recupère le nom de l'image (formulaire) pour indiquer quel type de fichier on va récupérer, miniature
        $parametre_upload_image = "modification"; // Dit si c'est une modification pour savoir si il faut créer un dossier pour l'image
        include('image_traitement.php');

        $reponse = $bdd->prepare('UPDATE jeu SET nom = :nom, contenu = :contenu, id_categorie = :id_categorie, presentation = :presentation, nom_miniature = :nom_miniature, date_sortie = :date_sortie, url = :url, video_background = :video_background, description = :description WHERE id = :id'); // Modification jeu
        $reponse->execute(array('nom' => $nom, 'contenu' => $contenu, 'id_categorie' => $id_categorie_jeu, 'presentation' => $presentation, 'nom_miniature' => $nom_miniature, 'date_sortie' => $date_sortie, 'url' => $url, 'id' => $id_jeu, 'video_background' => $video_background, 'description' => $description));
    } else { // Si il n'y a pas de nouvelle miniature
        $reponse = $bdd->prepare('UPDATE jeu SET nom = :nom, contenu = :contenu, id_categorie = :id_categorie, presentation = :presentation, date_sortie = :date_sortie, url = :url, video_background = :video_background, description = :description WHERE id = :id'); // Modification jeu
        $reponse->execute(array('nom' => $nom, 'contenu' => $contenu, 'id_categorie' => $id_categorie_jeu, 'presentation' => $presentation,  'date_sortie' => $date_sortie, 'url' => $url, 'id' => $id_jeu, 'video_background' => $video_background, 'description' => $description));
    }

    // Traitement plateformes des jeux
    $reponse = $bdd->prepare('DELETE FROM jeu_lier_plateformes WHERE id_jeu = :id_jeu'); // On supprime d'abord les plateformes liés puisqu'ils seront liés après
    $reponse->execute(array('id_jeu' => $id_jeu));

    for ($i = 0; $i < count($plateforme_trouver); $i++) { // Parcours des différents id des plateformes
        if ($id_plateforme_trouver[$i] == 1) {
            $reponse = $bdd->prepare('INSERT INTO jeu_lier_plateformes (id_jeu, id_plateforme) VALUES (:id_jeu, :id_plateforme) '); // Insertion de la liste des plateformes lié au jeu
            $reponse->execute(array('id_jeu' => $id_jeu, 'id_plateforme' => $plateforme_trouver[$i]));
        }
    }

        // Traitement genres des jeux
        $reponse = $bdd->prepare('DELETE FROM jeu_lier_genres WHERE id_jeu = :id_jeu'); // On supprime d'abord les genres liés puisqu'ils seront liés après
        $reponse->execute(array('id_jeu' => $id_jeu));
    
        for ($i = 0; $i < count($genre_trouver); $i++) { // Parcours des différents id des genres
            if ($id_genre_trouver[$i] == 1) {
                $reponse = $bdd->prepare('INSERT INTO jeu_lier_genres (id_jeu, id_genre) VALUES (:id_jeu, :id_genre) '); // Insertion de la liste des genres lié au jeu
                $reponse->execute(array('id_jeu' => $id_jeu, 'id_genre' => $genre_trouver[$i]));
 }
}

?>
    <script>
        <?php /* document.location.href = '/modifier_news/<?php echo $url; ?>-<?php echo $id; ?>'; // Redirection nouvelle url */ ?>
       document.location.href = '/jeu/<?php echo $url; ?>-<?php echo $id_jeu; ?>';
    </script>
<?php
    // header('Location: index.php'); // Redirection vers la page d'accueil

} else {
}
?>

</html>