<?php

namespace Tightenco\Parental\Commands;

use hanneskod\classtools\Iterator\ClassIterator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Finder\Finder;
use Tightenco\Parental\HasChildren;
use Tightenco\Parental\HasParent;

class DiscoverChildren extends Command
{
    protected $signature = 'parental:discover-children';

    protected $description = 'Discover the child models of parent classes using the HasChildren trait.';

    public function handle()
    {
        if (! file_exists(config_path('parental.php'))) {
            file_put_contents(config_path('parental.php'), file_get_contents(__DIR__.'/../../config/parental.php'));
        }

        $content = file_get_contents(config_path('parental.php'));
        $asString  = "'discovered_children' => ".var_export($this->findChildren(), true).', //just an anchor';

        $content = preg_replace("/\'discovered_children\' => .*, \/\/just an anchor/", $asString, $content);

        file_put_contents(config_path('parental.php'), $content);

        return true;
    }

    public function findChildren()
    {
        $finder = new Finder;
        $iter = new ClassIterator($finder->in(config('parental.model_directories', [])));
        $children = [];

        foreach ($iter->getClassMap() as $class => $fileInfo) {
            try {
                if (! is_a($class, Model::class, true)) {
                    continue;
                }
                $traits = class_uses_recursive($class);

                if (in_array(HasParent::class, $traits) // It's a child
                    && in_array(HasChildren::class, $traits) // and the parent has the parent trait
                ) {
                    $parent = get_parent_class($class);
                    $children[$parent][$class] = $class;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $children;
    }
}
