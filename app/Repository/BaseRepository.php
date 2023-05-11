<?php

namespace App\Repository;

use Hyperf\DbConnection\Db;

class BaseRepository
{
    public function sqlStore($sql)
    {
        return DB::select($sql);
    }

    public function dataStore($datas, $table)
    {
        DB::table($table)->insert($datas);
    }

    //foreach 存資料 回傳物件
    public function forEachStore($table, $datas)
    {
        $model = "\App\Models\\$table";
        $model = new $model;
        if (isset($datas['id'])) {
            $model = $model->find($datas['id']);
        }
        foreach ($datas as $key => $val) {
            if (!empty($val)) {
                $model->$key = $val;
            }
            if ($key == 'status' && (empty($val) || $val == '')) {
                $model->$key = 0;
            }
        }
        $model->save();
        return $model;
    }
    //共用
    public function sample(string $tableName, $where, $select, $whereFlag = false)
    {
        $obj = DB::table($tableName);
        if ($select) {
            $obj = $obj->select($select);
        }
        if ($where) {
            if ($whereFlag) {
                $obj = $obj->where([$where]);
            } else {
                $obj = $obj->where($where);
            }
        }
        return $obj;
    }
    //簡易查詢
    public function getTableData(string $tableName, $where, $select = '', $sort = '', $page = 0)
    {
        $obj = $this->sample($tableName, $where, $select);
        if ($sort) {
            $obj = $obj->orderBy($sort[0], $sort[1]);
        }
        if ($page == 0) {
            return $obj->get();
        } else {
            return $obj->paginate($page);
        }
    }
    //取得 
    public function getTableDataOffset(string $tableName, $where, $select = '', $sort = '',  $offset = 0, $take = 1)
    {
        $obj = $this->sample($tableName, $where, $select, true);
        if ($offset) {
            $obj = $obj->skip($offset);
        }
        if ($take) {
            $obj = $obj->take($take);
        }
        if ($sort) {
            $obj = $obj->orderBy($sort[0], $sort[1]);
        }
        return $obj->get();
    }

    //取得 
    public function findTableData(string $tableName, int $id, $select = '*')
    {
        $obj = DB::table($tableName)->select($select)->find($id);
        if ($obj) {
            return $obj;
        } else {
            return  false;
        }
    }

    //update 
    public function updateTableData(string $tableName, int $id, $data)
    {
        $obj = DB::table($tableName)->where('id',$id)->update($data);
        if ($obj) {
            return $obj;
        } else {
            return  false;
        }
    }

    //刪除
    public function delete(string $tableName, $where )
    {
        $obj = DB::table($tableName)->where($where)->delete();
        if ($obj) {
            return true;
        } else {
            return  false;
        }
    }
}

