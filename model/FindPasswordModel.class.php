<?php

class FindPasswordModel extends Model
{
    public function tableName()
    {
        return 'c_find_password';
    }

    public function add($params = array())
    {
        return $this->insert($params);
    }
}