<?php

namespace Spatie\DatabaseCleanup;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Stmt;

class MyNodeVisitor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Stmt\Class_) {
            return $node->namespacedName->toString();
        }
    }
}
