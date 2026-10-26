<?php

namespace Logonymic;

final class MatchResult
{
    public function __construct(
        public readonly Level $level,
        public readonly float $similarity,
    ) {}
}
