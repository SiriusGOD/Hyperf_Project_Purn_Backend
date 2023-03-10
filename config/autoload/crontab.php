<?php
use Hyperf\Crontab\Crontab;
return [
    // 是否開啟定時任務
    'enable' => true,
    'crontab' => [
        // Command型別定時任務
        (new Crontab())->setType('command')->setName('log delete')->setRule('00 23 * * *')->setCallback([
            'command' => 'log:delete',
        ]),
    ],
];
