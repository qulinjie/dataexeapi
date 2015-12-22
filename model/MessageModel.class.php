<?php


class MessageModel extends Model
{
    public function tableName()
    {
        return 'c_message';
    }

    public function add($id,$user_id,$number)
    {
       return $this->insert(array(
           'id'            => $id,
           'user_id'       => $user_id,
           'number'        => $number,
           'add_timestamp' => date('Y-m-d H:i:s')
       ));
    }

    public function getCnt($user_id)
    {
        return $this->count(null,'id','user_id=?',$user_id);
    }

    public function searchList($user_id,$page,$cnt)
    {
        return $this->where('user_id=?',$user_id)
            ->pageLimit($page,$cnt)
            ->order('add_timestamp DESC')
            ->from()
            ->select();
    }

}