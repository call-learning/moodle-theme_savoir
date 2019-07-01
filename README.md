# Installation

## Vérifier les plugin dépendants

- TopColl (course format: https://github.com/gjb2048/moodle-format_topcoll)
pour la version 3.5 

## Changer les menus
Aller dans Site Administration / Apparence/ Themes/ Paramètres Themes (admin/settings.php?section=themesettings)
et changer custommenuitems en:

    Etre.ensam.eu|http://etre.ensam.eu/|Environnement de Travail et de Ressources Électroniques
    Lise.ensam.eu|http://Lise.ensam.eu/|Lise
    ICIFTech.ensam.eu|http://ICIFTech.ensam.eu/|ICIF Tech

# Changer la page de garde

 - Aller dans la page de garde du site en tant qu'Admin (https://savoir.ensam.eu/?redirect=0)
et changer la description du cours en "Bienvenu sur la plateforme...."
 - Ajouter un label avec l'adresse de support

# Changer la dashboard par défaut

Cela permet d'avoir la dashboard etudiant en place:

    php7.1 cli/setups.php  --name=setup_system_dashboard

Ensuite il faudra voir comment on met en place la dashboard enseignant et
admin (sur cohortes ou autre).
Il existe un outil pour le faire pour l'admin:

    php7.1 cli/setups.php  --name=setup_dashboard_blocks

# Changer les identifiants (idnumber) des cours "Guides"

- Pour le guide étudiant (cours 70) GUIDE_ETUDIANT
- Pour le guide étudiant (cours 74) GUIDE_ENSEIGNANT

Ils seront listés dans les préférences du thème à partir du moment ou
leur Course ID commence par "GUIDE_"

# Notes de développpement

TODO:
- Bloc pour liste de cours
- Page pour liste de cours
- Topic collapsible (à mettre à jour dans l'install)
- Gérer logo couleur + pixicons
- Script affectation global dashboard

A vérifier (fonctionnement):
- Setting pour des couleurs dominantes (primary, secondary)
- Logo appartient au settings globaux

BUG MINEURS à corriger:
- Petit scroll sur la page de garde (probablement dû au footer)