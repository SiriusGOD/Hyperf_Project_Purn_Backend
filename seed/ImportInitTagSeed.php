<?php

declare(strict_types=1);

use App\Model\Tag;

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
class ImportInitTagSeed implements BaseInterface
{
    public function up(): void
    {
        $handle = fopen(BASE_PATH . '/storage/import/import_init_tags.csv', 'r');
        $key = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if(!empty($data[0]) && $key > 0){
                $model = Tag::where('name', $data[0])->first();
                $model -> is_init = 1;
                $model -> save();
            }
            $key++;
        }
        fclose($handle);
    }

    public function down(): void
    {
        
    }

    public function base(): bool
    {
        return false;
    }
}
