<?php

namespace Parental\Tests\Models;

use Parental\HasParent;

class ChildNode extends Node
{
    use HasParent;

    protected $guarded = [];

    public function parent()
    {
        return $this->hasOneThrough(
            ParentNode::class,
            NodeEdge::class,
            'child_node_id',
            'id',
            'id',
            'parent_node_id',
        );
    }
}
