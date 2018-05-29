<?php

namespace Dflydev\Stack\Firewall;

use Symfony\Component\HttpFoundation\Request;

class Matcher
{
    private $firewalls = [];

    public function __construct(array $firewalls)
    {
        if (!$firewalls) {
            // By default we should firewall the root request and not allow
            // anonymous requests. (will force challenge immediately)
            $firewalls = [
                ['path' => '/']
            ];
        }

        foreach ($firewalls as $firewall) {
            // Set default values
            $firewall = $firewall + [
                'anonymous' => false,
                'exact_match' => false,
                'method' => null
            ];
            $this->firewalls[] = $firewall;
        }

        // We want to sort things by more specific paths first. This will
        // ensure that for instance '/' is never captured before any other
        // firewalled paths.
        uasort($this->firewalls, function($a, $b) {
            if ($a['path'] === $b['path']) {
                return 0;
            }
            return -($a['path'] > $b['path'] ? 1 : -1);
        });
    }

    /**
     * Find the matching path
     */
    public function match(Request $request)
    {
        foreach ($this->firewalls as $firewall) {
            if ($firewall['method'] !== null && $request->getMethod() !== $firewall['method']) {
                continue;
            }

            if ($firewall['exact_match']) {
                if ($request->getPathInfo() === $firewall['path']) {
                    return $firewall;
                }
            } elseif (0 === strpos($request->getPathInfo(), $firewall['path'])) {
                return $firewall;
            }
        }

        return null;
    }
}
