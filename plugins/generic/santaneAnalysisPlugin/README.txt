# Plugin Santane AI Analysis pour OJS

Un plugin pour Open Journal Systems (OJS) qui fournit une analyse automatisée des résumés d'articles pour les utilisateurs premium.

## Fonctionnalités

- **Accès basé sur les rôles** : Seuls les utilisateurs avec le rôle Premium peuvent accéder à la fonctionnalité d'analyse
- **Analyse de résumé** : Analyse les résumés pour le nombre de mots, le nombre de phrases, les mots-clés et le score de clarté
- **Recommandations intelligentes** : Fournit des suggestions personnalisées pour améliorer la qualité du résumé
- **Interface AJAX** : Expérience utilisateur fluide avec des requêtes asynchrones

## Installation

1. Placez le dossier `santaneAnalysisPlugin` dans votre répertoire `plugins/generic/` d'OJS
2. Allez dans Tableau de bord d'administration OJS > Paramètres > Plugins
3. Trouvez "Santane AI Analysis" dans la liste et activez-le
4. Assurez-vous que les utilisateurs premium ont l'attribut `isPremium` défini dans leur profil

## Utilisation

1. Naviguez vers le formulaire de soumission d'article (Étape 3 : Entrer les métadonnées)
2. Si vous avez un rôle Premium, vous verrez un bouton "Run Santane AI Analysis" sous le champ de résumé
3. Cliquez sur le bouton pour analyser votre résumé
4. Consultez les résultats et recommandations affichés sous le bouton

## Détails techniques

### Hooks utilisés
- `TemplateManager::display` : Modifie le template du formulaire de soumission
- `LoadHandler` : Enregistre le point de terminaison API personnalisé

### Point de terminaison API
- URL : `/index.php/index/santane/analyze`
- Méthode : POST
- Paramètres : `abstract` (contenu textuel)
- Réponse : JSON avec les résultats d'analyse

### Structure des fichiers