<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Test\Category;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batchtest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * App\Models\Test\Category
     *
     * @var string
     */
    protected $Category;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->Category = new Category();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
/*
        //Foo - Bar - Bazの階層構造
        $node = Category::create([
            'name' => 'Foo',
            'children' => [
                [
                    'name' => 'Bar',
                    'children' => [
                        [ 'name' => 'Baz' ],
                    ],
                ],
            ],
        ]);
*/
/*
		$attributes = ['name' => 'hoge'];
		Category::create($attributes);
*/
/*
		$node = Category::find(2);
		$node->saveAsRoot();
*/
/*
		$parent = Category::find(2); //Bar
		$children = Category::find(1); //Foo
		$parent->appendNode($children);
*/

		$results = Category::get();
		$tree = $results->toTree();

		$traverse = function ($categories, $prefix = '-') use (&$traverse) {
			foreach ($categories as $category) {
				echo PHP_EOL.$prefix.' '.$category->name;

				$traverse($category->children, $prefix.'-');
				}
			};
		$traverse($tree);

    }
}
