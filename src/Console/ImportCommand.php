<?php

namespace Lin\LaravelScoutElasticsearch\Console;

use Exception;
use Illuminate\Console\Command;
use Lin\LaravelScoutElasticsearch\ElasticsearchClientTrait;

class ImportCommand extends Command
{
    use ElasticsearchClientTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elasticsearch:import {model}';

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
        $class = $this->argument('model');
        $model = new $class;
        // 主键id
        $primaryKey = $model->getKeyName();
        // 获取需要索引的字段
        $columns = $model->toSearchableArray();

        // 如果没有定义索引字段；则索引全部字段
        if (empty($columns)) {
            $columns = $model->getConnection()
                ->getSchemaBuilder()
                ->getColumnListing($model->getTable());
            $columns = array_flip($columns);
        }

        // 分词器
        $analyzer = config('scout.elasticsearch.analyzer');
        $columns = collect($columns)->except(['created_at', 'updated_at', 'deleted_at'])
            ->transform(function ($v, $k) use ($analyzer, $primaryKey) {
                if ($k == $primaryKey) {
                    return [
                        'type' => 'long'
                    ];
                } else {
                    return [
                        'type' => 'text',
                        'analyzer' => $analyzer
                    ];
                }
            });

        // 创建索引
        $type = $model->searchableAs();
        $data = [
            'index' => config('scout.elasticsearch.prefix') . $type
        ];

        $client = $this->getElasticsearchClient();

        // 判断索引是否存在 如果不存在 则初始索引
        if (!$client->indices()->exists($data)) {
            $settings = config('scout.elasticsearch.settings');
            if (!empty($settings)) {
                $data['body']['settings'] = $settings;
            }
            try {
                $client->indices()->create($data);
            } catch (Exception $ex) {
                throw new Exception('初始化失败', 500);
            }
        }

        // 创建索引
        if (!empty($columns)) {
            $data['body'] = [
                '_source' => array('enabled' => true),
                'properties' => $columns,
            ];
            $data['type'] = $type;
            try{
                $client->indices()->putMapping($data);
            }catch (Exception $ex){
                throw new Exception('创建索引失败', 500);
            }
        }

        // 导入数据
        $this->call('scout:import', [
            'model' => $class
        ]);
    }
}
