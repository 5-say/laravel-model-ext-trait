<?php

namespace FiveSay\Laravel\Model;

/*
|--------------------------------------------------------------------------
| 特性：模型拓展
|--------------------------------------------------------------------------
| 自动注册模型观察者
|--------------------------------------------------------------------------
| 创建与更新数据前，自动校验请求数据
|--------------------------------------------------------------------------
|
*/

use Illuminate\Foundation\Validation\ValidatesRequests;

trait ExtTrait
{
    use ValidatesRequests;

    /**
     * 数据校验规则
     * @var array
     */
    // public $rules = [];

    /**
     * boot this trait
     *
     * @return void
     */
    public static function bootExtTrait()
    {
        self::registerObserve();
        self::registerValidater();
    }

    /**
     * 注册 Observer 命名空间下，与当前模型同名的模型观察者
     *
     * @return void
     */
    private static function registerObserve()
    {
        $className        = 'Observer\\'.get_called_class();
        $observableEvents = [
            'creating', 'created', 'updating', 'updated',
            'deleting', 'deleted', 'saving', 'saved',
            'restoring', 'restored',
        ];

        if (class_exists($className)) {
            $priority = 0;
            foreach ($observableEvents as $event) {
                if (method_exists($className, $event)) {
                    static::registerModelEvent($event, $className.'@'.$event, $priority);
                }
            }
        }
    }

    /**
     * 注册数据校验
     *
     * @return void
     */
    private static function registerValidater()
    {
        // 提高优先级，确保在模型观察者之前执行
        $priority = 1;

        // 创建数据前
        static::registerModelEvent('creating', function ($model) {

            if (! $model->rules) return;

            // 针对所有数据进行校验
            $modelRules = $model->rules;
            $rules      = [];
            $messages   = [];

            foreach ($modelRules as $key => $value) {
                // 构造验证规则
                $rules[$key] = implode('|', array_keys($value));

                // 构造错误信息
                array_walk($value, function ($v, $k) use ($key, &$messages) {
                    $k = explode(':', $k);
                    $messages[$key.'.'.$k[0]] = $v;
                });
            }

            // 对请求的数据进行校验
            request()->merge($model->attributes);
            $model->validate(request(), $rules, $messages);
            
        }, $priority);

        // 更新数据前
        static::registerModelEvent('updating', function ($model) {

            if (! $model->rules) return;

            // 仅针对脏数据进行校验
            $modelRules = array_intersect_key($model->rules, $model->getDirty());
            $rules      = [];
            $messages   = [];

            foreach ($modelRules as $key => $value) {
                // 构造验证规则
                $rules[$key] = implode('|', array_keys($value));

                // 构造错误信息
                array_walk($value, function ($v, $k) use ($key, &$messages) {
                    $k = explode(':', $k);
                    $messages[$key.'.'.$k[0]] = $v;
                });
            }

            // 对请求的数据进行校验
            request()->merge($model->attributes);
            $model->validate(request(), $rules, $messages);

        }, $priority);
    }
    

}
