<?php

namespace App\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Базовый класс фильтров для Illuminate\Database\Eloquent\Builder
 *
 * Взял из своих прошлых наработок какой был, но есть много к чему приложить руки
 */
abstract class Filter
{
    const FILTER_TYPE_EXACT = 'EXACT';
    const FILTER_TYPE_LIKE = 'LIKE';

    /**
     * Поле с данными фильтра во входящих данных
     * Null если в корне
     */
    protected ?string $nameFilter = 'filter';

    private array $data;

    private Request $request;

    /**
     * Набор фильтров
     * Типы:
     *      FILTER_TYPE_EXACT - точное совпадение, для сравнения применяется 'namefilter = value'
     *      FILTER_TYPE_LIKE - текстовое сравнение, для сравнения применяется "namefilter like '%value%'"
     *      closure - параметры:
     *          $query - Illuminate\Database\Eloquent\Builder запрос для модификации
     *          $column - имя поля в фильтре
     *          $value - значение фильтра
     * Формат:
     * [
     *  'namefilter' => FILTER_TYPE_EXACT|FILTER_TYPE_LIKE|closure
     *  ....
     * ]
     */
    abstract public function getFilters(): array;

    /**
     * Правила валидации передаются в \Illuminate\Validation\Validator
     * Весь синтаксис берется оттуда
     *
     * В фильтре используются только прошедшие валидацию данные, остальные отбрасываются
     */
    abstract protected function validateRule(): array;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $data = $this->request->input($this->getNameFilter(), []);
        $this->setData(is_array($data) ? $data : []);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getNameFilter(): ?string
    {
        return $this->nameFilter;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
        $this->prepareData();
    }

    public function setNameFilter(?string $nameFilter): void
    {
        $this->nameFilter = $nameFilter;
    }

    private function like(Builder $query, string $column, $value): Builder
    {
        return $query->where($column, 'like', '%'.$value.'%');
    }

    private function exact(Builder $query, string $column, $value): Builder
    {
        return $query->where($column, '=', $value);
    }

    private function applyRule(Builder $query, string|Closure $rule, string $column, $value): Builder
    {
        //if (empty($value)) return $query;

        if ($rule instanceof Closure){
            $rule($query, $column, $value);
            return $query;
        }

        switch ($rule) {
            case self::FILTER_TYPE_LIKE:
                $this->like($query, $column, $value);
                break;
            case self::FILTER_TYPE_EXACT:
                $this->exact($query, $column, $value);
                break;
        }
        return $query;
    }

    public function prepareData(): void
    {
        $data = $this->getData();
        $validator = Validator::make($data, $this->validateRule());

        if ($validator->fails()) {
            foreach($validator->invalid() as $field => $value){
                unset($data[$field]);
            }
            $this->setData($data);
        }
    }

    /**
     * Применяет фильтры к запросу $query
     * Возращается тот же $query с примененными условиями
     */
    public function apply(Builder $query): Builder
    {
        $filterData = $this->getData();
        foreach ($this->getFilters() as $column => $rule) {
            if (isset($filterData[$column])) {
                $this->applyRule($query, $rule, $column, $filterData[$column]);
            }
        }
        return $query;
    }
}
