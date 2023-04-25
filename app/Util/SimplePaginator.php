<?php

namespace App\Util;

class SimplePaginator
{
    protected int $page;

    protected int $limit;

    protected string $path;

    public function __construct(int $page, int $limit, ?string $path)
    {
        $this->page = $page;
        $this->path = $path;
        $this->limit = $limit;
    }

    public function render() : array
    {
        $result = [
            'page' => $this->page,
            'step' => $this->limit,
        ];

        if (! empty($this->path)) {
            $result['next'] = $this->path . '?page=' . ($this->page + 1);
            $result['prev'] = $this->path . '?page=' . (($this->page == 0 ? 1 : $this->page) - 1);
        }

        return $result;
    }
}