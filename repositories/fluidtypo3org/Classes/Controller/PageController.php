<?php

declare(strict_types=1);

namespace FluidTYPO3\FluidTYPO3Org\Controller;

use FluidTYPO3\Flux\Controller\PageController as FluxPageController;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class PageController extends FluxPageController
{
    public function defaultAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }
}
