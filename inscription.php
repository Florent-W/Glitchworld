<?php
include('Header.php');
?>

<body>
    <div class="container">
        <div class="row">
            <form class="form" method="post" enctype="multipart/form-data" style="margin:50px">
                <h1>Inscription</h1>
                <hr> <!-- Trait -->
                <div class="form-group">
                    <label for="nom">Nom</label>
                    <input type="text" name="nom" id="nom" required value="<?php if (!empty($_POST['nom'])) echo $_POST['nom'] ?>" onchange="controleTexteInput(this, 'pseudoIndication', 'pseudo')" class="form-control"> <!-- On conserve les valeurs au cas où il y a une erreur dans l'envoi -->
                    <label id="pseudoIndication" class="text-danger"><?php if (isset($_POST['nom']) and empty($_POST['nom'])) echo "Veuillez choisir un pseudo" ?></label> <!-- Indication pseudo, il sera indiqué si le texte n'a pas de caractère ou le formulaire a déjà été soumis mais qu'il y a une erreur -->
                </div>
                <div class="form-group">
                    <label for="mdp">Mot de passe</label>
                    <input type="password" name="mdp" id="mdp" required value="<?php if (!empty($_POST['mdp'])) echo $_POST['mdp'] ?>" onchange="controleTexteInput(this, 'mdpIndication', 'mdp')" class="form-control">
                    <label id="mdpIndication" class="text-danger"><?php if (isset($_POST['mdp']) and empty($_POST['mdp'])) echo "Veuillez choisir un mot de passe" ?></label> <!-- Indication mot de passe, il sera indiqué si le texte n'a pas de caractère ou le formulaire a déjà été soumis mais qu'il y a une erreur -->
                </div>
                <div class="form-group">
                    <label for="mail">Adresse e-mail</label>
                    <input type="text" name="mail" id="mail" required value="<?php if (!empty($_POST['mail'])) echo $_POST['mail'] ?>" onchange="controleTexteInput(this, 'mailIndication', 'mail')" class="form-control">
                    <label id="mailIndication" class="text-danger"><?php if (isset($_POST['mailIndication']) and empty($_POST['mailIndication'])) echo "Veuillez choisir un e-mail" ?></label> <!-- Indication e-mail, il sera indiqué si le texte n'a pas de caractère ou le formulaire a déjà été soumis mais qu'il y a une erreur -->
                </div>
                <div class="form-group">
                    <label for="photo_profil">Photo de profil</label>
                    <div class="input-group">
                        <!-- Upload de photo de profil -->
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                        </div>
                        <div class="custom-file">
                            <input type="file" required required class="custom-file-input" name="photo_profil" id="inputGroupFile01" onchange="controleTexteInput(this, 'miniatureIndication', 'miniature')" aria-describedby="inputGroupFileAddon01"> <!-- Si un fichier a été choisi, l'événement onchange permettra de montrer le nom du fichier sur le label d'information -->
                            <label id="miniatureIndication" class="custom-file-label" for="inputGroupFile01">Choisir fichier</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Envoyer</button>
            </form>
        </div>
    </div>

    <?php
    if (!empty($_POST['nom']) and !empty($_POST['mdp']) and !empty($_POST['mail']) and !empty($_FILES['photo_profil']['tmp_name'])) {
        $pseudo = $_POST['nom'];
        $mdp = password_hash($_POST['mdp'], PASSWORD_DEFAULT); // Hash du mot de passe
        $mail = $_POST['mail'];
        $statut = "Membre";
        $nom_photo_profil_avant_conversion = $_FILES['photo_profil']['name']; // Pour obtenir le nom final du fichier image, on utilise l'id du membre ainsi que l'extension de l'image
        $extension_photo_profil = pathinfo($nom_photo_profil_avant_conversion, PATHINFO_EXTENSION);
    ?>

        <?php
        $reponse = $bdd->prepare('SELECT COUNT(pseudo) as NbPseudo FROM utilisateurs WHERE pseudo = :pseudo'); // On cherche le nombre de pseudo sous le meme nom pour voir si il est déjà pris
        $reponse->execute(array('pseudo' => $pseudo));
        $donnees = $reponse->fetch();
        $reponse->closeCursor();

        if ($donnees['NbPseudo'] == 0) { // Si le pseudo est disponible
            $reponse = $bdd->prepare('INSERT INTO utilisateurs (pseudo, mdp, mail, statut) VALUES (:pseudo, :mdp, :mail, :statut)'); // Insertion utilisateur
            $reponse->execute(array('pseudo' =>  $pseudo, 'mdp' => $mdp, 'mail' => $mail, 'statut' => $statut));

            $idUtilisateur = $bdd->lastInsertId();

            $reponse = $bdd->prepare('UPDATE utilisateurs SET nom_photo_profil = :nom_photo_profil WHERE id = :idUtilisateur'); // Mise à jour de l'utilisateur avec nom de l'image de profil
            $reponse->execute(array('nom_photo_profil' => $idUtilisateur . '.' . $extension_photo_profil, 'idUtilisateur' => $idUtilisateur));

            // Redimensionnement de l'image
            $tailleImage = getimagesize($_FILES['photo_profil']['tmp_name']); // Récupération taille de l'image uploadée
            $largeur = $tailleImage[0];
            $hauteur = $tailleImage[1];
            $largeur_miniature = 300; // Largeur de la future image
            $hauteur_miniature = $hauteur / $largeur * 300;

            if ($extension_photo_profil == 'jpg') {
                $im = imagecreatefromjpeg($_FILES['photo_profil']['tmp_name']); // Stockage de la photo qui vient d'être uploadée
                $im_miniature = imagecreatetruecolor($largeur_miniature, $hauteur_miniature); // Création de la miniature avec une couleur de 24 bits avec une hauteur proportionnelle à celle d'origine
                imagecopyresampled($im_miniature, $im, 0, 0, 0, 0, $largeur_miniature, $hauteur_miniature, $largeur, $hauteur); // Copie de l'image d'origine dans la miniature et redimensionnement
                imagejpeg($im_miniature, 'photo_profil/' . $idUtilisateur . '.' . $extension_photo_profil, 100); // Création de l'image jpg dans le dossier photo
            }
            else if ($extension_photo_profil == 'png') {
                $im = imagecreatefrompng($_FILES['photo_profil']['tmp_name']); // Stockage de la photo qui vient d'être uploadée
                $im_miniature = imagecreatetruecolor($largeur_miniature, $hauteur_miniature); // Création de la miniature avec une couleur de 24 bits avec une hauteur proportionnelle à celle d'origine
                imagecopyresampled($im_miniature, $im, 0, 0, 0, 0, $largeur_miniature, $hauteur_miniature, $largeur, $hauteur); // Copie de l'image d'origine dans la miniature et redimensionnement
                imagepng($im_miniature, 'photo_profil/' . $idUtilisateur . '.' . $extension_photo_profil, 1); // Création de l'image png dans le dossier photo
            } else if ($extension_photo_profil == 'bmp') {
                $im = imagecreatefrombmp($_FILES['photo_profil']['tmp_name']); // Stockage de la photo qui vient d'être uploadée
                $im_miniature = imagecreatetruecolor($largeur_miniature, $hauteur_miniature); // Création de la miniature avec une couleur de 24 bits avec une hauteur proportionnelle à celle d'origine
                imagecopyresampled($im_miniature, $im, 0, 0, 0, 0, $largeur_miniature, $hauteur_miniature, $largeur, $hauteur); // Copie de l'image d'origine dans la miniature et redimensionnement
                imagebmp($im_miniature, 'photo_profil/' . $idUtilisateur . '.' . $extension_photo_profil, 100); // Création de l'image bmp dans le dossier photo
            }
        
            $_SESSION['pseudo'] = $pseudo; // Variable de session, connexion
            $_SESSION['id'] = $idUtilisateur;
            $_SESSION['statut'] = $statut;
        ?>
            <script>
                document.location.href = '/index.php'; // Redirection nouvelle url
            </script> 
        <?php
            // mail($mail, "Bienvenue à Glitchworld !", "Bienvenue à Glitchworld !");
        } else {
        ?> <div>Le pseudo à déjà été pris. Veuillez en choisir un autre. </div><?php
                                                                            }
                                                                        }
                                                                                ?>

</body>

</html>