<?php

/**
 * @param array $array
 * @param string $methodName
 * @param bool $preserveKeys
 * @return array
 */
function mpull(array $array, $methodName, $preserveKeys = true)
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
