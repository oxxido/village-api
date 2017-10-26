<?php

namespace App\Airtable;

use TANIOS\Airtable\Airtable;
use TANIOS\Airtable\Request;
use TANIOS\Airtable\Response;

class Builder
{
    const PAGE_SIZE = 100;
    /** @var Airtable $airtable */
    protected $airtable;
    /** @var  Request */
    protected $request;
    /** @var  Response */
    protected $response;
    protected $pageSize = self::PAGE_SIZE;
    protected $filterByFormula = [];
    protected $fields = [];
    protected $sort = [];
    protected $view;
    protected $offset;
    protected $table;

    public function __construct($table)
    {
        $this->table    = $table;
        $this->airtable = app(Airtable::class);
    }

    public function get($page_size = null)
    {
        $this->pageSize = $page_size ?? $this->pageSize ?? self::PAGE_SIZE;

        $response = $this->fetch();

        $records = $response['records'] ?? [];

        return $records;
    }

    public function all()
    {
        $all            = [];
        $current_offset = null;

        do {
            $previous_offset = $current_offset;
            if (!($page = $this->get())) {
                break;
            }

            $all = array_merge($all, $page);

            $current_offset = $this->getOffset();
        } while ($previous_offset !== $current_offset);

        return $all;
    }

    public function first()
    {
        $this->pageSize = 1;

        $response = $this->fetch();

        $record = $response['records'][0] ?? null;

        return $record;
    }

    public function where($filter)
    {
        $this->filterByFormula = $filter;

        return $this;
    }

    public function view($view)
    {
        $this->view = $view;

        return $this;
    }

    public function sortBy($field, $direction = 'asc')
    {
        array_push($this->sort, ['field' => $field, 'direction' => $direction]);

        return $this;
    }

    public function select($field)
    {
        array_push($this->fields, $field);

        return $this;
    }

    public function offset($offset = null)
    {
        return $offset ? $this->setOffset($offset) : $this->getOffset();
    }

    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    public function getOffset()
    {
        if ($this->response && $offset = $this->response['offset']) {
            $this->offset = $offset;
        }

        return $this->offset;
    }

    protected function params()
    {
        $params = [
            'pageSize'        => $this->pageSize,
            'sort'            => $this->sort ? json_encode($this->sort) : null,
            'fields'          => $this->fields ? json_encode($this->fields) : null,
            'filterByFormula' => $this->filterByFormula ?? null,
            'offset'          => $this->offset ?? null,
            'view'            => $this->view ?? null,
        ];

        return array_filter($params, function ($param) {
            return $param !== null;
        });
    }

    protected function fetch()
    {
        $this->request = $this->airtable->getContent($this->table, $this->params());

        return $this->response = $this->request->getResponse();
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function post($fields, $table = null)
    {
        $table = $table ?? $this->table;

        return $this->airtable->saveContent($table, $fields);
    }

    public function put($fields, $table = null)
    {
        $table = $table ?? $this->table;

        return $this->airtable->updateContent($table, $fields);
    }
}
