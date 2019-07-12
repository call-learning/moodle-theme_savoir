# Installation

## Vérifier les plugin dépendants

- TopColl (course format: https://github.com/gjb2048/moodle-format_topcoll)
pour la version 3.5 (version 2018052303)
- Block block_savoir_mycourses (https://gitlab.com/Lmedavid/m_savoir_mycourses_block): nouveau bloc pour lister les cours. Est utilisé dans ce thème.

## Changer les menus
Où ? Réglages du thème: Administration du site / Présentation / Thèmes / Réglages thème

Aller dans Administration du site / Présentation / Thèmes / Réglages thème (admin/settings.php?section=themesettings)
et changer Éléments du menu personnalisé (custommenuitems) en:

    Etre.ensam.eu|http://etre.ensam.eu/|Environnement de Travail et de Ressources Électroniques
    Lise.ensam.eu|http://Lise.ensam.eu/|Lise
    ICIFTech.ensam.eu|http://ICIFTech.ensam.eu/|ICIF Tech

# Changer la page de garde

Où ? Page de garde, mode édition.

 - Aller dans la page de garde du site (<url>/?redirect=0) en tant qu'Admin (https://savoir.ensam.eu/?redirect=0)
et changer la description du cours en "Bienvenu sur la plateforme...."
 - Ajouter un label avec l'adresse de support
 - Ne pas trop rajouter d'information car la page doit rester quasiment vide

# Changer son tableau de bord par défaut
Où ? Script CLI.

Cela permet d'avoir son tableau de bord étudiant en place:

    php cli/setups.php  --name=setup_system_dashboard

Ensuite il faudra voir comment on met en place son tableau de bord enseignant et admin (sur cohortes ou autre).
Il existe un outil pour le faire pour l'admin:

    php cli/setups.php  --name=setup_dashboard_blocks
    
Pour l'instant nous n'avons pas de script qui permet de changer les dashboard de tous les utilisateurs.
En attendant on peut paramétrer son tableau de bord pour faire des effets.

# Changer les identifiants (idnumber) des cours "Guides"

Où ? Paramètres du thème: Administration du site / Présentation / Thèmes / Savoir Moodle Theme

Les guides sont repérés par leur Identifiant unique de cours. Ils seront listés dans les préférences du thème à partir du moment ou
leur Course ID commence par "GUIDE_"

- Pour le guide étudiant (cours 70) GUIDE_ETUDIANT
- Pour le guide étudiant (cours 74) GUIDE_ENSEIGNANT

# Enlever la messagerie

Où?  Administration du Site / Fonction Avancées

Pour cacher l'icône de message, il vaut mieux aller dans "Fonction Avancées" (admin/settings.php?section=optionalsubsystems) 
et décocher "Activer la messagerie"

 
# Sortir des menus du cours

Où ? Paramètres du thème: Administration du site / Présentation / Thèmes / Savoir Moodle Theme

La barre d'outils de cours permet de 'sortir' des menus du menu déroulant d'édition de cours.
Pour cela aller dans les paramètres du thème et sélectionner le menu à "sortir".
Ce menu sera représenté sous forme de bouton en haut de la page d'édition d'un cours.
 

# Changer les couleurs de bases

Où ? Paramètres du thème: Administration du site / Présentation / Thèmes / Savoir Moodle Theme

Dans le thème il est possible de choisir une couleur de base (primary/le violet ENSAM) et secondary (orange).
Il suffit juste de sélectionner la couleur voulue ou de la taper dans le champ texte.

# Changer le favicon et logo

Logo: Où ? Paramètres du thème: Administration du site / Présentation / Thèmes / Savoir Moodle Theme
Favicon: Où ? Paramètres du thème: Administration du site / Présentation / Thèmes / Savoir Moodle Theme

Dans le thème il est possible de choisir une couleur de base (primary/le violet ENSAM) et secondary (orange).
Il suffit juste de sélectionner la couleur voulue ou de la taper dans le champ texte.


# Afficher un message d'alerte sur la page de garde

Où ? Paramètres du thème: Administration du site / Présentation / Thèmes / Savoir Moodle Theme

Pour afficher un message d'alerte affiché sur la page de garde, il faut remplir le contenu du
message et ensuite cocher Montre/Cache le message sur la page de garde.
Le message apparaîtra sur la page de garde, en haut de la page.

# Le catalogue de cours

Le catalogue de cours récupère les informations sur la description du cours. Il faut donc
remplir le détail du cours avec les informations voulues. Le niveau de titre moyen (h4) est
stylé pour s'afficher en violet (couleur primaire du site). Le reste du style reste identique
à ce qui est entré dans l'éditeur html du détail de cours.

# Résumé de cours
Le résumé de cours est tiré en premier de la description du cours. Si celle-ci est vide, on récupère l'information
de la section 0. Lors du développement nous avons dû faire face à une incohérence de l'entrée des donnnées, parfois les
données de résumé sont dans la section 0, parfois dans la description de cours, parfois ni dans l'une ni dans l'autre.
 
A terme il faudra **uniformiser cela en entrant la description/résumé de cours dans la partie description de cours et non dans la section 0.**

# Catalogue de cours "libres"

Les cours en libre service/entrée sont selectionné à partir des plugin d'inscription affectés au cours.
Il faut que l'accès anonyme (guest) soit ajouté à la liste des accès possible au cours.

Les cours sont classé par catégorie (leur catégorie immédiatement parente), et les catégories
par ordre alphabétiques. On peut rendre visible/invisible une catégorie pour que les cours ne s'affichent
pas.


# Notes de développpement

TODO:

- Script affectation global dashboard
- Gérer le logo en couleur dans la page de login
- Menu fixe
- Bloc spécifique enseignant
- (x) Ajouter la gestion du message d'alerte sur la page de garde
- (x) Ajouter un paramètre pour changer le favicon du thème





