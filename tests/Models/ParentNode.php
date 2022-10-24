<?php

namespace Parental\Tests\Models;

use Parental\HasParent;

class ParentNode extends Node
{
    use HasParent;

    protected $guarded = [];

    public function children()
    {
        return $this->hasManyThrough(
            ChildNode::class,
            NodeEdge::class,
            'parent_node_id',
            'id',
            'id',
            'child_node_id',
        );
    }
}
