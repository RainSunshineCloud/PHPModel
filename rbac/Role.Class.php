<?php

class Role
{
    public $roleTable = 'role_table';
    public $actionTable = 'action_table';
    public $userTable = 'user_table';
    public $userRoleTable = 'user_role_table';
    public $role_id = 'role_id';
    public $user_id = 'uid';

    /**
     *限定角色
     * User: qing
     * Date: 2018/8/19
     * Time: 下午6:28
     *
     * @param Model $model
     */
    public function (Model $model) {
        $this->model = $model;
    }
    /**
     *增加角色
     * User: qing
     * Date: 2018/8/19
     * Time: 下午6:26
     *
     * @param $arr
     */
    public function addRoles(array $arr)
    {
        $this->model->beginTransaction();
        $arr = [ 'role_id','role_name','action_id','source_id'];
        if ($this->model->insert($arr)) {
            $this->error = [

            ];

            return false;
        }
        return true;
    }

    /**
     *删除角色
     * User: qing
     * Date: 2018/8/19
     * Time: 下午6:26
     *
     * @param       $arr
     * @param Model $model
     * @return mixed
     */
    public function removeRoles($role_id)
    {
        if ( !is_array($role_id) ) $role_id = [$role_id];

        $join = sprintf('%s.%s=%s.%s',$this->roleTable,$this->role_id,$this->userRoleTable,$this->role_id);
        $this->model->beginTransaction();
         if(
             $this->model->table($this->roleTable)
                   ->rightJoin($this->userRoleTable,$join)
                   ->whereIn($this->roleTable.'.'.$this->role_id,$role_id)
                   ->delete()
             &&
             $this->model->table($this->actionTable)
                   ->whereIn($this->roleTable.'.'.$this->role_id,$role_id)
                   ->delete()
         ) {
             return $this->model ->commit();
         }

         return $this->model->rollBack();

    }

    public function removeAction()
    {

    }
}