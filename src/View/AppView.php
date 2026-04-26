<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link https://cakephp.org CakePHP(tm) Project
 * @since 3.0.0
 * @license https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\View;

use Cake\View\View;

/**
 * Application View
 *
 * Your application's default view class
 *
 * @link https://book.cakephp.org/5/en/views.html#the-app-view
 */
class AppView extends View
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like adding helpers.
     *
     * e.g. `$this->addHelper('Html');`
     *
     * @return void
     */
    public function initialize(): void
    {
        // Override FormHelper templates that ship with inline `style="..."`
        // attributes so the demo runs cleanly under strict CSP. The default
        // `hiddenBlock` (used to wrap the CSRF token field on every form) is
        // `<div style="display:none;">{{content}}</div>`. Replace with the
        // HTML5 `hidden` attribute, which needs no CSS.
        $this->loadHelper('Form', [
            'templates' => [
                'hiddenBlock' => '<div hidden>{{content}}</div>',
            ],
        ]);
    }
}
