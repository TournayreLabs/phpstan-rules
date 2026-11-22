<?php

namespace Symfony\Bundle\FrameworkBundle\Controller {
    abstract class AbstractController
    {
    }
}

namespace Symfony\Component\HttpKernel\Attribute {
    #[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
    class AsController
    {
    }
}

namespace Symfony\Component\DependencyInjection\Attribute {
    #[\Attribute(\Attribute::TARGET_PARAMETER)]
    final class Autowire
    {
        public function __construct(
            mixed $value = null,
            ?string $service = null,
            ?string $expression = null,
            ?string $env = null,
            ?string $param = null,
        ) {
        }
    }
}
