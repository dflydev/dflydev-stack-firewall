<?php

namespace Dflydev\Stack;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Firewall implements HttpKernelInterface
{
    private $app;
    private $firewall;
    private $options;

    public function __construct(HttpKernelInterface $app, array $options = [])
    {
        $this->app = $app;
        $this->firewall = $options['firewall'];
        unset($options['firewall']);
        $this->options = $options;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $firewall = static::matchFirewall($request, $this->firewall);

        if (null === $firewall) {
            // If no firewall is matched we can delegate immediately.
            return $this->app->handle($request, $type, $catch);
        }

        // Otherwise, we should attempt authentication and we should let the
        // firewall dictate whether or not anonymous requests should be
        // allowed.
        return (new Authentication(
                $this->app,
                array_merge(
                    $this->options,
                    ['anonymous' => $firewall['anonymous']]
                )
            ))
            ->handle($request, $type, $catch);
    }

    /**
     * Left public currently so we can test this by itself; eventually would
     * maybe like to make this a service that can be swapped out via
     * configuration? Not sure what to do with it, really.
     */
    public static function matchFirewall(Request $request, array $firewalls)
    {
        if (!$firewalls) {
            // By default we should firewall the root request and not allow
            // anonymous requests. (will force challenge immediately)
            $firewalls = [
                ['path' => '/']
            ];
        }

        $sortedFirewalls = [];
        foreach ($firewalls as $firewall) {
            if (!isset($firewall['anonymous'])) {
                $firewall['anonymous'] = false;
            }

            if (isset($sortedFirewalls[$firewall['path']])) {
                throw new \InvalidArgumentException("Path '".$firewall['path']."' specified more than one time.");
            }

            $sortedFirewalls[$firewall['path']] = $firewall;
        }

        // We want to sort things by more specific paths first. This will
        // ensure that for instance '/' is never captured before any other
        // firewalled paths.
        krsort($sortedFirewalls);

        foreach ($sortedFirewalls as $path => $firewall) {
            if (0 === strpos($request->getPathInfo(), $path)) {
                return $firewall;
            }
        }

        return null;
    }
}
