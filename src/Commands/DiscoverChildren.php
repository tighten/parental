<?php

namespace Tightenco\Parental\Commands;

use hanneskod\classtools\Iterator\ClassIterator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Tightenco\Parental\HasChildren;
use Tightenco\Parental\HasParent;

class DiscoverChildren extends Command
{
    protected $signature = 'parental:discover-children';

    protected $description = 'Discover the child models of parent classes using the HasChildren trait.';

    public function handle()
    {
        file_put_contents(
            $this->path(),
            '<?php'.PHP_EOL.PHP_EOL.'return '.var_export($this->findChildren(), true).';'.PHP_EOL
        );

        return true;
    }

    private function path()
    {
        $path = config('parental.discovered_children_path');
        $root = config('filesystems.disks.local.root');

        if (Str::startsWith($path, $root)) {
            $path = ltrim(Str::replaceFirst($root, '', $path), '/');
        }

        return $path;
    }

    private function findChildren()
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
