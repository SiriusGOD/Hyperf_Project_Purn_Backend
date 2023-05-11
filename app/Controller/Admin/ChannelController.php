<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller\Admin;

use App\Model\Channel;
use App\Service\BaseService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\View\RenderInterface;

#[Controller]
class ChannelController extends TemplateController 
{
    public $main = 'channels';
    public $entity;
    public $fieldsSetting;
    public $baseService;
    public $redner;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct(RenderInterface $render, Channel $channel, BaseService $baseService)
    {       
        $this->render = $render;
        $this->entity = $channel;
        $this->fieldsSetting = $this->entity->tableFieldsSetting();
        $this->baseService = $baseService;
    }
}
