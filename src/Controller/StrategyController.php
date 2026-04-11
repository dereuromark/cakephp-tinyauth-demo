<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Strategy;

/**
 * Landing page for the four usage strategies the demo showcases.
 *
 * Each strategy is its own route prefix with its own controller
 * namespace under `App\Controller\{Prefix}\`. This page is the
 * entry point that explains the differences and links into each
 * subtree.
 */
class StrategyController extends AppController
{
    /**
     * @return void
     */
    public function index(): void
    {
        $strategies = Strategy::all();
        $this->set(compact('strategies'));
        $this->set('pageTitle', 'Usage Strategies');
    }
}
