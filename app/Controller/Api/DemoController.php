<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\ObfuscationService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Annotation\Controller;
use OpenApi\Annotations as OA;

/**
 * @Controller()
 * @OA\Info(title="My First API", version="0.1")
 */
class DemoController
{
    /**
     * @RequestMapping(path="getApiDoc", methods="get")
     * 獲取 OpenApi 的 json 格式資料 (給Swagger UI使用)
     */
    public function getApiDoc(RequestInterface $request, ObfuscationService $response)
    {
        $content = file_get_contents('openapi.json');
        return $content;
    }

    /**
     * @RequestMapping(path="indexGet", methods="get")
     * 
     * @OA\Get(
     *     path="/api/demo/indexGet",
     *     tags={"Demo"},
     *     summary="",
     *     description="Demo Get 測試",
     *     operationId="",
     *     @OA\Parameter(name="Authorization", in="header", description="JWT Token", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\Parameter(name="site_id", in="query", description="", required=true,
     *         @OA\Schema(type="string", default="1")
     *     ),
     *     @OA\Response(response="200", description="返回響應資料",
     *         @OA\JsonContent(type="object",
     *             required={"errcode","timestamp","data"},
     *             @OA\Property(property="errcode", type="integer", description="錯誤碼"),
     *             @OA\Property(property="timestamp", type="integer", description=""),
     *             @OA\Property(property="data", type="string", description="返回資料"),
     *         )
     *     )
     * )
     */
    public function indexGet(RequestInterface $request, ObfuscationService $response)
    {
        return $response->replyData('Swagger Get Method!');
    }

    /**
     * @RequestMapping(path="indexPost", methods="Post")
     * 
     * @OA\Post(
     *     path="/api/demo/indexPost",
     *     tags={"Demo"},
     *     summary="",
     *     description="Demo Post 測試",
     *     operationId="",
     *     @OA\Parameter(name="Authorization", in="header", description="JWT Token", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="請求body",
     *         @OA\JsonContent(type="object",
     *             required={"site_id"},
     *             @OA\Property(property="site_id", type="integer", description="網站ID"),
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回響應資料",
     *         @OA\JsonContent(type="object",
     *             required={"errcode","timestamp","data"},
     *             @OA\Property(property="errcode", type="integer", description="錯誤碼"),
     *             @OA\Property(property="timestamp", type="integer", description=""),
     *             @OA\Property(property="data", type="object", description="返回資料",
     *                 required={"msg","site_id"},
     *                 @OA\Property(property="msg", type="string", description=""),
     *                 @OA\Property(property="site_id", type="integer", description="網站ID"),
     *             ),
     *         )
     *     )
     * )
     */
    public function indexPost(RequestInterface $request, ObfuscationService $response)
    {
        $siteId = (int) $request->input('site_id', 1);
        $re = array(
            "msg" => "Swagger Post Method!",
            "site_id" => $siteId
        );
        return $response->replyData($re);
    }

    /**
     * @RequestMapping(path="indexPut", methods="Put")
     * 
     * @OA\Put(
     *     path="/api/demo/indexPut",
     *     tags={"Demo"},
     *     summary="",
     *     description="Demo Put 測試",
     *     operationId="",
     *     @OA\Parameter(name="Authorization", in="header", description="JWT Token", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="請求body",
     *         @OA\JsonContent(type="object",
     *             required={"site_id"},
     *             @OA\Property(property="site_id", type="integer", description=""),
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回響應資料",
     *         @OA\JsonContent(type="object",
     *             required={"errcode","timestamp","data"},
     *             @OA\Property(property="errcode", type="integer", description="錯誤碼"),
     *             @OA\Property(property="timestamp", type="integer", description=""),
     *             @OA\Property(property="data", type="object", description="返回資料",
     *                 required={"msg","site_id"},
     *                 @OA\Property(property="msg", type="string", description=""),
     *                 @OA\Property(property="site_id", type="integer", description="網站ID"),
     *             ),
     *         )
     *     )
     * )
     */
    public function indexPut(RequestInterface $request, ObfuscationService $response)
    {
        $siteId = (int) $request->input('site_id', 1);
        $re = array(
            "msg" => "Swagger Put Method!",
            "site_id" => $siteId
        );
        return $response->replyData($re);
    }

    /**
     * @RequestMapping(path="indexDelete", methods="Delete")
     * 
     * @OA\Delete(
     *     path="/api/demo/indexDelete",
     *     tags={"Demo"},
     *     summary="",
     *     description="Demo Delete 測試",
     *     operationId="",
     *     @OA\Parameter(name="Authorization", in="header", description="JWT Token", required=true,
     *         @OA\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
     *     @OA\RequestBody(description="請求body",
     *         @OA\JsonContent(type="object",
     *             required={"site_id"},
     *             @OA\Property(property="site_id", type="integer", description=""),
     *         )
     *     ),
     *     @OA\Response(response="200", description="返回響應資料",
     *         @OA\JsonContent(type="object",
     *             required={"errcode","timestamp","data"},
     *             @OA\Property(property="errcode", type="integer", description="錯誤碼"),
     *             @OA\Property(property="timestamp", type="integer", description=""),
     *             @OA\Property(property="data", type="object", description="返回資料",
     *                 required={"msg","site_id"},
     *                 @OA\Property(property="msg", type="string", description=""),
     *                 @OA\Property(property="site_id", type="integer", description="網站ID"),
     *             ),
     *         )
     *     )
     * )
     */
    public function indexDelete(RequestInterface $request, ObfuscationService $response)
    {
        $siteId = (int) $request->input('site_id', 1);
        $re = array(
            "msg" => "Swagger Delete Method!",
            "site_id" => $siteId
        );
        return $response->replyData($re);
    }
}
