# Installation

## Vérifier les plugin dépendants

- TopColl (course format: https://github.com/gjb2048/moodle-format_topcoll)
pour la version 3.5 (version 2018052303)
- Block block_savoir_mycourses (https://gitlab.com/Lmedavid/m_savoir_mycourses_block): nouveau bloc pour lister les cours. Est utilisé dans ce thème.

## Changer les menus
Ou ? Réglages du thème: Administration du site / Présentation / Thèmes / Réglages thème

Aller dans Administration du site / Présentation / Thèmes / Réglages thème (admin/settings.php?section=themesettings)
et changer Éléments du menu personnalisé (custommenuitems) en:

    Etre.ensam.eu|http://etre.ensam.eu/|Environnement de Travail et de Ressources Électroniques
    Lise.ensam.eu|http://Lise.ensam.eu/|Lise
    ICIFTech.ensam.eu|http://ICIFTech.ensam.eu/|ICIF Tech

# Changer la page de garde

Ou ? Page de garde, mode édition.

 - Aller dans la page de garde du site (<url>/?redirect=0) en tant qu'Admin (https://savoir.ensam.eu/?redirect=0)
et changer la description du cours en "Bienvenu sur la plateforme...."
 - Ajouter un label avec l'adresse de support
 - Ne pas trop rajouter d'information car la page doit rester quasiment vide

# Changer son tableau de bord par défaut
Ou ? Script CLI.

Cela permet d'avoir son tableau de bord étudiant en place:

    php cli/setups.php  --name=setup_system_dashboard

Ensuite il faudra voir comment on met en place son tableau de bord enseignant et admin (sur cohortes ou autre).
Il existe un outil pour le faire pour l'admin:

    php cli/setups.php  --name=setup_dashboard_blocks
    
Pour l'instant nous n'avons pas de script qui permet de changer les dashboard de tous les utilisateurs.
En attendant on peut paramétrer son tableau de bord pour faire des effets.

# Changer les identifiants (idnumber) des cours "Guides"

Ou ? Paramètres du thème: Administration du site / Présentation / Thèmes / Savoir Moodle Theme

Les guides sont repérés par leur Identifiant unique de cours. Ils seront listés dans les préférences du thème à partir du moment ou
leur Course ID commence par "GUIDE_"

- Pour le guide étudiant (cours 70) GUIDE_ETUDIANT
- Pour le guide étudiant (cours 74) GUIDE_ENSEIGNANT


# Sortir des menus du cours

Ou ? Paramètres du thème: Administration du site / Présentation / Thèmes / Savoir Moodle Theme

La barre d'outils de cours permet de 'sortir' des menus du menu déroulant d'édition de cours.
Pour cela aller dans les paramètres du thème et sélectionner le menu à "sortir".
Ce menu sera représenté sous forme de bouton en haut de la page d'édition d'un cours.
 
# Notes de développpement

TODO:
- Ajouter un paramètre pour changer le favicon du thème
- Script affectation global dashboard
- Gérer le logo en couleur dans la page de login

A vérifier (fonctionnement):
- Setting pour des couleurs dominantes (primary, secondary)



