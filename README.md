# Calculatrice Télécoms

Instructions rapides pour tester localement (Windows + XAMPP)

1. Placez le dossier `Calculatrice-Télécoms` dans `C:\xampp\htdocs` (déjà en place).
2. Démarrez Apache via le panneau de contrôle XAMPP.
3. Ouvrez votre navigateur et allez sur:

   http://localhost/Calculatrice-Télécoms/index.php

4. Utilisation:
   - Tapez un numéro (1..8) ou une commande (`gain`, `directivite`, `efficacite`, `attenuation`, `capacite`, `budget_optique`, `help`, `clear`).
   - Suivez les questions qui s'affichent dans la zone de sortie.
   - Le bouton en haut à droite permet de basculer le thème clair/sombre.

5. Tester l'API avec `curl` (exemples):

```bash
curl -X POST -d "action=choose&command=help" http://localhost/Calculatrice-Telecoms/api.php
```

6. Fichiers importants:
   - `index.php` : interface utilisateur et JS
   - `api.php` : logique serveur et calculs
   - `style.css` : styles (thème sombre par défaut). Le thème clair est activé via l'attribut `data-theme="light"`.

7. Dépannage rapide:
   - Si l'interface est blanche ou erreur 500, regardez les logs Apache (`C:\xampp\apache\logs\error.log`).
   - Assurez-vous que `api.php` est accessible et que `fetch` retourne JSON.

Si vous voulez, je peux:
- Ajouter un petit loader lors des requêtes réseau.
- Externaliser davantage le JS dans `app.js`.
- Ajouter une validation côté client pour les entrées numériques.
