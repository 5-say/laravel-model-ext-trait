# laravel-model-ext-trait
基于 trait 的 laravel 模型特性拓展：

- 自动注册模型观察者（注册 Observer 命名空间下，与当前模型同名的模型观察者）
- 创建与更新数据前，自动校验请求数据

> 创建新数据时，对所有规则进行验证。  
> 更新数据时，仅针对脏数据进行验证。（从根本上避免类似于 `unique` 这种特殊规则在编辑时遇到的“需额外参数以排除自身”的问题）

## 安装

```shell
    composer require five-say/laravel-model-ext-trait
```

## 使用

```php
<?php

use FiveSay\Trait\Laravel\Model\ExtTrait;

class User
{
    use ExtTrait;

    /**
     * 数据校验规则
     * @var array
     */
    public $rules = [
        'name' => [
            'required' => '请填写用户名',
        ],
        'email' => [
            'required'     => '请填写 email',
            'email'        => 'email 格式不正确',
            'unique:users' => 'email 已被占用',
        ],
    ];

    
}
```

