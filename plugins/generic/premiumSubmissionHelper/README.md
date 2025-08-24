# Premium Submission Helper (squelette)

Ce dossier contient un squelette de plugin OJS (générique) qui :
- Injecte un bouton **Analyser avec IA (Premium)** dans le formulaire de soumission (sous le champ Résumé).
- Expose un endpoint API `POST /api/v1/premium-submission-helper/analyze` qui retourne un score simulé.

## Installation (dev)
1. Copier `plugins/generic/premiumSubmissionHelper` dans votre dépôt OJS local.
2. `git add` + `commit` + `push` sur votre branche `feat/premium-helper-plugin-<votre_nom>`.
3. Activer le plugin dans l’interface d’administration d’OJS.
4. Tester la soumission d’un manuscrit et utiliser le bouton d’analyse.

> Remarque : le code est un squelette à adapter selon votre version exacte d’OJS/PKP (variations possibles de hooks et de templates).
