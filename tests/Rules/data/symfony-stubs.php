<?php

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
