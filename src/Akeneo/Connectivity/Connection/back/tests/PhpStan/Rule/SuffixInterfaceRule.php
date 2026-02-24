<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\PhpStan\Rule;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

final class SuffixInterfaceRule implements Rule
{
    private const ERROR_MESSAGE = 'Interface must be suffixed with "Interface" exclusively';

    public function getNodeType(): string
    {
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassLike) {
            return [];
        }

        if (\str_ends_with((string) $node->name, 'Interface')) {
            if (!$node instanceof Interface_) {
                return [RuleErrorBuilder::message(self::ERROR_MESSAGE)->build()];
            }

            return [];
        }

        if ($node instanceof Interface_) {
            return [RuleErrorBuilder::message(self::ERROR_MESSAGE)->build()];
        }

        return [];
    }
}
