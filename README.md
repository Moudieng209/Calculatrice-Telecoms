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

<img width="1359" height="956" alt="image" src="https://github.com/user-attachments/assets/99184f11-17ec-4794-b66b-482749d64c57" />
<img width="1326" height="956" alt="image" src="https://github.com/user-attachments/assets/6411938a-5419-41d4-ac03-b11f954e4bc3" />
<img width="1340" height="932" alt="image" src="https://github.com/user-attachments/assets/ef301e90-8b29-4331-accf-4692837c100b" />
<img width="1281" height="935" alt="image" src="https://github.com/user-attachments/assets/33dd1e2b-d278-441a-befb-84f3d0623c34" />
<img width="1360" height="920" alt="image" src="https://github.com/user-attachments/assets/3f6df596-e5a1-472d-952d-239d19f3eb77" />
