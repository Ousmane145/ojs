# Plan pour le Plugin Premium Helper

## Où sera le plugin
- **Nom** : premiumSubmissionHelper  
- **Dossier** : plugins/generic/premiumSubmissionHelper/

## Comment l'ajouter au formulaire
- J'utiliserai le hook : `Templates::Submission::SubmissionMetadataForm::AdditionalFields`

## API pour l'analyse
- **URL** : /premium-submission-helper/analyze-abstract
- **Méthode** : POST
- **Ce qu'on envoie** : le résumé et l'ID de la soumission
- **Ce qu'on reçoit** : un score de qualité et des suggestions

## Fonctionnement du JavaScript
- Ajouter un bouton "Analyser" dans le formulaire
- Quand on clique, envoyer le résumé au serveur
- Afficher les résultats sous forme de score et suggestions
- Uniquement pour les utilisateurs premium