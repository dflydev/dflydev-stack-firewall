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
        $this->matcher = new Firewall\Matcher($options['firewall']);
        unset($options['firewall']);
        $this->options = $options;
    }

    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $firewall = $this->matcher->match($request);

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

}
