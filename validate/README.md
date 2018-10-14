# php validate

本验证器尽量精简。现支持的特性如下

- 简单方便，支持添加自定义验证器
- 支持各个验证器之间的复用
- 支持临时验证
- 支持将规则按场景进行分组设置
- 支持自定义每个验证的错误消息
- 采用异常捕捉错误消息(TypeException)
- 采用异常返回验证器设置错误信息（validateException）

使用
```
try{
    //其中validate支持跨验证器使用，之所以在验证器内部不使用跨验证器，主要是因为容易造成类混乱
    validation::instance()->data($data)->validate('field','rule','1')->check('edit');
}catch (\TypeException $e) {
    $e->getMessage();
}

```
### 待完善
- 转换数据(将外部数据转换成自己想要的数据，并将该数据保存)，减少函数调用）
- 外部数据传入（主要Sql,比如在判断是否有该用户时，如果在验证器内查询一次，再控制器查询一次，则浪费查询,减少Sql查询）
