<?php

declare(strict_types=1);

namespace App\Service;
use Elasticsearch\ClientBuilder;
use Hyperf\HttpServer\Annotation\AutoController;

class ElasticsearchService 
{
  /*
 *  新增多筆 
 * */
  public function elkCreateBulk()
  {
        $client = ClientBuilder::create()->setHosts([env('ELK_SEARCH')])->build();
        $params = ['body' => []];
        $params['body'][] = [
            'index' => [
                '_index' => 'movies',
                '_id' => '1',
            ]
        ];
        $params['body'][] = [
            'title' => 'Inception',
            'year' => 2010,
        ];
        // Document 2
        $params['body'][] = [
            'index' => [
                '_index' => 'movies',
                '_id' => '2',
            ]
        ];
        $params['body'][] = [
            'title' => 'The Dark Knight',
            'year' => 2008,
        ];
        // Send the bulk request
        $response = $client->bulk($params);
        return $response;
  }
  /*
 *  新增單筆 
 * */
  public function elkCreate(){
    $client = ClientBuilder::create()->setHosts([env('ELK_SEARCH')])->build();
    $params = [
        'index' => 'shakespeare',
        'id'    => '1',
        'body'  => [
            'type' => 'line',
            'line_id' => 4,
            'play_name' => 'Hamletvictor',
            'speech_number' => 1,
            'line_number' => '1.1.1',
            'speaker' => 'BERNARDO',
            'text_entry' => "Who's there?"
        ]
    ];

    $response = $client->index($params);

    return $response;
  }

  /*
   * 簡易搜尋 
   */
  public function elkSearch(string $index , string $key, string $val){
    $client = ClientBuilder::create()->setHosts([env('ELK_SEARCH')])->build();
    $params = [
        'index' => $index,
        'body'  => [
                'query' => [
                    'match' => [
                        $key => $val 
                    ]
                ]
            ]
    ];
    $response = $client->search($params);
    return $response;
  }
}
