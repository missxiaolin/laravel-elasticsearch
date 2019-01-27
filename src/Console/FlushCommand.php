<?php

namespace Lin\LaravelScoutElasticsearch\Console;

use Exception;
use Illuminate\Console\Command;
use Lin\LaravelScoutElasticsearch\ElasticsearchClientTrait;

class FlushCommand extends Command
{
    use ElasticsearchClientTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:flush {--model=} {--command=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws Exception
     * @return mixed
     */
    public function handle()
    {
        $class = $this->option('model');
        $command = $this->option('command');
        if (!$class) {
            throw new Exception('model is require', 300);
            return;
        }
        $model = new $class;
        $index = [
            'index' => config('scout.elasticsearch.prefix') . $model->searchableAs()
        ];
        $client = $this->getElasticsearchClient();
        $client->indices()->delete($index);

        if ($command) {
            $this->call('scout:flush', [
                'model' => $class
            ]);
        }
    }
}
