<?php

/**
 * mpull([$ferrary, $bmw], 'getColor') = ['red', 'black']
 *
 * @param object[] $array
 * @param string $methodName
 * @param bool $preserveKeys
 * @return array
 */
function mpull(array $array, $methodName, $preserveKeys = false)
{
    $res = [];
    foreach ($array as $key => $elem) {
        $res[$key] = $elem->$methodName();
    }

    if ($preserveKeys) {
        $res = array_values($res);
    }

    return $res;
}

/**
 * head([3, 2, 5) = 3
 *
 * @param array $ar
 * @return mixed|null
 */
function head(array $ar)
{
    foreach ($ar as $value) {
        return $value;
    }
    return null;
}

/**
 * ipull([['id' => 1, 'var' => 'abc'], ['id' => 2, 'var' => 'def']], 'id')
 * = [1, 2]
 *
 * @param array[] $array
 * @param string|int $key
 * @param bool $preserveKeys
 * @return array
 */
function ipull(array $array, $key, $preserveKeys = false)
{
    $res = [];
    foreach ($array as $index => $item) {
        $res[$index] = $item[$key];
    }

    if ($preserveKeys) {
        $res = array_values($res);
    }

    return $res;
}

/**
 * igroup([
 *     ['id' => 1, 'var' => 'abc'],
 *     ['id' => 2, 'var' => 'def'],
 *     ['id' => 3, 'var' => 'abc'],
 * ], 'var')
 * = [
 *     'abc' => [
 *         ['id' => 1, 'var' => 'abc'],
 *         ['id' => 3, 'var' => 'abc'],
 *     ],
 *     'def' => [
 *         ['id' => 2, 'var' => 'def'],
 *     ]
 * ]
 *
 * @param array[] $array
 * @param string|int $key
 * @return array[]
 */
function igroup(array $array, $key)
{
    $res = [];
    foreach ($array as $item) {
        $res[$item[$key]][] = $item;
    }
    return $res;
}
