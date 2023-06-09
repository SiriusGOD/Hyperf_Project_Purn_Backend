<?php

declare(strict_types=1);

use App\Model\Actor;
use App\Model\ActorHasClassification;
use Hyperf\DbConnection\Db;
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class ActorSeed implements BaseInterface
{
    public function up(): void
    {
        // 讀檔
        $filePath = BASE_PATH . '/storage/import/actors.csv';
        $file = fopen($filePath, 'r');

        if ($file) {
            $key = 0;
            // 清空 actor table
            Actor::truncate();
            // 清空 actor_has_classifications
            ActorHasClassification::truncate();
            // 例外處理 未分類演員
            Db::insert("INSERT INTO actors (id, user_id, sex, name, created_at, updated_at) VALUES (0, 1, 1, '未分類', now(), now())");
            Db::update('update actors set id = 0 where id = 1');
            $ahc = new ActorHasClassification();
            $ahc -> actor_id = 0;
            $ahc -> actor_classifications_id = 12;
            $ahc -> save();
            
            while (($line = fgets($file)) !== false) {
                if($key == 0){
                    $key++;
                    continue;
                }
                $parts = explode(',', $line);
                var_dump($parts);
                
                // 是否已有重複值
                $actor_name = trim($parts[0]);
                $actor = Actor::where('name', $actor_name)->first();
                if(empty($actor)){
                    // insert to actor table
                    $model = new Actor();
                    $model -> user_id = 1;
                    $model -> sex = 1;
                    $model -> name = trim($parts[0]);
                    $model -> save();

                    $actor_id = $model -> id;
                }else{
                    $actor_id = $actor -> id;
                }
                
                // insert to actor_has_classifications
                $ahc = new ActorHasClassification();
                $ahc -> actor_id = $actor_id;
                $ahc -> actor_classifications_id = (int)trim($parts[1]);
                $ahc -> save();
                
                $key++;
                var_dump('已新增第'.$key.'筆');
            }
            
            fclose($file);
        }else{
            var_dump('查無檔案');
        }

        
    }

    public function down(): void
    {
        \App\Model\Actor::truncate();
        \App\Model\ActorHasClassification::truncate();
    }

    public function base(): bool
    {
        return false;
    }
}
