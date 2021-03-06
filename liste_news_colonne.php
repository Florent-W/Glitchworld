<?php
if ($_GET["onglet_jeu"] == "news") { // Va servir à dire si c'est une news ou autre, et va mettre les variables pour les différents textes
    $nomCategorie = "News";
    $nomCategorieTexteMajuscule = "News";
    $nomCategorieTexteMinuscule = "news";
    $nombreArticleParPage = $nombreNewsParPage;
}
else if ($_GET["onglet_jeu"] == "glitchs") {
    $nomCategorie = "Glitchs";
    $nomCategorieTexteMajuscule = "Glitchs";
    $nomCategorieTexteMinuscule = "glitch";
    $nombreArticleParPage = $nombreGlitchParPage;
}
else if ($_GET["onglet_jeu"] == "mods") {
    $nomCategorie = "Mods";
    $nomCategorieTexteMajuscule = "Mods";
    $nomCategorieTexteMinuscule = "mods";
    $nombreArticleParPage = $nombreModsParPage;
}
else if ($_GET["onglet_jeu"] == "tutoriels") {
    $nomCategorie = "Tutoriels";
    $nomCategorieTexteMajuscule = "Tutoriels";
    $nomCategorieTexteMinuscule = "Tutoriels";
    $nombreArticleParPage = $nombreTutorielsParPage;
}
$offsetPageArticle = $nombreArticleParPage * ($pageSelectionner - 1); // Offset pour dire quand on commence à prendre les News
?>

<h2 style="margin-bottom: 20px;" class="d-flex justify-content-center">Liste des <?php echo $nomCategorieTexteMajuscule; ?> de <?php echo $nom_jeu; ?> :</h2>
<ul class="list-group" style="margin-bottom: 20px; top:100px">

    <?php
    $reponse = $bdd->prepare('SELECT COUNT(*) as nb_article FROM article_lier_jeu INNER JOIN jeu ON article_lier_jeu.id_jeu = jeu.id INNER JOIN article ON article_lier_jeu.id_article = article.id WHERE article.nom_categorie = :nom_categorie AND jeu.nom = :nom_jeu AND article.approuver = "Approuver" AND article.titre LIKE :article'); // Nombre de News trouvés, si aucun, on n'affichera pas
    $reponse->bindValue('nom_categorie', $nomCategorie, PDO::PARAM_STR);
     $reponse->bindValue('nom_jeu', $nom_jeu, PDO::PARAM_STR);
    $reponse->bindValue('article', '%' . $rechercheArticle . '%', PDO::PARAM_STR);
    $reponse->execute();
    $donnees = $reponse->fetch();
    $nbArticleTrouver = $donnees['nb_article'];
    $reponse->closeCursor();

    $reponse = $bdd->prepare('SELECT article.id, article.titre, article.url, article.nom_miniature, article.contenu, DATE_FORMAT(date_creation, "%d %M %Y à %Hh%imin%ss") AS date_article, DATE_FORMAT(date_creation, "%Y/%M/%d/%kh%i") AS date_article_dossier FROM article_lier_jeu INNER JOIN jeu ON article_lier_jeu.id_jeu = jeu.id INNER JOIN article ON article_lier_jeu.id_article = article.id WHERE article.nom_categorie = :nom_categorie AND jeu.nom = :nom_jeu AND article.approuver = "Approuver" AND article.titre LIKE :article ORDER BY article.id DESC LIMIT :nombreArticleParPage OFFSET :offsetPageArticle'); // Sélection des Newss et formatage de la date à partir de la page de jeu selectionnée
    $reponse->bindValue('nom_categorie', $nomCategorie, PDO::PARAM_STR);
    $reponse->bindValue('nom_jeu', $nom_jeu, PDO::PARAM_STR);
    $reponse->bindValue('article', '%' . $rechercheArticle . '%', PDO::PARAM_STR);
    $reponse->bindValue('nombreArticleParPage', $nombreArticleParPage, PDO::PARAM_INT);
    $reponse->bindValue('offsetPageArticle', $offsetPageArticle, PDO::PARAM_INT);
    $reponse->execute();

    // Si les News sont trouvés, on les affiche
    if ($nbArticleTrouver > 0) {

        $positionArticle = 0; // On va voir la place de la news et une fois sur deux, elle sera en couleur
        while ($donnees = $reponse->fetch()) {
    ?>
            <!-- Liste News -->
            <?php if ($positionArticle % 2 == 0) { // Un News sur deux sera en couleur
            ?>
                <a href="/news/<?php echo htmlspecialchars($donnees['url']); ?>-<?php echo htmlspecialchars($donnees['id']); ?>" style="text-decoration-color: black; text-decoration: none;" class="list-group-item justify-content-center list-group-item-secondary liste-item-sans-bordure">
                    <!-- L'url est composé à l'aide de l'url rewriting, de l'url marqué dans la base de données ainsi que de l'id -->
                <?php } else { ?>
                    <a href="/news/<?php echo htmlspecialchars($donnees['url']); ?>-<?php echo htmlspecialchars($donnees['id']); ?>" style="text-decoration-color: black; text-decoration: none;" class="list-group-item justify-content-center list-group-item-light liste-item-sans-bordure">
                    <?php
                } ?>
                    <img src="/Articles/<?php echo $donnees['date_article_dossier']; ?>/<?php echo $donnees['url']; ?>/miniature/<?php echo $donnees['nom_miniature']; ?>" onerror="this.oneerror=null; this.src='/1.jpg';" class="img-fluid img-news img-thumbnail" style="float:left; height: 200px; background-color:transparent;"> <!-- Image à gauche et si image non trouvée, elle est remplacée par une image par défaut, titre à droite -->
                    <div class="row">
                        <div class="col">
                            <h1 class="list-group-item-heading text-body"><?php echo $donnees['titre']; ?></h1> <!-- Nom du News -->
                        </div>
                        <div class="col">
                            <p class="list-group-item-text pull-right text-right lead"><?php echo $donnees['date_article']; ?></p> <!-- Date de l'écriture de l'article -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <p class="list-group-item-text pull-right lead" style="word-wrap: break-word"><?php echo nl2br(tronquerTexte(remplacementBBCode($donnees['contenu'], false, true), 150, "news/" . $donnees['url'] . "-" . $donnees['id'])); ?></p> <!-- Contenu -->
                        </div>
                    </div>
                    </a>
                    <?php
                    if (isset($_SESSION['pseudo']) && $_SESSION['statut'] == "Administrateur") { // Si le statut de l'utilisateur est administrateur, on lui autorise à modifier l'article 
                    ?>
                        <a href="/modifier_news/<?php echo $donnees['url']; ?>-<?php echo $donnees['id']; ?>">
                            <p class="list-group-item-text pull-right text-right lead">Modifier</p> <!-- Modification de la page du News -->
                        </a>
                <?php }
                    $positionArticle++; // On augmente la position du News vu qu'on change de News
                }
            } else { // Si aucun résultat n'a été trouvé, un message d'erreur est affiché 
                ?>
                <?php
                if ($_GET['onglet_jeu'] == "news") { ?>
                    <p class="text-center">Aucune news n'a été trouvée.</p>
                <?php } else if ($_GET['onglet_jeu'] == "glitch") { ?>
                    <p class="text-center">Aucun glitch n'a été trouvé.</p>
                <?php } else if ($_GET['onglet_jeu'] == "mods") { ?>
                    <p class="text-center">Aucun mods n'a été trouvé.</p>
                <?php } ?>

            <?php
            }
            $reponse->closeCursor();
            ?>
</ul>

<?php
// $nbArticleTrouver = $nbNewsTrouver; // On indique combien d'article il y a
// $nombreArticleParPage = $nombreNewsParPage;
include('pagination_article_jeu.php');
?>