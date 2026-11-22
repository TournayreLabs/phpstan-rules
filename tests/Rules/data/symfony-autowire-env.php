<?php

namespace TournayreLabs\PHPStanRules\Tests\Rules\data {
    use Symfony\Component\DependencyInjection\Attribute\Autowire;

    final class EnvAutowiredService
    {
        public function __construct(
            #[Autowire(env: 'MAILER_DSN')]
            private string $dsn,
            #[Autowire('%env(APP_SECRET)%')]
            private string $secret,
            #[Autowire(value: '%env(int:PORT)%')]
            private int $port,
        ) {
        }
    }

    final class NonEnvAutowiredService
    {
        public function __construct(
            #[Autowire(param: 'kernel.project_dir')]
            private string $projectDir,
            #[Autowire(service: 'app.mailer')]
            private object $mailer,
            #[Autowire('%kernel.debug%')]
            private bool $debug,
            #[Autowire('plain')]
            private string $plain,
            private string $noAttribute,
        ) {
        }
    }

    final class EnvAutowiredController
    {
        public function __invoke(#[Autowire(env: 'APP_ENV')] string $env): void
        {
        }
    }

    final class OtherAttribute
    {
        public function __construct(#[\Other\Autowire(env: 'X')] private string $x)
        {
        }
    }

    final class AllowedEnvConfig
    {
        public function __construct(#[Autowire(env: 'MAILER_DSN')] public string $dsn)
        {
        }
    }
}
