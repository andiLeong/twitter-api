<?php

namespace Tests;

use BadMethodCallException;
use Illuminate\Support\Facades\DB;

class ValidationTester
{
    private $isJson = true;
    private $method;
    private $endpoint;
    private $rules;
    private $test;
    private $config;

    public function __construct($method, $endpoint, $rules, $test, $config = [])
    {
        $this->method = $method;
        $this->endpoint = $endpoint;
        $this->rules = $rules;
        $this->test = $test;
        $this->config = $config;
    }

    public function run()
    {
        foreach ($this->rules as $attribute => $rule) {
            if (!is_array($rule)) {
                $rule = explode('|', $rule);
            }

            foreach ($rule as $singleRule) {
                [$method, $args] = $this->parseRule($singleRule);

                if (!method_exists($this, $method)) {
                    continue;
                }

                $this->$method($attribute, $args);
            }
        }
    }

    public function fire($payload = [])
    {
        $method = $this->getMethodToCall();
        return $this->test->$method($this->endpoint, $payload);
    }

    public function getMethodToCall()
    {
        $binding = [
            'post' => 'postJson',
        ];

        if (!array_key_exists($this->method, $binding)) {
            throw new BadMethodCallException('Method not found');
        }

        return $binding[$this->method];
    }

    protected function email($attribute)
    {
        $res = $this->fire([
            $attribute => 'not an email',
        ]);
        $res->assertJsonValidationErrorFor($attribute);
    }

    protected function required($attribute)
    {
        $res = $this->fire();
        $res->assertJsonValidationErrorFor($attribute);
    }

    protected function min($attribute, $args = [])
    {
        $length = $args[0];
        $res = $this->fire([
            $attribute => str_repeat('a', $length - 1),
        ]);
        $res->assertJsonValidationErrorFor($attribute);
    }

    protected function max($attribute, $args = [])
    {
        $length = $args[0];
        $res = $this->fire([
            $attribute => str_repeat('a', $length + 1),
        ]);
        $res->assertJsonValidationErrorFor($attribute);
    }

    protected function confirmed($attribute)
    {
        $res = $this->fire([
            $attribute => 'password',
            $attribute . '_confirmation' => 'different password',
        ]);
        $res->assertJsonValidationErrorFor($attribute);
    }

    protected function unique($attribute, $args)
    {
        $field = $args[1];

        $model = $this->config[$attribute][__FUNCTION__];

        $res = $this->fire([
            $attribute => $model->$field,
        ]);

        $res->assertJsonValidationErrorFor($attribute);
    }

    private function parseRule(mixed $singleRule)
    {
        $method = $singleRule;
        if (str_contains($singleRule, ':')) {
            $exploded = explode(':', $singleRule);
            $method = $exploded[0];
            $payload = explode(',', $exploded[1]);
            $payload[0] = str_replace(':', '', $payload[0]);
        }

        return [$method, $payload ?? []];
    }
}
