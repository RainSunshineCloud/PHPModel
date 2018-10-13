<?php

interface Model
{
    //获取用户角色
    public function getUserRole($id);
    //获取所有角色
    public function getRoles();

    //获取所有用户信息
    public function getAllUser();
    //获取
    public function getUserRoles();
    //获取角色权限
    public function getRolesAction();
    //添加角色
    public function addRole();
    //添加角色权限
    public function addRoleActions();
    //添加用户角色
    public function addUserRoles();

}
