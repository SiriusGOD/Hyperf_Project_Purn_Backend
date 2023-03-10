<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Hyperf\DbConnection\Db;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 * 產生Swagger注釋範例
 */
#[Command]
class SwaggerCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    // 表注釋對應
    protected $swaggerTable;

    // 請求方式
    protected $swaggerMethod;

    // url路徑
    protected $swaggerPath;

    // 標籤
    protected $swaggerTag;

    // 概要
    protected $swaggerSummary;

    // 描述
    protected $swaggerDescription;

    // query格式資料
    protected $swaggerQuery;

    // 請求 json 資料類型
    protected $swaggerRequestBody;

    // 響應 json 資料類型
    protected $swaggerResponse;

    /**
     * 執行的命令行.
     *
     * @var string
     */
    protected $name = 'swagger:format';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct($this -> name);
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp('範例: php bin/hyperf.php swagger:format Post \'{"errcode":0,"errmsg":"success","data":{"token":"666"}}\'');
        $this->setDescription('Swagger mode 生成');
        $this->addArgument('method', InputArgument::OPTIONAL, '請求方式', 'Get');
        $this->addArgument('response', InputArgument::OPTIONAL, '響應 json 資料類型');
        $this->addArgument('table', InputArgument::OPTIONAL, '自動填充資料表');
        $this->addOption('path', 'P', InputOption::VALUE_OPTIONAL, 'url路徑');
        $this->addOption('tag', 'T', InputOption::VALUE_OPTIONAL, '標籤');
        $this->addOption('summary', 'S', InputOption::VALUE_OPTIONAL, '概要');
        $this->addOption('description', 'D', InputOption::VALUE_OPTIONAL, '描述');
        $this->addOption('query', 'Q', InputOption::VALUE_OPTIONAL, 'query格式資料');
        $this->addOption('request', 'R', InputOption::VALUE_OPTIONAL, '請求 json 資料類型');
    }

    public function handle()
    {
        $this->swaggerTable = $this->input->getArgument('table');
        $this->swaggerMethod = ucfirst(strtolower($this->input->getArgument('method')));
        $this->swaggerPath = $this->getSwaggerPath($this->input->getOption('path'));
        $this->swaggerTag = $this->getSwaggerTag($this->input->getOption('tag'));
        $this->swaggerSummary = $this->getSwaggerSummary($this->input->getOption('summary'));
        $this->swaggerDescription = $this->getSwaggerDescription($this->input->getOption('description'));
        $this->swaggerQuery = $this->getSwaggerParameter($this->input->getOption('query'));
        $this->swaggerRequestBody = $this->getSwaggerRequestBody($this->input->getOption('request'));
        $this->swaggerResponse = $this->getSwaggerResponse($this->input->getArgument('response'));
        echo $this->getSwaggerModel();
    }

    /**
     * 獲取 swagger 結構.
     */
    public function getSwaggerModel()
    {
        $doc = PHP_EOL . PHP_EOL;
        $header = $this->swaggerPath . $this->swaggerTag . $this->swaggerSummary . $this->swaggerDescription;
        $header = trim($header, PHP_EOL);

        $body = $this->swaggerQuery . $this->swaggerRequestBody . $this->swaggerResponse;
        $body = trim($body, PHP_EOL);
        $doc .= <<<eof
    /**
     * @OA\\{$this->swaggerMethod}(
{$header}
     *     operationId="",
     *     @OA\\Parameter(name="Authorization", in="header", description="JWT Token", required=true,
     *         @OA\\Schema(type="string", default="Bearer {{Authorization}}")
     *     ),
{$body}
     * )
     */
eof;
        $doc .= PHP_EOL . PHP_EOL;
        return $doc;
    }

    /**
     * 獲取路徑資料.
     * @param null|string $path 路徑
     */
    public function getSwaggerPath(?string $path): string
    {
        $doc = <<<eof
     *     path="{$path}",
eof;
        $doc .= PHP_EOL;
        return $doc;
    }

    /**
     * 獲取標籤資料.
     * @param null|string $tag 標籤
     */
    public function getSwaggerTag(?string $tag): string
    {
        $doc = <<<eof
     *     tags={"{$tag}"},
eof;
        $doc .= PHP_EOL;
        return $doc;
    }

    /**
     * 獲取概要資料.
     * @param null|string $summary 概要
     * @return string 概要格式資料
     */
    public function getSwaggerSummary(?string $summary): string
    {
        $doc = <<<eof
     *     summary="{$summary}",
eof;
        $doc .= PHP_EOL;
        return $doc;
    }

    /**
     * 獲取描述資料.
     * @param null|string $description 描述
     * @return string 描述格式資料
     */
    public function getSwaggerDescription(?string $description): string
    {
        $doc = <<<eof
     *     description="{$description}",
eof;
        $doc .= PHP_EOL;
        return $doc;
    }

    /**
     * query 参数拼接.
     */
    public function getSwaggerParameter(?string $parameter): string
    {
        if (! $parameter) {
            return <<<'eof'
eof;
        }
        parse_str($parameter, $output);
        if (! $parameter) {
            return <<<'eof'
eof;
        }
        $doc = '';
        foreach ($output as $k => $v) {
            $type = $this->gettype($v);

            $doc .= <<<eof
     *     @OA\\Parameter(name="{$k}", in="query", description="",
     *         @OA\\Schema(type="{$type}", default="{$v}")
     *     ),
eof;
            $doc .= PHP_EOL;
        }
        return $doc;
    }

    /**
     * 獲取請求 JsonContent 資料.
     */
    public function getSwaggerRequestBody(?string $requestBody): string
    {
        if (! $requestBody) {
            return <<<'eof'
eof;
        }
        $requestBody = $this->constructSwaggerDoc($requestBody);
        $doc = <<<eof
     *     @OA\\RequestBody(description="請求body",
     *         @OA\\JsonContent(type="object",
{$requestBody}
     *         )
     *     ),
eof;
        $doc .= PHP_EOL;
        return $doc;
    }

    /**
     * 獲取響應 JsonContent 資料.
     * @param null|string $response 響應json
     */
    public function getSwaggerResponse(?string $response): string
    {
        $comment = $this->getColumnComment($this->swaggerTable);
        $comment['errcode'] = '錯誤碼';
        $comment['errmsg'] = '錯誤訊息';
        $comment['data'] = '返回資料';
        if (! $response) {
            return <<<eof
     *     @OA\\Response(response="200", description="返回響應資料",
     *         @OA\\JsonContent(type="object",
     *             @OA\\Property(property="errcode", type="integer", description="{$comment['errcode']}"),
     *             @OA\\Property(property="errmsg", type="string", description="{$comment['errmsg']}"),
     *             @OA\\Property(property="data", type="object", description="{$comment['data']}",
     *                  @OA\\Property(property="example", type="string", description="例子",
     *             )
     *         )
     *     )
eof;
        }
        $response = $this->constructSwaggerDoc($response, $comment);
        return <<<eof
     *     @OA\\Response(response="200", description="返回響應資料",
     *         @OA\\JsonContent(type="object",
{$response}
     *         )
     *     )
eof;
    }

    /**
     * 檢測是否為有序資料.
     * @param mixed $array
     * @return string array 有序資料 object 無序数组
     */
    public function checkIsArray($array): string
    {
        if (! is_array($array)) {
            return 'string';
        }
        $num = count($array);
        for ($i = 0; $i < $num; ++$i) {
            if (isset($array[$i])) {
                continue;
            }
            return 'object';
        }
        return 'array';
    }

    /**
     * 構造 JsonContent 資料.
     * @param array|string $data json格式字符串資料｜数组資料
     * @param array $comment 注釋
     * @param string $placeholder 占位空格数量
     * @param string $propertyType 資料格式
     * @return string JsonContent格式資料
     */
    private function constructSwaggerDoc($data, array $comment = [], string $placeholder = '', string $propertyType = 'Property'): string
    {
        if (! $data) {
            return <<<'eof'
eof;
        }
        $data = is_array($data) ? $data : json_decode($data, true);
        $required = $this->arrayToJsonObject($data);
        $doc = '';
        if ($propertyType == 'Items') {
            $doc .= <<<eof
     *             {$placeholder}@OA\\Items(
eof;
            $doc .= PHP_EOL;
            $placeholder .= '    ';
        }
        $doc .= <<<eof
     *             {$placeholder}required={$required},
eof;
        $doc .= PHP_EOL;
        foreach ($data as $k => $v) {
            $description = $comment[$k] ?? null;
            $type = $this->gettype($v);
            if (in_array($type, ['object', 'array'])) {
                if (isset($v[0]) || empty($v)) {
                    if (isset($v[0]) && is_array($v[0])) {
                        $doc .= <<<eof
     *             {$placeholder}@OA\\Property(property="{$k}", type="array", description="{$description}",
eof;
                        $doc .= PHP_EOL;
                        $newPlaceholder = $placeholder . '    ';
                        $doc .= $this->constructSwaggerDoc($v[0], $comment, $newPlaceholder, 'Items');
                    } else {
                        $doc .= <<<eof
     *             {$placeholder}@OA\\Property(property="{$k}", type="array", description="{$description}",
     *                 {$placeholder}@OA\\Items()
eof;
                        $doc .= PHP_EOL;
                    }
                } else {
                    $doc .= <<<eof
     *             {$placeholder}@OA\\Property(property="{$k}", type="object", description="{$description}",
eof;
                    $doc .= PHP_EOL;
                    $newPlaceholder = $placeholder . '    ';
                    $doc .= $this->constructSwaggerDoc($v, $comment, $newPlaceholder);
                }
                if (isset($v[0]) || empty($v)) {
                    $doc .= <<<eof
     *             {$placeholder}),
eof;
                } else {
                    $doc .= <<<eof
     *             {$placeholder}),
eof;
                }
                $doc .= PHP_EOL;
            } else {
                $doc .= <<<eof
     *             {$placeholder}@OA\\Property(property="{$k}", type="{$type}", description="{$description}"),
eof;
                $doc .= PHP_EOL;
            }
        }
        if ($propertyType == 'Items') {
            $placeholder = substr($placeholder, 0, -4);
            $doc .= <<<eof
     *             {$placeholder}),
eof;
            $doc .= PHP_EOL;
        }
        if (! $placeholder) {
            $doc = trim($doc, PHP_EOL);
        }
        return $doc;
    }

    /**
     * 獲取資料類型.
     * @param $value
     */
    private function gettype($value): string
    {
        $type = (string) gettype($value);
        switch ($type) {
            case 'array':
                $result = $this->checkIsArray($value);
                break;
            case 'integer':
            case 'boolean':
            case 'string':
                $result = $type;
                break;
            default:
                $result = 'string';
                break;
        }
        return $result;
    }

    /**
     * json数组格式轉化為對象格式.
     * @param array $array 有序数组
     */
    private function arrayToJsonObject(array $array): string
    {
        $jsonObject = trim(json_encode(array_keys($array)), '[');
        return '{' . trim($jsonObject, ']') . '}';
    }

    /**
     * 獲取資料表字段描述.
     */
    private function getColumnComment(?string $tables): array
    {
        if (empty($tables)) {
            return [];
        }

        $tablesList = explode(',', $tables);
        if (! $tablesList) {
            return [];
        }
        $haveKey = [];
        $list = [];
        foreach ($tablesList as $v) {
            $sql = "SELECT COLUMN_NAME,column_comment FROM INFORMATION_SCHEMA.Columns WHERE table_name = '{$v}'";
            $comment = Db::select($sql);
            // 需要保持字段名唯一，唯一字段才会保留
            foreach ($comment as $v) {
                // 判断字段名是否出現過
                if (in_array($v['COLUMN_NAME'], $haveKey)) {
                    continue;
                }
                // 判断字段名是否存在，存在的情况需要移除，并加入到 haveKey 数组
                if (isset($list[$v['COLUMN_NAME']])) {
                    unset($list[$v['COLUMN_NAME']]);
                    array_push($haveKey, $v['COLUMN_NAME']);
                }
                $list[$v['COLUMN_NAME']] = $v['column_comment'];
            }
        }
        return $list;
    }
}
