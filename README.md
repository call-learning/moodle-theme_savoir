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

# Notes de développpement




TODO:
- Menu gauche utilisateur (maquette étudiant)
- Bloc pour liste de cours
- Page pour liste de cours
- Topic collapsible (à mettre à jour dans l'install)

A vérifier:
- Setting pour des couleurs dominantes (primary, secondary)
- Logo appartient au settings globaux
- Voir logo couleur et pixicon 
- Debrayage pour admin/superadmin
- Dashboard par utilisateur (commment affecter à différent groupes)