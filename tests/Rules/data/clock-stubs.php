<?php

namespace Psr\Clock {
    interface ClockInterface
    {
        public function now(): \DateTimeImmutable;
    }
}

namespace Symfony\Component\Clock {
    interface ClockInterface extends \Psr\Clock\ClockInterface
    {
    }
}
