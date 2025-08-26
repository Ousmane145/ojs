<?php

/**
 * @file PremiumSubmissionHelper.inc.php
 *
 * Copyright (c) 2023 Santane Analysis
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class PremiumSubmissionHelper
 * @brief Main class for Santane Analysis plugin.
 */

namespace APP\plugins\generic\PremiumSubmissionHelper;

use PKP\Application;
use PKP\plugins\HookRegistry;
use PKP\plugins\GenericPlugin;

class PremiumSubmissionHelper extends GenericPlugin
{
    /**
     * @copydoc Plugin::register()
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if ($success && $this->getEnabled()) {
            HookRegistry::register('TemplateManager::display', array($this, 'handleTemplateDisplay'));
            HookRegistry::register('LoadHandler', array($this, 'handleLoadHandler'));
        }
        return $success;
    }

    /**
     * Hook to display template and add JavaScript
     */
    public function handleTemplateDisplay($hookName, $args)
    {
        $templateMgr = $args[0];
        $template = $args[1];

        // Only modify the submission form template
        if ($template !== 'author/submit/submitStep3.tpl') {
            return false;
        }

        // Check if user has Premium role
        $user = Application::get()->getRequest()->getUser();
        if (!$user) {
            return false;
        }

        // For testing, let's consider all authors as premium
        // In a real scenario, you would check a custom user field
        $userRoles = $user->getRoles();
        $isPremium = false;
        foreach ($userRoles as $role) {
            if ($role->getRoleId() === ROLE_ID_AUTHOR) {
                $isPremium = true;
                break;
            }
        }

        if ($isPremium) {
            // Add custom JavaScript
            $templateMgr->addJavaScript(
                'santaneAnalysis',
                $this->getPluginPath() . '/js/santaneAnalysis.js',
                array('contexts' => 'backend')
            );

            // Add custom CSS
            $templateMgr->addStyleSheet(
                'santaneAnalysis',
                $this->getPluginPath() . '/css/santaneAnalysis.css',
                array('contexts' => 'backend')
            );

            // Add the button and results container
            $templateMgr->assign('showSantaneAnalysis', true);
        }

        return false;
    }

    /**
     * Handle custom API endpoint
     */
    public function handleLoadHandler($hookName, $args)
    {
        $page = $args[0];
        $op = $args[1];
        $sourceFile = $args[2];

        if ($page === 'santane' && $op === 'analyze') {
            $this->handleAnalysisRequest();
            exit;
        }

        return false;
    }

    /**
     * Process analysis request
     */
    private function handleAnalysisRequest()
    {
        header('Content-Type: application/json');

        // Check if it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Invalid request method']);
            exit;
        }

        // Get and validate the abstract text
        $request = Application::get()->getRequest();
        $abstract = $request->getUserVar('abstract');

        if (empty($abstract)) {
            echo json_encode(['error' => 'Abstract text is required']);
            exit;
        }

        // Perform simulated analysis
        $analysis = $this->analyzeAbstract($abstract);

        // Return analysis results
        echo json_encode($analysis);
        exit;
    }

    /**
     * Simulate abstract analysis
     */
    private function analyzeAbstract($abstract)
    {
        // Count words
        $wordCount = str_word_count(strip_tags($abstract));

        // Count sentences
        $sentenceCount = preg_match_all(
            '/[^\s](\.|\!|\?)(?=\s|$)/',
            $abstract,
            $matches
        );

        // Check for keywords (simulated)
        $keywords = [
            'method',
            'result',
            'conclusion',
            'study',
            'research',
            'data',
            'analysis'
        ];

        $foundKeywords = [];
        foreach ($keywords as $keyword) {
            if (stripos($abstract, $keyword) !== false) {
                $foundKeywords[] = $keyword;
            }
        }

        // Generate random clarity score (50-100)
        $clarityScore = rand(50, 100);

        // Return analysis results
        return [
            'wordCount' => $wordCount,
            'sentenceCount' => $sentenceCount,
            'foundKeywords' => $foundKeywords,
            'clarityScore' => $clarityScore,
            'recommendations' => $this->generateRecommendations(
                $wordCount,
                $sentenceCount,
                count($foundKeywords),
                $clarityScore
            )
        ];
    }

    /**
     * Generate recommendations based on analysis
     */
    private function generateRecommendations($wordCount, $sentenceCount, $keywordCount, $clarityScore)
    {
        $recommendations = [];

        if ($wordCount < 150) {
            $recommendations[] = "Consider expanding your abstract. " .
                "The ideal length is typically 150-250 words.";
        } elseif ($wordCount > 300) {
            $recommendations[] = "Your abstract may be too long. " .
                "Consider condensing it to 150-250 words.";
        }

        if ($sentenceCount < 3) {
            $recommendations[] = "Your abstract may be too brief. " .
                "Consider adding more detail about your methods and findings.";
        }

        if ($keywordCount < 3) {
            $recommendations[] = "Consider including more keywords relevant " .
                "to your research area to improve discoverability.";
        }

        if ($clarityScore < 70) {
            $recommendations[] = "The clarity of your abstract could be improved. " .
                "Consider simplifying complex sentences.";
        }

        if (empty($recommendations)) {
            $recommendations[] = "Your abstract is well-structured. Good job!";
        }

        return $recommendations;
    }

    /**
     * @copydoc Plugin::getDisplayName()
     */
    public function getDisplayName()
    {
        return __('Santane AI Analysis');
    }

    /**
     * @copydoc Plugin::getDescription()
     */
    public function getDescription()
    {
        return __('Provides AI-powered analysis of article abstracts for premium users.');
    }
}