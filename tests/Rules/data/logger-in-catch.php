<?php

namespace Psr\Log {
    interface LoggerInterface
    {
        public function error(string $message, array $context = []): void;
    }
}

namespace TournayreLabs\PHPStanRules\Tests\Rules\data {
    use Psr\Log\LoggerInterface;

    final class LoggerInCatchFixture
    {
        public function __construct(private LoggerInterface $logger)
        {
        }

        public function logged(): void
        {
            try {
                $this->run();
            } catch (\Throwable $e) {
                $this->logger->error($e->getMessage());
            }
        }

        public function rethrown(): void
        {
            try {
                $this->run();
            } catch (\Throwable $e) {
                throw new \RuntimeException('wrapped', 0, $e);
            }
        }

        public function swallowed(): void
        {
            try {
                $this->run();
            } catch (\Throwable $e) {
            }
        }

        public function notALogger(): void
        {
            try {
                $this->run();
            } catch (\Throwable $e) {
                $this->run();
            }
        }

        public function throwInsideClosureOnly(): void
        {
            try {
                $this->run();
            } catch (\Throwable $e) {
                $fn = static function (): void {
                    throw new \LogicException('never rethrows the caught one');
                };
            }
        }

        private function run(): void
        {
        }
    }
}
