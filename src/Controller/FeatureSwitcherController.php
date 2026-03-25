<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Controller for toggling TinyAuth features in demo mode.
 *
 * Stores feature toggles in session, which are then applied
 * to Configure in bootstrap for FeatureService to use.
 */
class FeatureSwitcherController extends AppController
{
    /**
     * Available features that can be toggled.
     *
     * @var array<string>
     */
    public const FEATURES = ['acl', 'allow', 'roles', 'resources', 'scopes'];

    /**
     * Toggle a specific feature.
     *
     * @return \Cake\Http\Response|null
     */
    public function toggle(): ?Response
    {
        $feature = $this->request->getData('feature');
        $enabled = (bool)$this->request->getData('enabled');

        if (!in_array($feature, self::FEATURES, true)) {
            $this->Flash->error(__('Invalid feature: {0}', $feature));

            return $this->redirect($this->referer('/'));
        }

        $features = $this->request->getSession()->read('TinyAuthFeatures') ?? [];
        $features[$feature] = $enabled;
        $this->request->getSession()->write('TinyAuthFeatures', $features);

        $status = $enabled ? 'enabled' : 'disabled';
        $this->Flash->success(__('Feature "{0}" {1}', $feature, $status));

        return $this->redirect($this->referer('/'));
    }

    /**
     * Update multiple features at once.
     *
     * @return \Cake\Http\Response|null
     */
    public function update(): ?Response
    {
        $features = [];
        foreach (self::FEATURES as $feature) {
            $features[$feature] = (bool)$this->request->getData($feature);
        }

        $this->request->getSession()->write('TinyAuthFeatures', $features);
        $this->Flash->success(__('Feature settings updated'));

        return $this->redirect($this->referer('/'));
    }

    /**
     * Reset features to auto-detect mode.
     *
     * @return \Cake\Http\Response|null
     */
    public function reset(): ?Response
    {
        $this->request->getSession()->delete('TinyAuthFeatures');
        $this->Flash->success(__('Features reset to auto-detect'));

        return $this->redirect($this->referer('/'));
    }
}
