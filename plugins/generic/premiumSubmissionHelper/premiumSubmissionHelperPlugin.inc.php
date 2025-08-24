<?php
/**
 * @file plugins/generic/premiumSubmissionHelper/PremiumSubmissionHelperPlugin.inc.php
 *
 * Plugin générique "Premium Submission Helper"
 * - Injecte un bouton sur le formulaire de soumission pour lancer une analyse IA simulée.
 * - Expose un endpoint API POST /api/v1/premium-submission-helper/analyze
 */

require_once('lib/pkp/classes/plugins/GenericPlugin.inc.php');

class PremiumSubmissionHelperPlugin extends GenericPlugin
{
    /** @var bool */
    private $enabled = true;

    /**
     * Register plugin
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (!parent::register($category, $path)) {
            return false;
        }

        // Hooks d'injection UI
        HookRegistry::register('Templates::Submission::SubmissionMetadataForm::AdditionalMetadata', [$this, 'addHelperBlock']);
        HookRegistry::register('TemplateManager::display', [$this, 'maybeInjectAssets']);

        // Hook pour ajouter des routes API (Slim)
        HookRegistry::register('API::addRoutes', [$this, 'addRoutes']);

        return true;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.premiumSubmissionHelper.displayName', [], 'fr_FR') ?: 'Premium Submission Helper';
    }

    public function getDescription()
    {
        return __('plugins.generic.premiumSubmissionHelper.description', [], 'fr_FR') ?: 'Ajoute un bouton premium pour analyser le résumé via une simulation IA.';
    }

    /**
     * Injecte le bloc (template) dans le formulaire de soumission (si hook dispo)
     */
    public function addHelperBlock($hookName, $params)
    {
        $smarty = $params[1]; // TemplateManager
        $output =& $params[2];

        $template = $this->getTemplateResource('helper.tpl');
        $output .= $smarty->fetch($template);
        return false;
    }

    /**
     * Injecte l'asset JS sur les pages de soumission (fallback compatible)
     */
    public function maybeInjectAssets($hookName, $params)
    {
        $templateMgr = $params[0];
        $template = $params[1];

        // Heuristique : si on est sur une page de soumission ou d'édition d'article
        if (strpos($template, 'submission') !== false || strpos($template, 'article') !== false) {
            $request = Application::get()->getRequest();
            $dispatcher = $request->getDispatcher();
            $assetUrl = $dispatcher->url($request, ROUTE_PAGE, null, 'index', 'plugin', null, [
                'category' => 'generic',
                'plugin' => $this->getName(),
                'type' => 'js',
                'file' => 'premiumHelper.js',
            ]);
            $templateMgr->addHeader('premiumHelperJs', '<script src="'.$assetUrl.'"></script>');
        }
        return false;
    }

    /**
     * Sert les fichiers statiques du plugin
     */
    public function getManageLinkAction($request, $verb, $category, $plugin)
    {
        return null;
    }

    /**
     * Expose un endpoint API via Slim: POST /api/v1/premium-submission-helper/analyze
     */
    public function addRoutes($hookName, $args)
    {
        $app = $args[0]; // \Slim\App

        $plugin = $this; // pour use() ci-dessous
        $app->post('/api/v1/premium-submission-helper/analyze', function ($request, $response, $args) use ($plugin) {
            $container = $this->getContainer();
            $requestBody = $request->getParsedBody();

            $abstract = isset($requestBody['abstract']) ? trim($requestBody['abstract']) : '';

            if ($abstract === '') {
                return $response->withJson([
                    'status' => 'error',
                    'message' => 'Le champ "abstract" est requis.'
                ], 400);
            }

            // (MVP) Simulation IA: score heuristique simple basé sur la longueur et la diversité lexicale
            $score = $plugin->computeHeuristicScore($abstract);

            return $response->withJson([
                'status' => 'success',
                'score' => round($score, 2),
                'message' => 'Résumé analysé avec succès.'
            ], 200);
        });

        return false;
    }

    /**
     * Fonction heuristique simple pour le MVP
     */
    private function computeHeuristicScore($text)
    {
        $len = mb_strlen($text);
        $unique = count(array_unique(preg_split('/\s+/u', mb_strtolower($text))));
        if ($len === 0) return 0.0;
        $lexDiv = $unique / max(1, count(preg_split('/\s+/u', $text)));
        $score = min(1.0, 0.5 * ($len / 1200) + 0.5 * $lexDiv);
        return $score;
    }

    /**
     * Nom système du plugin (répertoire)
     */
    public function getName()
    {
        return 'premiumSubmissionHelper';
    }

    /**
     * Permet de servir les assets (JS/CSS) via l’action "plugin"
     */
    public function getPluginPath()
    {
        return basename($this->getPluginPath());
    }
}
