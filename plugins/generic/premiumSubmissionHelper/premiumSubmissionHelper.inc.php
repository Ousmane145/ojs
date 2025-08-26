<?php

/**
 * @file PremiumSubmissionHelper.inc.php
 *
 * Copyright (c) 2023 Santane Analysis
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class PremiumSubmissionHelper
 * @brief Main class for Premium Submission Helper plugin.
 */

namespace APP\plugins\generic\premiumSubmissionHelper;

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
                'premiumSubmissionHelper',
                $this->getPluginPath() . '/js/premiumSubmissionHelper.js',
                array('contexts' => 'backend')
            );

            // Add custom CSS
            $templateMgr->addStyleSheet(
                'premiumSubmissionHelper',
                $this->getPluginPath() . '/css/premiumSubmissionHelper.css',
                array('contexts' => 'backend')
            );

            // Add the button and results container
            $templateMgr->assign('showPremiumSubmissionHelper', true);
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

        if ($page === 'premiumHelper' && $op === 'analyze') {
            $this->handleAnalysisRequest();
            return true; // ← IMPORTANT: return true au lieu de exit
        }

        return false;
    }

    // ... le reste du code inchangé mais avec le même namespace corrigé
}